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
        Schema::table('users', function (Blueprint $table) {
            $table->string('image', 255)->nullable();
            $table->string('mobile_number', 255)->nullable();
            $table->longText('likedProducts')->nullable();
            $table->string('city', 255)->nullable();
            $table->string('latitude', 255)->nullable();
            $table->string('longitude', 255)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('pincode', 255)->nullable();
            $table->string('state', 255)->nullable();
            $table->integer('subscription_id')->nullable();
            $table->string('account_number', 255)->nullable();
            $table->string('bank_name', 255)->nullable();
            $table->string('ifsc_code', 255)->nullable();
            $table->string('account_holder_name', 255)->nullable();
            $table->string('branch', 255)->nullable();
            $table->string('fcm_token')->nullable()->after('remember_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }
};
