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
        Schema::table('earnings', function (Blueprint $table) {
            $table->dropColumn(['type']);

            $table->decimal('value', 36, 18)->unsigned()->change();
            $table->integer('precision')->unsigned()->default(2)->after('value');
            $table->text('description')->nullable()->change();

            $table->string('transaction_type')->nullable()->after('description');
            $table->char('transaction_id', 36)->nullable()->after('description');
            $table->unique(['transaction_type', 'transaction_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('earnings', function (Blueprint $table) {
            $table->dropUnique(['transaction_type', 'transaction_id']);
            $table->dropColumn(['transaction_type', 'transaction_id', 'precision']);
            $table->enum('type', ['wallet', 'exchange', 'giftcard'])->nullable();
        });
    }
};
