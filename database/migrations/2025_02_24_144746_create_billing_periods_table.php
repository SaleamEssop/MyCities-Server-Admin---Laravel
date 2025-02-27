<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBillingPeriodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('billing_periods', function (Blueprint $table) {
            $table->id();
            $table->foreign('meter_id')->references('id')->on('meters')->onDelete('cascade');
            $table->foreign('start_reading_id')->references('id')->on('meter_readings')->onDelete('set null');
            $table->foreign('end_reading_id')->references('id')->on('meter_readings')->onDelete('set null');
            $table->unsignedBigInteger('meter_id');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('start_reading', 10); // e.g., "000600"
            $table->string('end_reading', 10);   // e.g., "000610"
            $table->unsignedBigInteger('start_reading_id')->nullable(); // Foreign key to meter_readings
            $table->unsignedBigInteger('end_reading_id')->nullable();   // Foreign key to meter_readings
            $table->decimal('usage_liters', 10, 2);
            $table->decimal('cost', 10, 2);
            $table->decimal('consumption_charge', 10, 2);
            $table->decimal('discharge_charge', 10, 2);
            $table->json('additional_costs');
            $table->json('water_out_additional');
            $table->decimal('vat', 10, 2);
            $table->decimal('daily_usage', 10, 2);
            $table->decimal('daily_cost', 10, 2);
            $table->string('status'); // "Final Estimate", "Actual", "Estimated"
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
        Schema::dropIfExists('billing_periods');
    }
}
