<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateErpRecordsTable extends Migration
{
    public function up()
    {
        Schema::create('erp_records', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('module', 40)->index();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status', 30)->default('active');
            $table->decimal('amount', 14, 2)->nullable();
            $table->date('record_date')->nullable();
            $table->unsignedBigInteger('assigned_user_id')->nullable()->index();
            $table->unsignedBigInteger('created_by')->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('assigned_user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('erp_records');
    }
}
