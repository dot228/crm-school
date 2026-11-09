<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up()
    {
        \DB::statement("ALTER TABLE schedules MODIFY COLUMN status ENUM('draft','scheduled','completed','cancelled','no_show') NOT NULL DEFAULT 'scheduled'");
    }

    public function down()
    {
        \DB::statement("ALTER TABLE schedules MODIFY COLUMN status ENUM('scheduled','completed','cancelled','no_show') NOT NULL DEFAULT 'scheduled'");
    }
};
