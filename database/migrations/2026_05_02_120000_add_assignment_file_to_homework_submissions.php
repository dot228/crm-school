<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('homework_submissions', function (Blueprint $table) {
            $table->string('assignment_file_path')->nullable()->after('assignment_text');
        });
    }

    public function down()
    {
        Schema::table('homework_submissions', function (Blueprint $table) {
            $table->dropColumn('assignment_file_path');
        });
    }
};
