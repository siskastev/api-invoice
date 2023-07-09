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

    public $incrementing = false;

    protected $keyType = 'string';

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

    public static function findByCode($code): ?Invoice
    {
        return static::join('customers', 'invoices.customer_id', '=', 'customers.id')
            ->select([
                'invoices.code',
                'invoices.status',
                'invoices.subject',
                'invoices.issue_date',
                'invoices.due_date',
                'customers.id as customers_id',
                'customers.name as customer_name',
                'customers.address',
                'customers.country',
                'customers.city',
                'invoices.total_items',
                'invoices.sub_total',
                'invoices.tax',
                'invoices.grand_total',
                'invoices.created_at',
                'invoices.updated_at',
            ])
            ->where('invoices.code', $code)
            ->first();
    }
}
