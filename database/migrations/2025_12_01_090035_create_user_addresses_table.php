<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('user_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('nombre');
            $table->string('apellido');
            $table->string('telefono');
            $table->string('direccion');
            $table->string('ciudad');
            $table->string('departamento');
            $table->string('codigo_postal');
            $table->boolean('predeterminada')->default(false);
            $table->timestamps();
            
            $table->index(['user_id', 'predeterminada']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('user_addresses');
    }
};