<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('commerce_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->decimal('value', 36, 0)->unsigned();
            $table->decimal('dollar_price', 36, 18)->unsigned();

            $table->string('currency');

            $table->enum('status', ['completed', 'pending', 'canceled'])->default('pending');

            $table->dateTime('expires_at');
            $table->dateTime('completed_at')->nullable();
            $table->dateTime('canceled_at')->nullable();

            $table->string('address');

            $table->foreignId('wallet_account_id')
                ->constrained('wallet_accounts')->cascadeOnDelete();

            $table->foreignUuid('commerce_customer_id')->nullable()
                ->constrained('commerce_customers')->nullOnDelete();

            $table->foreignId('commerce_account_id')
                ->constrained('commerce_accounts')->cascadeOnDelete();

            $table->string('transactable_type');
            $table->char('transactable_id', 36);
            $table->index(['transactable_type', 'transactable_id']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('commerce_transactions');
    }
};
