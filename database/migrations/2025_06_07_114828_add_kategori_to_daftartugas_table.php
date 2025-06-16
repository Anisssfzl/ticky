<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('daftartugas', function (Blueprint $table) {
            $table->enum('kategori', ['Work', 'Study', 'Personal'])->default('Personal')->after('is_important');
        });
    }

    public function down()
    {
        Schema::table('daftartugas', function (Blueprint $table) {
            $table->dropColumn('kategori');
        });
    }
};