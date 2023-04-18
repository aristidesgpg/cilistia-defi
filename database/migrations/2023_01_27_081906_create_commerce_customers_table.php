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
        Schema::create('commerce_customers', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('last_name')->nullable();
            $table->string('first_name')->nullable();

            $table->string('email');

            $table->foreignId('commerce_account_id')
                ->constrained('commerce_accounts')->cascadeOnDelete();

            $table->unique(['email', 'commerce_account_id']);

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
        Schema::dropIfExists('commerce_customers');
    }
};
