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
        Schema::table('schedules', function (Blueprint $table) {
            $table->enum('absence_type', ['excused', 'unexcused'])->nullable()->after('status');
            $table->text('absence_reason')->nullable()->after('absence_type');
            $table->foreignId('substitute_teacher_id')->nullable()->after('teacher_id')
                  ->constrained('users')->nullOnDelete();
        });

        Schema::table('enrollments', function (Blueprint $table) {
            $table->unsignedInteger('lessons_balance')->default(0)->after('notes');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('tariff_plan_id')->nullable()->after('enrollment_id')
                  ->constrained('tariff_plans')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropForeign(['substitute_teacher_id']);
            $table->dropColumn(['absence_type', 'absence_reason', 'substitute_teacher_id']);
        });

        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropColumn('lessons_balance');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['tariff_plan_id']);
            $table->dropColumn('tariff_plan_id');
        });
    }
};
