<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFixedCostPropsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fixed_cost_props', function (Blueprint $table) {
            $table->id();
            $table->integer('property_id')->nullable();
            $table->string('title');
            $table->string('value')->nullable();
            $table->integer('is_default')->default(0);
            $table->integer('added_by')->nullable();
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
        Schema::dropIfExists('fixed_cost_props');
    }
}
