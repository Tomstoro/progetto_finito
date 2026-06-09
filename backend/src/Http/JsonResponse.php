<?php

class JsonResponse
{
    public static function success(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }

    public static function error(string $message, int $status = 400): void
    {
        http_response_code($status);
        echo json_encode(['success' => false, 'error' => $message]);
        exit;
    }
}
