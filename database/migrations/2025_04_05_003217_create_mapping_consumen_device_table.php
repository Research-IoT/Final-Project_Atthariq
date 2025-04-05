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
        Schema::create('mapping_consumen_device', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_consumen')->constrained('consumen')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('id_device')->constrained('devices')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamp('added_at');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mapping_consumen_device');
    }
};
