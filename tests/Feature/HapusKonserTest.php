<?php

namespace Tests\Feature;

use App\Models\Konser;
use App\Models\Pesanan;
use App\Models\Promotor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HapusKonserTest extends TestCase
{
    use RefreshDatabase;

    public function test_konser_yang_masih_punya_pesanan_tidak_bisa_dihapus(): void
    {
        $konser = $this->buatKonser();

        Pesanan::create([
            'konser_id' => $konser->id,
            'nama_pemesan' => 'Budi',
            'email' => 'budi@mail.test',
            'jumlah' => 1,
            'harga_satuan' => $konser->harga,
            'total' => $konser->harga,
            'status' => Pesanan::MENUNGGU_BAYAR,
        ]);

        $respons = $this->actingAs(User::factory()->create())
            ->delete("/konser/{$konser->id}");

        $respons->assertRedirect(route('konser.index'));
        $respons->assertSessionHas('error');
        $this->assertDatabaseHas('konsers', ['id' => $konser->id]);
    }

    public function test_konser_tanpa_pesanan_bisa_dihapus(): void
    {
        $konser = $this->buatKonser();

        $respons = $this->actingAs(User::factory()->create())
            ->delete("/konser/{$konser->id}");

        $respons->assertRedirect(route('konser.index'));
        $respons->assertSessionHas('success');
        $this->assertDatabaseMissing('konsers', ['id' => $konser->id]);
    }

    private function buatKonser(): Konser
    {
        $promotor = Promotor::create(['nama' => 'Promotor Uji', 'kontak' => 'uji@mail.test']);

        return Konser::create([
            'promotor_id' => $promotor->id,
            'nama' => 'Konser Uji',
            'kota' => 'Jakarta',
            'harga' => 100000,
            'sisa' => 10,
            'tanggal' => '2027-01-01',
        ]);
    }
}
