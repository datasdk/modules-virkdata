<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Virkdata extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('virkdata')) {
            Schema::create('virkdata', function (Blueprint $table) {

                $table->increments('id');
                $table->string('auth_token', 255)->nullable(); // API token
                $table->boolean('active')->default(1); // mulighed for flere tokens, kun én aktiv
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
         Schema::dropIfExists('virkdata');
    }
}
