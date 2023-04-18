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
        Schema::create('commerce_payment_wallet', function (Blueprint $table) {
            $table->id();

            $table->foreignUuid('commerce_payment_id')
                ->constrained('commerce_payments')->cascadeOnDelete();

            $table->foreignId('wallet_id')
                ->constrained('wallets')->cascadeOnDelete();

            $table->unique(['commerce_payment_id', 'wallet_id']);

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
        Schema::dropIfExists('commerce_payment_wallet');
    }
};
