<?php

namespace Tests\Feature;

use App\Models\Konser;
use App\Models\Pesanan;
use App\Models\Promotor;
use App\Models\TicketType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RiwayatPesananTest extends TestCase
{
    use RefreshDatabase;

    public function test_pesanan_baru_mendapat_kode_12_karakter_unik(): void
    {
        [$konser, $tipe] = $this->siapkanKonser();

        $pertama = $this->buatPesanan($konser, $tipe);
        $kedua = $this->buatPesanan($konser, $tipe);

        $this->assertMatchesRegularExpression('/^[A-Z0-9]{12}$/', $pertama->kode_pesanan);
        $this->assertNotSame($pertama->kode_pesanan, $kedua->kode_pesanan);
        $this->assertDatabaseCount('pesanans', 2);
    }

    public function test_api_cek_mengembalikan_detail_pesanan(): void
    {
        [$konser, $tipe] = $this->siapkanKonser();
        $pesanan = $this->buatPesanan($konser, $tipe);

        $this->getJson('/api/pesanan/cek/'.strtolower($pesanan->kode_pesanan))
            ->assertOk()
            ->assertJsonPath('data.kode_pesanan', $pesanan->kode_pesanan)
            ->assertJsonPath('data.konser', 'Konser Uji')
            ->assertJsonPath('data.jenis_tiket', 'VIP')
            ->assertJsonPath('data.jumlah', 2)
            ->assertJsonPath('data.total', 400000)
            ->assertJsonPath('data.status', 'menunggu_bayar');
    }

    public function test_kode_yang_tidak_ditemukan_ditolak(): void
    {
        $this->getJson('/api/pesanan/cek/AAAAAAAAAAAA')
            ->assertNotFound()
            ->assertJsonPath('message', 'Kode pesanan tidak ditemukan.');
    }

    public function test_halaman_blade_cek_menampilkan_detail_pesanan(): void
    {
        [$konser, $tipe] = $this->siapkanKonser();
        $pesanan = $this->buatPesanan($konser, $tipe);

        $this->get('/cek-pesanan?kode='.strtolower($pesanan->kode_pesanan))
            ->assertOk()
            ->assertSee('Cek Pesanan')
            ->assertSee($pesanan->kode_pesanan)
            ->assertSee('Konser Uji')
            ->assertSee('VIP');
    }

    public function test_halaman_blade_cek_menampilkan_pesan_kode_salah(): void
    {
        $this->get('/cek-pesanan?kode=AAAAAAAAAAAA')
            ->assertOk()
            ->assertSee('Kode pesanan tidak ditemukan.');
    }

    public function test_kode_tidak_berubah_saat_status_dibayar_atau_dibatalkan(): void
    {
        [$konser, $tipe] = $this->siapkanKonser();
        $pesanan = $this->buatPesanan($konser, $tipe);
        $kode = $pesanan->kode_pesanan;

        $pesanan->update(['status' => Pesanan::LUNAS]);

        $this->assertSame($kode, $pesanan->fresh()->kode_pesanan);
        $this->getJson("/api/pesanan/cek/{$kode}")->assertOk();
    }

    private function siapkanKonser(): array
    {
        $promotor = Promotor::create(['nama' => 'Promotor Uji', 'kontak' => 'uji@mail.test']);
        $konser = Konser::create([
            'promotor_id' => $promotor->id,
            'nama' => 'Konser Uji',
            'kota' => 'Jakarta',
            'harga' => 200000,
            'sisa' => 5,
            'tanggal' => '2027-01-01',
        ]);
        $tipe = TicketType::create([
            'konser_id' => $konser->id,
            'nama' => 'VIP',
            'harga' => 200000,
            'sisa' => 5,
        ]);

        return [$konser, $tipe];
    }

    private function buatPesanan(Konser $konser, TicketType $tipe): Pesanan
    {
        return Pesanan::create([
            'konser_id' => $konser->id,
            'ticket_type_id' => $tipe->id,
            'nama_pemesan' => 'Budi',
            'email' => 'budi@mail.test',
            'jumlah' => 2,
            'harga_satuan' => $tipe->harga,
            'total' => $tipe->harga * 2,
            'status' => Pesanan::MENUNGGU_BAYAR,
        ]);
    }
}
