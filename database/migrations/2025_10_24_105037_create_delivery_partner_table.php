<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('delivery_partners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hub_id')->constrained('hubs')->onDelete('no action');
            $table->string('name', 255)->nullable();
            $table->string('mobile_number', 255)->nullable();
            $table->unsignedBigInteger('city_id');
            $table->string('area_name')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->timestamps();

            $table->foreign('city_id')->references('id')->on('cities')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
