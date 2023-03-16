<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRegionsAccountTypeCostTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('regions_account_type_cost', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('region_id')->nullable();
            $table->unsignedBigInteger('account_type_id')->nullable();
            $table->unsignedBigInteger('meter_type_id')->nullable();
            $table->json('meter_type_id')->nullable();
            $table->double('garbase_collection_cost')->nullable();
            $table->double('infrastructure_levy_cost')->nullable();
            $table->double('vat_rate')->nullable();
            $table->double('vat_percentage')->nullable();
            $table->integer('is_active')->default(1);
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
        Schema::dropIfExists('regions_account_type_cost');
    }
}
