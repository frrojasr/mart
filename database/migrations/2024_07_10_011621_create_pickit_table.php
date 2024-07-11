<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pickit', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable();
            $table->unsignedBigInteger('order_id');
            $table->string('service_type', 191);
            $table->string('point_id', 191)->nullable();
            $table->decimal('pickit_price', 16, 8);
            $table->string('transaction_id', 191)->nullable();
            $table->string('pickit_code', 191)->nullable();
            $table->string('url_tracking', 191)->nullable();
            $table->string('status', 191)->comment('pending-payment/processing/completed/canceled');;
          
            $table->timestamps();

           


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pickit');
    }
};
