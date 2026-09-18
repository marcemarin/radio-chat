<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            // Payload crudo del proveedor (key + message): Evolution lo necesita para descargar el media sin DB.
            $table->jsonb('raw')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('messages', fn (Blueprint $t) => $t->dropColumn('raw'));
    }
};
