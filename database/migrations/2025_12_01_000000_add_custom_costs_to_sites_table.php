<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Schema;

class AddCustomCostsToSitesTable extends Migration
{
    public function up()
    {
        Schema::table('sites', function ($table) {
            $table->boolean('use_custom_costs')->default(false);
            $table->decimal('custom_electricity_cost', 10, 2)->nullable();
            $table->decimal('custom_water_cost', 10, 2)->nullable();
        });
    }

    public function down()
    {
        Schema::table('sites', function ($table) {
            $table->dropColumn('use_custom_costs');
            $table->dropColumn('custom_electricity_cost');
            $table->dropColumn('custom_water_cost');
        });
    }
}