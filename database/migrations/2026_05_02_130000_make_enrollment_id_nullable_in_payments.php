<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        \DB::statement('ALTER TABLE payments MODIFY enrollment_id BIGINT UNSIGNED NULL');
    }

    public function down()
    {
        \DB::statement('ALTER TABLE payments MODIFY enrollment_id BIGINT UNSIGNED NOT NULL');
    }
};
