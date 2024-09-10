<?php
namespace Budgetcontrol\Authentication\Domain\Model;

use Budgetcontrol\Authentication\Traits\Crypt;
use Illuminate\Database\Eloquent\Casts\Attribute;

class User extends \Budgetcontrol\Library\Model\User
{
    use Crypt;

    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
        'uuid',
        'sub'
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'id'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function email(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => $this->decrypt($value),
            set: fn (string $value) => $this->encrypt($value),
        );
    }

    public function password(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => $this->decrypt($value),
            set: fn (string $value) => $this->encrypt($value),
        );
    }

    public function name(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => $this->decrypt($value),
            set: fn (string $value) => $this->encrypt($value),
        );
    }

    public function sub(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => is_null($value) ? null : $this->decrypt($value),
            set: fn (?string $value) => is_null($value) ? null : $this->encrypt($value),
        );
    }
    
    public function workspaces()
    {
        return $this->hasMany(Workspace::class);
    }
}