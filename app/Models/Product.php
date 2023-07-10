<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\UUID;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, UUID, SoftDeletes;

    protected $fillable = [
        'name', 'type', 'qty', 'unit_price', 'total_price'
    ];
}
