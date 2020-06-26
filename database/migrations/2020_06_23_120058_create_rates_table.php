<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("rates", function(Blueprint $table) {
            $table->smallIncrements("id");
            $table->string("tariff", 40);
            $table->integer("price")->unsigned();
            $table->smallInteger("company_id")->unsigned();
            $table->foreign("company_id")->references("id")->on("the_company")
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
