<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->date('date_of_birth')->nullable()->after('bio');
            $table->string('guardian_name')->nullable()->after('date_of_birth');
            $table->string('guardian_phone')->nullable()->after('guardian_name');
            $table->string('guardian_email')->nullable()->after('guardian_phone');
            $table->string('guardian_relation')->nullable()->after('guardian_email');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['date_of_birth', 'guardian_name', 'guardian_phone', 'guardian_email', 'guardian_relation']);
        });
    }
};
