<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePropertiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_manager_id')->nullable()->constrained('users')->onDelete('set null');
            $table->unsignedBigInteger('region_cost_id');

            $table->string('name');
            $table->string('contact_person');
            $table->string('address');
            $table->text('description')->nullable();
            $table->string('phone');
            $table->string('whatsapp');
            $table->integer('billing_day');
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
        Schema::dropIfExists('properties');
    }
}
