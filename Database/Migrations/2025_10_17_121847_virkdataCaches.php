<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class VirkdataCaches extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        if (!Schema::hasTable('virkdata_caches')) {

            Schema::create('virkdata_caches', function (Blueprint $table) {
                $table->id();
                $table->string('company_name')->nullable();
                $table->string('vat')->unique(); // CVR / VAT nummer
                $table->json('data')->nullable(); // hele JSON-indholdet fra VirkData
                $table->timestamp('expire_at')->nullable()->default(null);
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
        Schema::dropIfExists('virkdata_caches');
    }
}
