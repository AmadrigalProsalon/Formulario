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
        Schema::create('form_fields', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('label');
    $table->string('type');
    $table->boolean('required')->default(false);
    $table->boolean('visible')->default(true);
    $table->string('data_source')->nullable();
    $table->string('data_table')->nullable();
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_fields');
    }
};
