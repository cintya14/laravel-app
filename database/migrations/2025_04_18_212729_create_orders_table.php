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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('total_general',10,2)->nullable();
            $table->string('metodo_pago')->nullable();
            $table->string('estado_pago')->nullable();
            $table->enum('estado_pedido',['nuevo', 'proceso', 'enviado', 'entregado', 'cancelado'])->default('nuevo');
            $table->string('moneda')->nullable();
            $table->decimal('costo_envio', 10, 2)->nullable();
            $table->string('metodo_envio')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
