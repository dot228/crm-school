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
        Schema::table('homework_submissions', function (Blueprint $table) {
            $table->text('assignment_text')->nullable()->after('schedule_id');
            $table->timestamp('assigned_at')->nullable()->after('assignment_text');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('homework_submissions', function (Blueprint $table) {
            $table->dropColumn(['assignment_text', 'assigned_at']);
        });
    }
};
