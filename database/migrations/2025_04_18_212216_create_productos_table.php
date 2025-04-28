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
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categoria_id')->constrained('categorias')->cascadeOnDelete();
            $table->foreignId('marca_id')->constrained('marcas')->cascadeOnDelete();
            $table->string('nombre');
            $table->string('slug')->unique();
            $table->json('imagenes')->nullable(); 
            $table->longText('descripcion')->nullable();
            $table->decimal('precio',10,2);
            $table->boolean('activo')->default(true);
            $table->boolean('destacado')->default(false);
            $table->boolean('en_stock')->default(true);
            $table->boolean('en_venta')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
