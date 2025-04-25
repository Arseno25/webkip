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
        Schema::create('kip_recipients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('nisn')->unique()->nullable()->comment('Nomor Induk Siswa Nasional');
            $table->string('kip_number')->unique()->comment('Nomor Kartu Indonesia Pintar');
            $table->enum('gender', ['L', 'P']);
            $table->text('address')->nullable();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->foreignId('subdistrict_id')->constrained()->onDelete('cascade');
            $table->string('parent_name')->nullable();
            $table->enum('grade', ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'])->nullable();
            $table->year('year_received')->comment('Tahun penerimaan KIP');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kip_recipients');
    }
};
