<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('action'); // ex: 'Ganhou', 'Gastou'
            $table->string('user_name'); // Registra o nome do usuário no momento da ação
            $table->string('detail'); // ex: 'Missão: Arrumar cama', 'Recompensa: Videogame'
            $table->integer('amount'); // ex: +10, -50
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
