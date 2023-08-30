<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditTrail extends Model
{
    protected $fillable = ['user_id, activity', 'name'];

    use HasFactory;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}