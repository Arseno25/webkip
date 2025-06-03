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
        Schema::table('kip_recipients', function (Blueprint $table) {
            $table->bigInteger('recipient')->nullable();
            $table->bigInteger('amount')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kip_recipients', function (Blueprint $table) {
            $table->dropColumn('recipient');
            $table->dropColumn('name');
            $table->dropColumn('gender');
            $table->dropColumn('address');
            $table->dropColumn('kip_number');
            $table->dropColumn('amount');
            $table->dropColumn('parent_name');
            $table->dropColumn('grade');
            $table->dropColumn('nisn');
            $table->dropColumn('is_active');
        });
    }
};
