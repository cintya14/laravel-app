<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('stock_reservas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->restrictOnDelete();
            $table->string('session_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('cantidad');
            $table->timestamp('expira_en');
            $table->string('estado')->default('activa');
            $table->timestamps();
            
            $table->index(['session_id', 'estado']);
            $table->index(['user_id', 'estado']);
            $table->index('expira_en');
        });
    }

    public function down(): void {
        Schema::dropIfExists('stock_reservas');
    }
};