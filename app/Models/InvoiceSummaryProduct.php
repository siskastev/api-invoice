<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceSummaryProduct extends Model
{
    use HasFactory;

    protected $table = 'invoice_summary_products';

    protected $fillable = [
        'invoice_code',
        'product_id',
        'product_name',
        'qty',
        'unit_price',
        'total_price'
    ];
}
