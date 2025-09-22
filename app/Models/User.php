<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory; //
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'subscription_tier',
        'subscription_status',
        'credits',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'credits' => 'integer',
    ];

    public function projects()
    {
        return $this->hasMany(Project::class);
    }
}