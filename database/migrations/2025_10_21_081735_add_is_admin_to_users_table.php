<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // productos -> categorias, marcas (QUITAR CASCADE)
        Schema::table('productos', function (Blueprint $table) {
            $table->dropForeign('productos_categoria_id_foreign');
            $table->dropForeign('productos_marca_id_foreign');

            $table->foreign('categoria_id')
                ->references('id')->on('categorias')
                ->restrictOnDelete(); // <--- BLOQUEA borrado de categoría con productos

            $table->foreign('marca_id')
                ->references('id')->on('marcas')
                ->restrictOnDelete(); // <--- BLOQUEA borrado de marca con productos
        });

        // order_items -> productos (QUITAR CASCADE)
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign('order_items_producto_id_foreign');

            $table->foreign('producto_id')
                ->references('id')->on('productos')
                ->restrictOnDelete(); // <--- BLOQUEA borrado de producto con ventas
        });

        // direcciones -> orders (normalmente mantener restrict también)
        Schema::table('direcciones', function (Blueprint $table) {
            $table->dropForeign('direcciones_order_id_foreign');

            $table->foreign('order_id')
                ->references('id')->on('orders')
                ->restrictOnDelete(); // <--- evita borrar order con direcciones
        });

        // orders -> users (depende de tu regla; usualmente RESTRICT)
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign('orders_user_id_foreign');

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->restrictOnDelete(); // <--- no borrar usuario con pedidos
        });
    }

    public function down(): void
    {
        // Revertir a CASCADE si hiciera falta
        Schema::table('productos', function (Blueprint $table) {
            $table->dropForeign(['categoria_id']);
            $table->dropForeign(['marca_id']);

            $table->foreign('categoria_id')->references('id')->on('categorias')->cascadeOnDelete();
            $table->foreign('marca_id')->references('id')->on('marcas')->cascadeOnDelete();
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['producto_id']);
            $table->foreign('producto_id')->references('id')->on('productos')->cascadeOnDelete();
        });

        Schema::table('direcciones', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
