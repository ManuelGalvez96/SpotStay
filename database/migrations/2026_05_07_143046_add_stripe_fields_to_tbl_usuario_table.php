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
        Schema::table('tbl_usuario', function (Blueprint $table) {
            $table->string('stripe_id')->nullable()->after('email_usuario')->index();
            $table->string('stripe_account_id')->nullable()->after('stripe_id')->index();
            $table->string('stripe_subscription_id')->nullable()->after('stripe_account_id');
            $table->string('stripe_status')->nullable()->after('stripe_subscription_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_usuario', function (Blueprint $table) {
            $table->dropColumn(['stripe_id', 'stripe_account_id', 'stripe_subscription_id', 'stripe_status']);
        });
    }
};
