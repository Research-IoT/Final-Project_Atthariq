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
        Schema::create('controller', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_device')->constrained('devices')->onUpdate('cascade')->onDelete('cascade');
            $table->jsonb('controller');
            $table->timestamp('modified_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('controller');
    }
};
