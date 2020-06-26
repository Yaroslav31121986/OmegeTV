<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("customer", function(Blueprint $table) {
            $table->smallIncrements("id");
            $table->string("surname", 40);
            $table->string("first name", 40);
            $table->enum('state', ['on', 'off']);
            $table->smallInteger("rates_id")->unsigned();
            $table->foreign("rates_id")->references("id")->on("rates")
                ->onDelete("cascade")->onUpdate("cascade");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
