<?php

namespace App\Password;

use Illuminate\Auth\Passwords\CacheTokenRepository;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Support\Carbon;

class PhoneRepository extends CacheTokenRepository
{
    public function create(CanResetPassword $user)
    {
        $this->delete($user);

        $token = mt_rand(100000, 999999);

        $this->cache->put(
            $this->prefix.$user->getEmailForPasswordReset(),
            [$this->hasher->make($token), Carbon::now()->format($this->format)],
            $this->expires,
        );

        return $token;
    }
}
