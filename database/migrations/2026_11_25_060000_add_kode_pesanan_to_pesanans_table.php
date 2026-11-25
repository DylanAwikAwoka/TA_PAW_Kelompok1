<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pesanans', function (Blueprint $table) {
            $table->string('kode_pesanan', 12)->nullable()->unique()->after('id');
        });

        // Pesanan lama ikut mendapat kode agar tetap bisa dicek.
        foreach (DB::table('pesanans')->whereNull('kode_pesanan')->pluck('id') as $id) {
            do {
                $kode = Str::upper(Str::random(12));
            } while (DB::table('pesanans')->where('kode_pesanan', $kode)->exists());

            DB::table('pesanans')->where('id', $id)->update(['kode_pesanan' => $kode]);
        }
    }

    public function down(): void
    {
        Schema::table('pesanans', function (Blueprint $table) {
            $table->dropColumn('kode_pesanan');
        });
    }
};
