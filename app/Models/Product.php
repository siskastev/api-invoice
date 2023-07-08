<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\UUID;

class Product extends Model
{
    use HasFactory, UUID;

    protected $fillable = [
        'name', 'type', 'qty', 'unit_price', 'total_price'
    ];
}
