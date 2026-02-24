<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    protected $fillable = [
        'id',
        'name',
        'description',
        'phone',
        'mail',
        'delivery_day',
    ];
}
