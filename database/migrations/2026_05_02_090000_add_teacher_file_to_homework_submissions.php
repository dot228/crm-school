<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('homework_submissions', function (Blueprint $table) {
            $table->string('teacher_file_path')->nullable()->after('teacher_comment');
        });
    }

    public function down()
    {
        Schema::table('homework_submissions', function (Blueprint $table) {
            $table->dropColumn('teacher_file_path');
        });
    }
};
