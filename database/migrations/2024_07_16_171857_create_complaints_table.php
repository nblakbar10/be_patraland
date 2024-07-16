<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('complaints', function (Blueprint $table) {
            // $table->integer('complaint_id')->primary();
            $table->id();
            $table->integer('user_id');
            $table->string('home_address');
            $table->string('description');
            $table->string('handling_asset'); //image
            $table->string('complaint_asset'); //image
            $table->string('sparepart');
            $table->string('handling_description');
            $table->enum('status', ['receive', 'ongoing', 'done']);
            $table->integer('user_handler_id');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
