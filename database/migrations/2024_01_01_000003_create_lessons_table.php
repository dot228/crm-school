<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('group_id')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->datetime('scheduled_at')->nullable();
            $table->unsignedInteger('duration_minutes')->default(60);
            $table->string('status')->default('scheduled');
            $table->string('zoom_link')->nullable();
            $table->text('homework')->nullable();
            $table->foreign('course_id')->references('id')->on('courses')->cascadeOnDelete();
            $table->foreign('group_id')->references('id')->on('groups')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('lessons');
    }
};
