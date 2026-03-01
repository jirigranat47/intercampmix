<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_code',
        'first_name',
        'last_name',
        'country',
        'original_group_id',
        'target_group'
    ];
}
