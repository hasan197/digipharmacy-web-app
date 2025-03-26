<?php

namespace App\Infrastructure\Auth\Services;

use Illuminate\Support\Facades\Log;

class LogService
{
    public function authSuccess(string $message, int $userId, string $email, string $ipAddress): void
    {
        Log::channel('auth')->info($message, [
            'user_id' => $userId,
            'email' => $email,
            'ip' => $ipAddress
        ]);
    }

    public function authFailure(string $message, string $email, string $ipAddress): void
    {
        Log::channel('auth')->warning($message, [
            'email' => $email,
            'ip' => $ipAddress
        ]);
    }
}
