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
        'is_leader',
        'country',
        'original_group_id',
        'target_group'
    ];

    public function originalGroup()
    {
        return $this->belongsTo(OriginalGroup::class, 'original_group_id', 'order_number');
    }
}
