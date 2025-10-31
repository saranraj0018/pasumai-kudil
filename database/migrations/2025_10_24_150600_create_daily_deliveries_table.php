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
        Schema::create('daily_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('no action');
            $table->foreignId('subscription_id')->constrained('user_subscriptions')->onDelete('no action');
            $table->foreignId('delivery_id')->constrained('delivery_partners')->onDelete('no action');
            $table->decimal('amount', 10, 2);
            $table->date('delivery_date');
            $table->string('delivery_status')->default('pending');
            $table->string('image', 255)->nullable();
            $table->integer('quantity')->nullable();
            $table->string('pack')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }
};
