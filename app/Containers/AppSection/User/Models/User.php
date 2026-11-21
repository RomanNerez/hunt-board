<?php

namespace App\Containers\AppSection\User\Models;

use App\Containers\AppSection\User\Data\Collections\UserCollection;
use App\Containers\AppSection\User\Enums\Gender;
use App\Ship\Parents\Models\UserModel as ParentUserModel;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Casts\Attribute;

final class User extends ParentUserModel implements FilamentUser
{
    protected $fillable = [
        'name',
        'email',
        'password',
        'gender',
        'birth',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'immutable_datetime',
        'password' => 'hashed',
        'gender' => Gender::class,
        'birth' => 'immutable_date',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function newCollection(array $models = []): UserCollection
    {
        return new UserCollection($models);
    }

    protected function email(): Attribute
    {
        return new Attribute(
            get: static fn (string|null $value): string|null => is_null($value) ? null : strtolower($value),
        );
    }
}
