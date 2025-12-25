<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // CARTS
        Schema::create('carts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('book_id');
            $table->integer('quantity')->default(1);
            $table->timestamp('added_at')->useCurrent();

            $table->unique(['user_id', 'book_id']);
            $table->index('user_id');

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign('book_id')
                ->references('id')->on('books')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });

        // VOUCHERS
        Schema::create('vouchers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 50)->unique();
            $table->text('description')->nullable();
            $table->string('discount_type', 20); // percentage, fixed
            $table->decimal('discount_value', 10, 2);
            $table->decimal('max_discount_amount', 10, 2)->nullable();
            $table->decimal('min_purchase_amount', 10, 2)->default(0);
            $table->integer('quota')->nullable();
            $table->integer('used_count')->default(0);
            $table->timestamp('valid_from');
            $table->timestamp('valid_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();

            $table->index('valid_from');
            $table->index('valid_until');
        });

        // ORDERS
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('order_number', 50)->unique();
            $table->uuid('user_id');
            $table->decimal('total_amount', 12, 2);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('final_amount', 12, 2);
            $table->uuid('voucher_id')->nullable();
            $table->string('status', 20)->default('pending'); // pending, paid, cancelled, expired
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index('user_id');
            $table->index('status');

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign('voucher_id')
                ->references('id')->on('vouchers')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });

        // ORDER ITEMS
        Schema::create('order_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('order_id');
            $table->uuid('book_id');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('subtotal', 12, 2);

            $table->index('order_id');

            $table->foreign('order_id')
                ->references('id')->on('orders')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign('book_id')
                ->references('id')->on('books')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });

        // VOUCHER USAGES
        Schema::create('voucher_usages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('voucher_id');
            $table->uuid('user_id');
            $table->uuid('order_id');
            $table->decimal('discount_amount', 10, 2);
            $table->timestamp('used_at')->useCurrent();

            $table->index('voucher_id');
            $table->index('user_id');
            $table->index('order_id');

            $table->foreign('voucher_id')
                ->references('id')->on('vouchers')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->foreign('order_id')
                ->references('id')->on('orders')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voucher_usages');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('vouchers');
        Schema::dropIfExists('carts');
    }
};
