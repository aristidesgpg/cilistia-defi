<?php

namespace App\Models\Support;

use App\Helpers\TwoFactorAuth;
use PragmaRX\Google2FA\Google2FA;

trait TwoFactor
{
    /**
     * Determine if the user has enabled two factor
     *
     * @return bool
     */
    public function isTwoFactorEnabled(): bool
    {
        return $this->two_factor_enable;
    }

    /**
     * Reset two factor
     *
     * @return void
     */
    public function resetTwoFactorToken(): void
    {
        $this->two_factor_secret = app(TwoFactorAuth::class)->generateSecretKey();
        $this->save();
    }

    /**
     * Disable two factor
     *
     * @return void
     */
    public function disableTwoFactor(): void
    {
        $this->two_factor_enable = false;
        $this->save();
    }

    /**
     * Enable two factor
     *
     * @return void
     */
    public function enableTwoFactor(): void
    {
        $this->two_factor_enable = true;
        $this->save();
    }

    /**
     * Decrypt two factor secret
     *
     * @param  string  $value
     * @return string
     */
    protected function getTwoFactorSecretAttribute(string $value): string
    {
        return decrypt($value);
    }

    /**
     * Encrypt two factor secret
     *
     * @param  string  $value
     */
    protected function setTwoFactorSecretAttribute(string $value): void
    {
        $this->attributes['two_factor_secret'] = encrypt($value);
    }

    /**
     * Verify two factor token
     *
     * @param $token
     * @return bool|int
     */
    public function verifyTwoFactorToken($token): bool|int
    {
        return resolve(Google2FA::class)->verifyKey($this->two_factor_secret, $token);
    }

    /**
     * Get the two factor authentication QR code URL.
     *
     * @return string
     */
    public function getTwoFactorQrCodeUrl(): string
    {
        return app(TwoFactorAuth::class)->qrCodeUrl(
            config('app.name'),
            $this->email,
            $this->two_factor_secret
        );
    }
}
