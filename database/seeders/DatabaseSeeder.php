<?php

namespace Database\Seeders;

use App\Models\Konser;
use App\Models\Promotor;
use App\Models\TicketType;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $nusantara = Promotor::create([
            'nama' => 'Nusantara Live', 'kontak' => 'kontak@nusantaralive.test',
        ]);
        $harmoni = Promotor::create([
            'nama' => 'Harmoni Entertainment', 'kontak' => 'info@harmoni.test',
        ]);
        $jogja = Promotor::create([
            'nama' => 'Jogja Sound Project', 'kontak' => 'halo@jogjasound.test',
        ]);

        $coldplay = $this->buatKonser($nusantara, 'Coldplay - Music of the Spheres', 'Jakarta', 1500000, 25, '2027-03-15');
        $this->buatKonser($nusantara, 'NCT Dream - The Dream Show', 'Jakarta', 1200000, 0, '2027-04-02');
        $dewa = $this->buatKonser($jogja, 'Dewa 19 - Pesta Rakyat', 'Yogyakarta', 450000, 120, '2027-02-20');
        $pantai = $this->buatKonser($harmoni, 'Festival Musik Pantai', 'Bali', 600000, 8, '2027-05-10');
        $akustik = $this->buatKonser($harmoni, 'Malam Akustik Kota', 'Bandung', 250000, 60, '2027-01-30');

        foreach ([[$coldplay, 25], [$dewa, 120], [$pantai, 8], [$akustik, 60]] as [$konser, $totalStok]) {
            $this->buatJenis($konser, $totalStok);
        }

        // Konser tanpa stok tetap diberi dua jenis dengan stok 0 agar bisa dikelola admin.
        $this->buatJenis(Konser::where('nama', 'NCT Dream - The Dream Show')->firstOrFail(), 0);

        $this->call(PesananSeeder::class);
        $this->call(AdminSeeder::class);
    }

    private function buatKonser(
        Promotor $promotor,
        string $nama,
        string $kota,
        int $harga,
        int $sisa,
        string $tanggal
    ): Konser {
        return Konser::create([
            'promotor_id' => $promotor->id,
            'nama' => $nama,
            'kota' => $kota,
            'harga' => $harga,
            'sisa' => 0,
            'tanggal' => $tanggal,
        ]);
    }

    private function buatJenis(Konser $konser, int $totalStok): void
    {
        $vip = (int) min(5, floor($totalStok / 5));

        TicketType::create([
            'konser_id' => $konser->id,
            'nama' => 'VIP',
            // Harga VIP mengikuti kebijakan kelompok: 1,5 kali harga dasar, dibulatkan.
            'harga' => (int) round($konser->harga * 1.5),
            'sisa' => $vip,
        ]);

        TicketType::create([
            'konser_id' => $konser->id,
            'nama' => 'Reguler',
            'harga' => $konser->harga,
            'sisa' => $totalStok - $vip,
        ]);
    }
}
