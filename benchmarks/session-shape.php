<?php
/**
 * Does per-field (hash) session storage beat a single string?
 *
 * Models one HTTP request under PHP's session lifecycle, which reads at
 * session_start() and writes at session_write_close():
 *
 *   string             GET whole session + SETEX whole session
 *   per-field, 1 seg   HGET one segment  + HSET one segment
 *   per-field, all     HGETALL           + HMSET
 *
 * The string figure is what Aura.Session ships today. "per-field, 1 seg" is the
 * best case for the Segment-backed redesign asked for in issue #95 -- a request
 * that touches exactly one segment. "per-field, all" is its worst case, a
 * request that touches every segment.
 *
 * Both shapes make exactly two round trips, so latency is paid equally by both
 * and cancels out; what differs is bytes on the wire and serialization work.
 * Read the absolute saving, not the percentage: at a 0.5ms RTT every request
 * already spends ~1ms waiting before any of these numbers apply.
 *
 * Needs ext-redis. Writes only keys prefixed "aura-bench:" and deletes them
 * afterwards; it never flushes the database, so it is safe against a Redis that
 * holds other data. Even so, point it at a throwaway server.
 *
 * Usage: php benchmarks/session-shape.php [host] [port]
 */

$host = $argv[1] ?? '127.0.0.1';
$port = (int) ($argv[2] ?? 6379);

if (! extension_loaded('redis')) {
    fwrite(STDERR, "This benchmark needs ext-redis.\n");
    exit(1);
}

$redis = new Redis();
$redis->connect($host, $port);

const PREFIX = 'aura-bench:';

/** Remove only the keys this script created. */
function cleanup(Redis $redis): void
{
    $it = null;
    while (($keys = $redis->scan($it, PREFIX . '*', 1000)) !== false) {
        if ($keys !== []) {
            $redis->del($keys);
        }
    }
}

cleanup($redis);

const ITERATIONS = 2000;
const WARMUP     = 200;

/** Build a session of $segments segments, each about $bytes of payload. */
function makeSession(int $segments, int $bytes): array
{
    $session = [];
    for ($i = 0; $i < $segments; $i++) {
        $session["Vendor\\Package\\Segment{$i}"] = [
            'user_id'  => 1000 + $i,
            'payload'  => str_repeat('x', $bytes),
        ];
    }
    return $session;
}

function timeIt(callable $fn): float
{
    for ($i = 0; $i < WARMUP; $i++) { $fn(); }
    $start = hrtime(true);
    for ($i = 0; $i < ITERATIONS; $i++) { $fn(); }
    return (hrtime(true) - $start) / ITERATIONS / 1e6; // ms per op
}

$scenarios = [
    ['segments' => 2,  'bytes' => 100],
    ['segments' => 3,  'bytes' => 300],
    ['segments' => 5,  'bytes' => 500],
    ['segments' => 5,  'bytes' => 2000],
    ['segments' => 10, 'bytes' => 2000],
    ['segments' => 20, 'bytes' => 2000],
    ['segments' => 20, 'bytes' => 20000],
];

printf("Redis %s at %s:%d, PHP %s, %d iterations\n\n",
    $redis->info('server')['redis_version'], $host, $port, PHP_VERSION, ITERATIONS);

printf("%-18s %10s %10s | %9s %9s %9s | %s\n",
    'session shape', 'total', 'one seg', 'string', 'pf(1seg)', 'pf(all)', 'verdict');
printf("%s\n", str_repeat('-', 108));

$rows = [];

foreach ($scenarios as $s) {
    $session  = makeSession($s['segments'], $s['bytes']);
    $keys     = array_keys($session);
    $touched  = $keys[0];

    $whole    = serialize($session);
    $field    = serialize($session[$touched]);
    $sid      = PREFIX . $s['segments'] . ':' . $s['bytes'];
    $hkey     = $sid . ':h';

    // seed both shapes
    $redis->set($sid, $whole);
    $hash = [];
    foreach ($session as $k => $v) { $hash[$k] = serialize($v); }
    $redis->hMSet($hkey, $hash);

    // --- a request that touches one segment ---

    // string: read the whole session, write the whole session back
    $stringMs = timeIt(function () use ($redis, $sid, $touched) {
        $raw  = $redis->get($sid);
        $data = unserialize($raw);
        $data[$touched]['user_id']++;
        $redis->setEx($sid, 1440, serialize($data));
    });

    // per-field: read only the touched segment, write only that segment
    $fieldMs = timeIt(function () use ($redis, $hkey, $touched) {
        $raw = $redis->hGet($hkey, $touched);
        $seg = unserialize($raw);
        $seg['user_id']++;
        $redis->hSet($hkey, $touched, serialize($seg));
    });

    // adversarial case for per-field: the request touches every segment, so
    // it must fetch them all (HGETALL) and write them all back.
    $allMs = timeIt(function () use ($redis, $hkey, $keys) {
        $raw = $redis->hGetAll($hkey);
        $out = [];
        foreach ($raw as $k => $v) {
            $seg = unserialize($v);
            $seg['user_id']++;
            $out[$k] = serialize($seg);
        }
        $redis->hMSet($hkey, $out);
    });

    $ratio   = $stringMs / $fieldMs;
    $verdict = $ratio > 1.10 ? sprintf('per-field %.1fx faster', $ratio)
             : ($ratio < 0.91 ? sprintf('string %.1fx faster', 1 / $ratio)
             : 'no real difference');

    printf("%-18s %10s %10s | %8.3f %8.3f %8.3f | %s\n",
        $s['segments'] . ' seg x ' . $s['bytes'] . 'B',
        number_format(strlen($whole)),
        number_format(strlen($field)),
        $stringMs, $fieldMs, $allMs,
        $verdict
    );

    $rows[] = [
        'shape'  => $s['segments'] . ' seg x ' . $s['bytes'] . 'B',
        'string' => $stringMs,
        'field'  => $fieldMs,
        'bs'     => strlen($whole) * 2,
        'bf'     => strlen($field) * 2,
        'all'    => $allMs,
    ];
}

// Both shapes use 2 round trips, so added network latency cancels out; what
// differs is bytes on the wire. Model a slow link to see when that matters.
echo "\nSame comparison over a slower link (bytes matter, round trips do not:\n";
echo "both shapes make exactly 2). Assumes 100 Mbit/s, i.e. 12.5 MB/s.\n\n";
echo "Absolute saving is what matters, not the percentage: both shapes make 2\n";
echo "round trips, and that latency is paid by both. At 0.5ms RTT each request\n";
echo "already spends ~1ms waiting, before any of these numbers.\n\n";

foreach ([['1 Gbit/s LAN', 125_000_000], ['100 Mbit/s', 12_500_000]] as [$label, $bps]) {
    printf("-- %s --\n", $label);
    printf("%-18s %12s %12s | %s\n", 'session shape', 'string', 'per-field', 'per-field saves');
    printf("%s\n", str_repeat('-', 70));
    foreach ($rows as $r) {
        $wireS = $r['string'] + ($r['bs'] / $bps) * 1000;
        $wireF = $r['field']  + ($r['bf'] / $bps) * 1000;
        printf("%-18s %10.3fms %10.3fms | %.3fms (%.0f%%)\n",
            $r['shape'], $wireS, $wireF, $wireS - $wireF, ($wireS - $wireF) / $wireS * 100);
    }
    echo "\n";
}

cleanup($redis);
