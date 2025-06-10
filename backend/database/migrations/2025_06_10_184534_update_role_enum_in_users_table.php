<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Para SQLite, vamos apenas alterar o tipo para string
        // No código da aplicação vamos validar os valores aceitos
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('passenger')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('passenger')->change();
        });
    }
};
