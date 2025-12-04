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
        Schema::table('productos', function (Blueprint $table) {
            $table->integer('stock_disponible')->default(0)->after('precio');
            $table->integer('stock_reservado')->default(0)->after('stock_disponible');
            $table->integer('stock_minimo')->default(5)->after('stock_reservado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn(['stock_disponible', 'stock_reservado', 'stock_minimo']);
            //
        });
    }
};
