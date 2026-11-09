<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('enrollment_id');
            $table->decimal('amount', 10, 2);
            $table->string('status')->default('pending');
            $table->string('method')->default('cash');
            $table->date('payment_date');
            $table->text('notes')->nullable();
            $table->foreign('student_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('enrollment_id')->references('id')->on('enrollments')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payments');
    }
};
