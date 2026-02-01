<?php
namespace Aura\Session;

use SessionHandlerInterface;

// a session handler that does nothing, for testing purposes only
class FakeSessionHandler implements SessionHandlerInterface
{
    public array $data = [];

    public function close(): bool
    {
        return true;
    }

    public function destroy(string $session_id): bool
    {
        unset($this->data[$session_id]);
        return true;
    }

    public function gc(int $maxlifetime): int|false
    {
        return 0;
    }

    public function open(string $save_path, string $session_id): bool
    {
        return true;
    }

    public function read(string $session_id): string|false
    {
        return $this->data[$session_id] ?? '';
    }

    public function write(string $session_id, string $session_data): bool
    {
        $this->data[$session_id] = $session_data;
        return true;
    }
}
