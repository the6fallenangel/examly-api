<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class OtpService
{
    private const OTP_LENGTH = 6;

    private const OTP_TTL = 300;

    public function __construct(
        private readonly string $scope = 'register'
    ) {}

    public function generate(string $email): string
    {
        $min = (int) (10 ** (self::OTP_LENGTH - 1));
        $max = (int) ((10 ** self::OTP_LENGTH) - 1);
        $otp = (string) random_int($min, $max);

        Cache::put($this->key($email), Hash::make($otp), self::OTP_TTL);

        return $otp;
    }

    public function verify(string $email, string $otp): bool
    {
        $storedOtp = Cache::get($this->key($email));
        if (! $storedOtp) {
            return false;
        }

        return Hash::check($otp, $storedOtp);
    }

    public function forget(string $email): void
    {
        Cache::forget($this->key($email));
    }

    private function key(string $email): string
    {
        return $this->scope.':otp:'.strtolower(trim($email));
    }
}
