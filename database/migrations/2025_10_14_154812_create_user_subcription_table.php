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
        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('no action');
            $table->foreignId('subscription_id')->constrained('subscriptions')->onDelete('no action');
            $table->date('start_date');
            $table->date('end_date');
            $table->date('valid_date')->nullable();
            $table->string('pack')->nullable();
            $table->integer('quantity')->default(1);
            $table->integer('status')->default(1)->comment('1-active, 2-inactive');
            $table->string('description')->nullable();
            $table->json('cancelled_date')->nullable();
            $table->date('in_active_date')->nullable();
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
