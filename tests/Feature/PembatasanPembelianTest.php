<?php

namespace Tests\Feature;

use App\Models\Konser;
use App\Models\Promotor;
use App\Models\TicketType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PembatasanPembelianTest extends TestCase
{
    use RefreshDatabase;

    public function test_lima_tiket_per_pesanan_diterima(): void
    {
        [$konser, $tipe] = $this->siapkanKonser();

        $this->post(route('pesanan.store', $konser), $this->data(5, $tipe->id))
            ->assertRedirect(route('pesanan.index'));

        $this->assertDatabaseHas('pesanans', ['jumlah' => 5, 'ticket_type_id' => $tipe->id]);
        $this->assertSame(5, $tipe->fresh()->sisa);
    }

    public function test_enam_tiket_ditolak_dan_stok_tidak_berubah(): void
    {
        [$konser, $tipe] = $this->siapkanKonser();

        $this->post(route('pesanan.store', $konser), $this->data(6, $tipe->id))
            ->assertSessionHasErrors(['jumlah' => 'Maksimal 5 tiket per pesanan.']);

        $this->assertDatabaseCount('pesanans', 0);
        $this->assertSame(10, $tipe->fresh()->sisa);
    }

    public function test_batas_diambil_dari_config_bukan_angka_tetap(): void
    {
        config(['tiketwar.batas_pembelian_per_pesanan' => 2]);
        [$konser, $tipe] = $this->siapkanKonser();

        $this->post(route('pesanan.store', $konser), $this->data(3, $tipe->id))
            ->assertSessionHasErrors(['jumlah' => 'Maksimal 2 tiket per pesanan.']);

        $this->assertDatabaseCount('pesanans', 0);
    }

    public function test_api_memakai_batas_yang_sama(): void
    {
        [$konser, $tipe] = $this->siapkanKonser();

        $this->postJson("/api/konser/{$konser->id}/pesanan", $this->data(6, $tipe->id))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['jumlah' => 'Maksimal 5 tiket per pesanan.']);

        $this->postJson("/api/konser/{$konser->id}/pesanan", $this->data(5, $tipe->id))
            ->assertCreated()
            ->assertJsonPath('data.jumlah', 5);

        $this->assertDatabaseCount('pesanans', 1);
    }

    private function siapkanKonser(): array
    {
        $promotor = Promotor::create(['nama' => 'Promotor Uji', 'kontak' => 'uji@mail.test']);
        $konser = Konser::create([
            'promotor_id' => $promotor->id,
            'nama' => 'Konser Uji',
            'kota' => 'Jakarta',
            'harga' => 100000,
            'sisa' => 10,
            'tanggal' => '2027-01-01',
        ]);
        $tipe = TicketType::create([
            'konser_id' => $konser->id,
            'nama' => 'Reguler',
            'harga' => 100000,
            'sisa' => 10,
        ]);

        return [$konser, $tipe];
    }

    private function data(int $jumlah, int $tipeId): array
    {
        return [
            'nama_pemesan' => 'Budi',
            'email' => 'budi@mail.test',
            'jumlah' => $jumlah,
            'ticket_type_id' => $tipeId,
        ];
    }
}
