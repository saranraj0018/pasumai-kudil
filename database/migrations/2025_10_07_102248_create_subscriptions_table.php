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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('plan_name');
            $table->integer('plan_id')->nullable();
            $table->integer('plan_amount');
            $table->string('plan_pack'); // month / quarter / half year / annual
            $table->string('plan_type'); // Basic / Best Value / Customize
            $table->string('plan_duration')->nullable(); // 20days / 1month /   3months / 6months
            $table->json('plan_details')->nullable(); // ["secure planning", ...]
            $table->json('quantity')->nullable();     // [1,2,...]
            $table->string('pack')->nullable();         // ["500ml", "1ltr"]
            $table->json('delivery_days')->nullable();
            $table->boolean('is_show_mobile')->default(0);
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
