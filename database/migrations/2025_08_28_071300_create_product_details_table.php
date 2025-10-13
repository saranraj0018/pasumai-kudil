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
        Schema::create('product_details', function (Blueprint $table) {
             $table->bigIncrements('id');
             $table->unsignedBigInteger('product_id');
             $table->unsignedBigInteger('category_id');
             $table->decimal('sale_price', 10, 2)->nullable();
             $table->decimal('regular_price', 10, 2);
             $table->decimal('purchase_price', 10, 2);
             $table->decimal('weight', 8, 2)->default(0);
             $table->string('weight_unit', 10)->default('kg');
             $table->integer('tax_type')->nullable();
             $table->integer('tax_percentage')->nullable();
             $table->boolean('is_featured_product')->default(0);
             $table->smallInteger('stock');
             $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_details');
    }
};
