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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone_number');
            $table->string('alt_mobile_number', 20)->nullable();
            $table->string('address');
            $table->string('pincode');
            $table->string('state');
            $table->string('city');
            $table->string('address_type');
            $table->boolean('is_default')->default(0);
            $table->text('latitude')->nullable();
            $table->text('longitude')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->integer('updated_by')->nullable();

            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_id')->default('#20250320125942');
            $table->string('user_id');
            $table->longText('address_id');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->enum('status', ['1', '2', '3', '4', '5', '6'])->default('1');
            $table->double('net_amount');
            $table->double('shipping_amount')->default(0);
            $table->double('gross_amount');
            $table->double('gst_amount')->default(0);
            $table->longText('notes')->nullable();
            $table->tinyInteger('rating_status')->default(0);
            $table->integer('coupon_id')->nullable();
            $table->double('coupon_amount')->nullable();
            $table->dateTime('shipped_at')->nullable();
            $table->dateTime('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();

            $table->timestamps();
        });

        Schema::create('order_details', function (Blueprint $table) {
            $table->id();
            $table->integer('order_id');
            $table->integer('product_id');
            $table->integer('category_id');
            $table->text('variant_id');
            $table->string('product_name');
            $table->integer('quantity');
            $table->double('net_amount');
            $table->integer('gst_type');
            $table->integer('gst_percentage')->default(12);
            $table->double('gst_amount')->default(0);
            $table->bigInteger('weight')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::dropIfExists('addresses');
        // Schema::dropIfExists('orders');
        // Schema::dropIfExists('order_details');
    }
};