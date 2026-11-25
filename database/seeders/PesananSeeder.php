<?php

namespace Database\Seeders;

use App\Models\Konser;
use App\Models\Pesanan;
use Illuminate\Database\Seeder;

class PesananSeeder extends Seeder
{
    public function run(): void
    {
        $this->buat('Coldplay - Music of the Spheres', 'VIP', 'Budi Santoso', 'budi@mail.test', 2, Pesanan::LUNAS, 6);
        $this->buat('Dewa 19 - Pesta Rakyat', 'Reguler', 'Rina Wijaya', 'rina@mail.test', 4, Pesanan::LUNAS, 5);
        $this->buat('Festival Musik Pantai', 'Reguler', 'Agus Pratama', 'agus@mail.test', 2, Pesanan::BATAL, 4);
        $this->buat('Coldplay - Music of the Spheres', 'Reguler', 'Siti Aminah', 'siti@mail.test', 1, Pesanan::MENUNGGU_BAYAR, 3);
        $this->buat('Malam Akustik Kota', 'Reguler', 'Dewi Lestari', 'dewi@mail.test', 3, Pesanan::LUNAS, 2);
        $this->buat('Dewa 19 - Pesta Rakyat', 'Reguler', 'Hendra Gunawan', 'hendra@mail.test', 2, Pesanan::MENUNGGU_BAYAR, 1);
    }

    private function buat(
        string $namaKonser,
        string $namaJenis,
        string $nama,
        string $email,
        int $jumlah,
        string $status,
        int $hariLalu
    ): void {
        $konser = Konser::where('nama', $namaKonser)->firstOrFail();
        $tipe = $konser->ticketTypes()->where('nama', $namaJenis)->firstOrFail();

        $pesanan = new Pesanan([
            'konser_id' => $konser->id,
            'ticket_type_id' => $tipe->id,
            'nama_pemesan' => $nama,
            'email' => $email,
            'jumlah' => $jumlah,
            'harga_satuan' => $tipe->harga,
            'total' => $tipe->harga * $jumlah,
            'status' => $status,
        ]);
        $pesanan->created_at = now()->subDays($hariLalu);
        $pesanan->save();

        // Pesanan yang tidak batal sudah memakai stok jenis tiket yang bersangkutan.
        if ($status !== Pesanan::BATAL) {
            $tipe->decrement('sisa', $jumlah);
        }
    }
}
