<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->string('code', 10)->primary();
            $table->boolean('status');
            $table->string('subject', 100)->index();
            $table->date('issue_date')->index();
            $table->date('due_date')->index();
            $table->string('customer_id');
            $table->unsignedInteger('total_items')->index();
            $table->unsignedDouble('sub_total');
            $table->unsignedDouble('grand_total');
            $table->unsignedDouble('tax');
            $table->timestamps();
            $table->foreign('customer_id')
                ->references('id')
                ->on('customers')
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
        Schema::dropIfExists('invoices');
    }
}
