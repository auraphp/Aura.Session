# Benchmarks

Standalone scripts. They are not part of the test suite and are not run in CI;
they exist so that performance claims about this package can be re-checked
rather than taken on trust.

## `session-shape.php`

Answers the question behind [issue #95](https://github.com/auraphp/Aura.Session/issues/95):
is it faster to store a session as one Redis **string**, or as a **hash with one
field per segment** so that a request reads and writes only the fields it
touches?

### Running it

Needs `ext-redis` and a Redis server. Point it at a throwaway server — it only
writes keys prefixed `aura-bench:` and deletes them afterwards, and never
flushes the database, but there is no reason to aim it at anything real.

```
redis-server --port 6399 --save '' --appendonly no --daemonize yes
php benchmarks/session-shape.php 127.0.0.1 6399
redis-cli -p 6399 shutdown nosave
```

Defaults to `127.0.0.1:6379` if no host and port are given.

### What it measures

One HTTP request under PHP's session lifecycle, which reads once at
`session_start()` and writes once at `session_write_close()`:

| shape | read | write |
|-------|------|-------|
| string | `GET` whole session | `SETEX` whole session |
| per-field, 1 segment | `HGET` one segment | `HSET` one segment |
| per-field, all segments | `HGETALL` | `HMSET` |

The string row is what `RedisSessionHandler` does today. "per-field, 1 segment"
is the best case for the Segment-backed redesign #95 asks for; "per-field, all
segments" is its worst case.

Session shapes are varied from 2 segments of 100 bytes (a typical auth identity
plus a flash message) up to 20 segments of 20 KB.

### Reading the output

**Both shapes make exactly two round trips.** Latency is therefore paid equally
by both and cancels out; what differs is bytes on the wire and the
serialization work. The script adds modelled wire time for a 1 Gbit LAN and a
100 Mbit link, but round-trip latency is deliberately excluded from those
figures — at a 0.5 ms RTT a request already spends ~1 ms waiting before any of
these numbers apply.

So read the **absolute** saving, not the percentage. A "90% saving" on a figure
that omits 1 ms of unavoidable latency is a few percent of the real request.

### Results as of 2026-09, Redis 6.2.6, PHP 8.4.1, 2000 iterations

| session shape | total bytes | string | per-field, 1 seg | per-field, all |
|---|---:|---:|---:|---:|
| 2 seg × 100B | 368 | 0.044 | 0.043 | 0.045 |
| 3 seg × 300B | 1,149 | 0.044 | 0.043 | 0.046 |
| 5 seg × 500B | 2,911 | 0.048 | 0.047 | 0.048 |
| 5 seg × 2000B | 10,416 | 0.047 | 0.044 | 0.051 |
| 10 seg × 2000B | 20,827 | 0.059 | 0.046 | 0.065 |
| 20 seg × 2000B | 41,657 | 0.072 | 0.045 | 0.085 |
| 20 seg × 20000B | 401,677 | 0.244 | 0.056 | 0.282 |

(ms per request; lower is better.)

### Conclusions

- **Below ~3 KB of session — the realistic case — there is no difference.**
  Adding 1 Gbit wire time, per-field saves 0.007–0.038 ms per request, against
  the ~1 ms both shapes spend on two round trips. That is 1–4% of real cost.
- **The crossover is around 10–20 KB of session.** At 41 KB per-field saves
  0.66 ms on a LAN; at 400 KB it saves 6.3 ms. Those are worth having.
- **Per-field is *slower* when a request touches every segment** — 0.085 vs
  0.072 ms at 41 KB — because it pays hash overhead and per-field serialization
  while moving the same bytes. The win depends on most segments going untouched,
  not merely on the session being large.

Hence the shipped handler stores a string. A Segment-backed store doing
`HGET`/`HSET` per segment is worth building for large sessions with a sparse
access pattern, and is not worth it otherwise.
