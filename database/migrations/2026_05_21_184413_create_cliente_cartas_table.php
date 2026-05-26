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
        Schema::create('clientes_cartas', function (Blueprint $table) {
            $table->id();
    
            $table->string('documento')->nullable()->index();
            $table->string('nombre')->nullable();
            $table->string('calle')->nullable();
            $table->text('observaciones')->nullable();
            $table->string('localidad')->nullable();
    
            $table->boolean('seleccionado')->default(false);
    
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cliente_cartas');
    }
};
