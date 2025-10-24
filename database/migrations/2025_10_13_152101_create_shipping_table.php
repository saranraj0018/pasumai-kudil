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
         Schema::create('shippings', function (Blueprint $table) {
            $table->id();
            $table->string('city');
            $table->string('latitude');
            $table->string('longitude');
            $table->decimal('free_shipping', 8, 2)->default(0);
            $table->decimal('extra_km', 8, 2)->default(0);
            $table->string('address')->nullable();
            $table->boolean('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::dropIfExists('shipping');
    }
};
