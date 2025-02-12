<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Filament\Pages\ProfilePage;
use Devdojo\Auth\Models\User as AuthUser;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Jeffgreco13\FilamentBreezy\Traits\TwoFactorAuthenticatable;
use LakM\Comments\Concerns\Commenter;
use LakM\Comments\Contracts\CommenterContract;
use Laravel\Sanctum\HasApiTokens;

class User extends AuthUser implements CommenterContract, FilamentUser, HasAvatar
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use Commenter, HasApiTokens, HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = [
        'filament_avatar_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function hasVerifiedPhone(): bool
    {
        return $this->phone_verified_at !== null;
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar_url ? Storage::url($this->avatar_url) : null;
    }

    public function filamentAvatarUrl(): Attribute
    {
        return new Attribute(
            get: fn () => $this->getFilamentAvatarUrl(),
        );
    }

    public function profileLink(): Attribute
    {
        return new Attribute(
            get: fn () => ProfilePage::getUrl([
                'user' => $this,
            ]),
        );
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }
}
