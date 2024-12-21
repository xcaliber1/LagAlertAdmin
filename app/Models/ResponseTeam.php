<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class ResponseTeam extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'responseteam';

    protected $fillable = [
        'email',
        'department',
        'password',
        'qr_code_id',
    ];

    protected $hidden = [
        'remember_token',
    ];
}
