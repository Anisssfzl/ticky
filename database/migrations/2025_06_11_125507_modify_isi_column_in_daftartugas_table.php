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
        // Metode aman: update data kosong terlebih dahulu
        DB::table('daftartugas')
            ->whereNull('isi')
            ->orWhere('isi', '')
            ->update(['isi' => '']);

        // Kemudian ubah struktur kolom
        Schema::table('daftartugas', function (Blueprint $table) {
            $table->text('isi')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daftartugas', function (Blueprint $table) {
            // Set default value untuk semua data null sebelum mengubah kembali
            DB::table('daftartugas')
                ->whereNull('isi')
                ->update(['isi' => '']);
                
            $table->text('isi')->nullable(false)->change();
        });
    }
};