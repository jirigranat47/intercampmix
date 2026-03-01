<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OriginalGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'country',
        'subcamp',
        'troop_name',
        'number_of_children'
    ];
}
