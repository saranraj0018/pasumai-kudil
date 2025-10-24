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
        Schema::create('hubs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id');
            $table->text('address')->nullable();
            $table->string('name',100);
            $table->string('latitude',100);
            $table->string('longitude',100);
            $table->tinyInteger('type')->nullable()->comment('1-Grocery, 2-Milk');
            $table->tinyInteger('status')->nullable()->comment('1-activate, 0-deactivate');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hubs');
    }
};
