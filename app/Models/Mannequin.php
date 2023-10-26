<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mannequin extends Model
{
    protected $table = 'mannequins';

    protected $fillable = [
        'po',
        'itemref',
        'company',
        'category',
        'type',
        'price',
        'description',
        'images',
        'file',
        'pdf',
        'addedBy',
        'activeStatus',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category');
    }
    public function company()
    {
        return $this->belongsTo(Company::class, 'company');
    }

    public function mannequin()
    {
        return $this->hasMany('po');
    }
    public function po()
    {
        return $this->hasMany('itemref');
    }
}
