<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // PAYMENTS
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('payment_number', 50)->unique();
            $table->uuid('order_id');
            $table->uuid('user_id');
            $table->decimal('amount', 12, 2);
            $table->string('payment_method', 50); // wallet, bank_transfer, credit_card, qris
            $table->string('payment_gateway_ref', 255)->nullable();
            $table->string('status', 20)->default('pending'); // pending, success, failed, expired
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index('order_id');
            $table->index('user_id');
            $table->index('status');

            $table->foreign('order_id')
                ->references('id')->on('orders')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });

        // WALLETS
        Schema::create('wallets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->unique();
            $table->decimal('balance', 12, 2)->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });

        // WALLET TRANSACTIONS
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('wallet_id');
            $table->string('transaction_type', 20); // top_up, payment, refund
            $table->decimal('amount', 12, 2);
            $table->decimal('balance_before', 12, 2);
            $table->decimal('balance_after', 12, 2);
            $table->uuid('reference_id')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('wallet_id');
            $table->index('transaction_type');
            $table->index('created_at');

            $table->foreign('wallet_id')
                ->references('id')->on('wallets')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
        Schema::dropIfExists('wallets');
        Schema::dropIfExists('payments');
    }
};
