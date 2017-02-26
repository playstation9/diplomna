<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableCustomerFoodRel extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_food_rel', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('food_id');
            $table->string('title',150);
            $table->string('units',50);
            $table->string('amount');
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
        Schema::drop('customer_food_rel');
    }
}
