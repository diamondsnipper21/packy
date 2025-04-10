<?php

namespace App\Services\Authenticator;

class AuthenticatorService
{
    /**
     * @return Authenticator
     */
    public function getInstance(): Authenticator
    {
        $service = $this->getService();

        return new $service;
    }

    /**
     * @return string|null
     */
    private function getService(): ?string
    {
        return match (env('AUTHENTICATOR')) {
            default => LocalAuthenticatorService::class,
            'google' => GoogleAuthenticatorService::class,
        };
    }
}
