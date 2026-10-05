<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Student extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'students';

    protected $guarded = ['*'];

    protected $hidden = [
        'password',
        'ic_no',
        'phone',
        'email',
    ];
}
