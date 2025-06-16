<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddStatusToDaftartugasTable extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('daftartugas', 'status')) {
            Schema::table('daftartugas', function (Blueprint $table) {
                $table->enum('status', ['SELESAI', 'BELUM SELESAI', 'TERLAMBAT'])
                      ->default('BELUM SELESAI')
                      ->after('isi');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('daftartugas', 'status')) {
            Schema::table('daftartugas', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
}

