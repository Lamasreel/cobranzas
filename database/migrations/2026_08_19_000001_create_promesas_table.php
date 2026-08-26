<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('promesas')) {
            return;
        }

        Schema::create('promesas', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->unsignedBigInteger('dni')->index();
            $table->string('cliente', 120)->nullable();
            $table->decimal('saldo_total', 12, 2)->default(0.00);
            $table->decimal('importe_promesa', 12, 2)->nullable();
            $table->date('fecha_promesa')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamp('creado_en')->nullable();
            $table->timestamp('actualizado_en')->nullable();

            $table->unique('dni');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promesas');
    }
};