<?php
namespace Aura\Session;

// a session handler that does nothing, for testing purposes only
class FakeSessionHandler
{
    public $data;

    public function close()
    {
        return true;
    }

    public function destroy($session_id)
    {
        $this->data[$session_id] = null;
        return true;
    }

    public function gc($maxlifetime)
    {
        return true;
    }

    public function open($save_path, $session_id)
    {
        return true;
    }

    public function read($session_id)
    {
        return isset($this->data[$session_id]) ? $this->data[$session_id] : '';
    }

    public function write($session_id, $session_data)
    {
        $this->data[$session_id] = $session_data;
        return true;
    }
}
