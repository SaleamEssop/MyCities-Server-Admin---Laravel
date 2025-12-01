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
        Schema::table('sites', function (Blueprint $table) {
            $table->boolean('use_custom_costs')->default(false)->after('billing_type');
            $table->decimal('custom_electricity_cost', 10, 2)->nullable()->after('use_custom_costs');
            $table->decimal('custom_water_cost', 10, 2)->nullable()->after('custom_electricity_cost');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->dropColumn(['use_custom_costs', 'custom_electricity_cost', 'custom_water_cost']);
        });
    }
};