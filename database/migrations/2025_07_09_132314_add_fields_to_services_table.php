<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->integer('price')->default(0)->after('image');
            $table->integer('discount')->default(0)->after('image');
            $table->integer('duration')->default(0)->after('image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('price');
            $table->dropColumn('discount');
            $table->dropColumn('duration');
        });
    }
};
