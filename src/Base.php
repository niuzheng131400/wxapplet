<?php

namespace App;

class Base
{
    public function __construct()
    {
//        if (!self::checkAgent()) self:: showJson(9000, 'agent illegal');
    }

    static private function checkAgent(): bool
    {
        $agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        if (!$agent || strpos(strtolower($agent), 'micromessenger') === false) return false;
        return true;
    }

    static protected function showJson($code = 200, $message = 'success', $data = [])
    {
        $data = [
            'code' => $code,
            'message' => $message,
            'data' => $data
        ];
        exit(json_encode($data));
    }
}