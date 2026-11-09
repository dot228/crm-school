<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->decimal('lesson_price', 10, 2)->nullable()->after('price');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->decimal('money_balance', 10, 2)->default(0)->after('guardian_relation');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->string('payment_type')->default('lessons')->after('tariff_plan_id');
        });
    }

    public function down()
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('lesson_price');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('money_balance');
        });
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('payment_type');
        });
    }
};
