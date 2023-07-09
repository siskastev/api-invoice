<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    const STATUS_PAID = 1;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'invoices';

    protected $primaryKey = 'code';

    protected $fillable = [
        'code',
        'status',
        'subject',
        'issue_date',
        'due_date',
        'customer_id',
        'total_items',
        'sub_total',
        'tax',
        'grand_total'
    ];
}
