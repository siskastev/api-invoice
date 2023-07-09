<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceSummaryProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_summary_products', function (Blueprint $table) {
            $table->id();
            $table->string("invoice_code", 100);
            $table->uuid("product_id");
            $table->string("product_name",100);
            $table->unsignedDouble('qty');
            $table->unsignedDouble('unit_price');
            $table->unsignedDouble('total_price');
            $table->timestamps();
            $table->foreign('invoice_code')
                ->references('code')
                ->on('invoices')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoice_summary_products');
    }
}
