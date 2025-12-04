<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('stock_movimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->restrictOnDelete();
            $table->string('tipo');
            $table->integer('cantidad');
            $table->integer('stock_anterior');
            $table->integer('stock_nuevo');
            $table->string('motivo')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->foreignId('stock_reserva_id')->nullable()->constrained('stock_reservas')->nullOnDelete();
            $table->text('notas')->nullable();
            $table->timestamps();
            
            $table->index(['producto_id', 'created_at']);
            $table->index('tipo');
        });
    }

    public function down(): void {
        Schema::dropIfExists('stock_movimientos');
    }
};