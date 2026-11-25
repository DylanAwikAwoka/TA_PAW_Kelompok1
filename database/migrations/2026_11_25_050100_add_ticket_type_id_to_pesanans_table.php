<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pesanans', function (Blueprint $table) {
            $table->foreignId('ticket_type_id')->nullable()->after('konser_id')
                ->constrained('ticket_types')->nullOnDelete();
        });

        // Data lama: pesanan tetap terhubung ke satu jenis Reguler pada konsernya.
        foreach (DB::table('konsers')->select('id', 'harga', 'sisa')->get() as $konser) {
            $jenisId = DB::table('ticket_types')->insertGetId([
                'konser_id' => $konser->id,
                'nama' => 'Reguler',
                'harga' => $konser->harga,
                'sisa' => $konser->sisa,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('pesanans')->where('konser_id', $konser->id)
                ->update(['ticket_type_id' => $jenisId]);
        }
    }

    public function down(): void
    {
        Schema::table('pesanans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('ticket_type_id');
        });
    }
};
