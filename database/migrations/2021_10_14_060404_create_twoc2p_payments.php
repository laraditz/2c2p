<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTwoc2pPayments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('twoc2p_payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('action', 100)->nullable();
            $table->json('request')->nullable();
            $table->json('response')->nullable();
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
        Schema::dropIfExists('twoc2p_payments');
    }
}
