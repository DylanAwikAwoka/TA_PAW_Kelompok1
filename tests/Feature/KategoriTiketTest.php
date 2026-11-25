<?php

namespace Tests\Feature;

use App\Models\Konser;
use App\Models\Pesanan;
use App\Models\Promotor;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class KategoriTiketTest extends TestCase
{
    use RefreshDatabase;

    public function test_harga_dan_total_dihitung_dari_jenis_tiket_dan_hanya_jenis_ituyang_berubah(): void
    {
        [$konser, $vip, $reguler] = $this->siapkanKonser();

        $this->post(route('pesanan.store', $konser), [
            'nama_pemesan' => 'Budi',
            'email' => 'budi@mail.test',
            'jumlah' => 2,
            'ticket_type_id' => $vip->id,
        ])->assertRedirect(route('pesanan.index'));

        $this->assertDatabaseHas('pesanans', [
            'ticket_type_id' => $vip->id,
            'harga_satuan' => 200000,
            'total' => 400000,
        ]);
        $this->assertSame(3, $vip->fresh()->sisa);
        $this->assertSame(10, $reguler->fresh()->sisa);
        $this->assertSame(13, $konser->fresh()->sisa);
    }

    public function test_jumlah_melebihi_stok_jenis_ditolak_dengan_pesan_yang_jelas(): void
    {
        [$konser, $vip, $reguler] = $this->siapkanKonser();
        $vip->update(['sisa' => 4]); // Jumlah uji tetap di bawah batas beli, jadi yang diuji stok.

        $this->post(route('pesanan.store', $konser), [
            'nama_pemesan' => 'Budi',
            'email' => 'budi@mail.test',
            'jumlah' => 5,
            'ticket_type_id' => $vip->id,
        ])->assertSessionHasErrors(['jumlah' => 'Sisa tiket VIP hanya 4.']);

        $this->assertSame(4, $vip->fresh()->sisa);
        $this->assertSame(10, $reguler->fresh()->sisa);
        $this->assertDatabaseCount('pesanans', 0);
    }

    public function test_konser_tanpa_jenis_tiket_tidak_bisa_dipesan(): void
    {
        $konser = $this->buatKonser();

        $this->post(route('pesanan.store', $konser), [
            'nama_pemesan' => 'Budi',
            'email' => 'budi@mail.test',
            'jumlah' => 1,
            'ticket_type_id' => 999,
        ])->assertSessionHasErrors('ticket_type_id');

        $this->assertDatabaseCount('pesanans', 0);
    }

    public function test_jenis_tiket_dari_konser_lain_ditolak(): void
    {
        [$konser] = $this->siapkanKonser();
        [, $vipLain] = $this->siapkanKonser('Konser Lain');

        $this->post(route('pesanan.store', $konser), [
            'nama_pemesan' => 'Budi',
            'email' => 'budi@mail.test',
            'jumlah' => 1,
            'ticket_type_id' => $vipLain->id,
        ])->assertSessionHasErrors(['ticket_type_id' => 'Jenis tiket ini tidak tersedia pada konser tersebut.']);
    }

    public function test_batal_mengembalikan_stok_ke_jenis_yang_sama(): void
    {
        [$konser, $vip, $reguler] = $this->siapkanKonser();
        $pesanan = $this->buatPesanan($konser, $vip, 2);

        $this->actingAs(User::factory()->create())
            ->patch(route('pesanan.batal', $pesanan))
            ->assertRedirect(route('pesanan.index'));

        $this->assertSame(5, $vip->fresh()->sisa);
        $this->assertSame(10, $reguler->fresh()->sisa);
        $this->assertSame(15, $konser->fresh()->sisa);
        $this->assertSame(Pesanan::BATAL, $pesanan->fresh()->status);
    }

    public function test_jenis_tiket_yang_sudah_dipakai_tidak_bisa_dihapus(): void
    {
        [$konser, $vip] = $this->siapkanKonser();
        $this->buatPesanan($konser, $vip, 1);

        $this->actingAs(User::factory()->create())
            ->delete(route('jenis-tiket.destroy', $vip))
            ->assertSessionHas('error', 'Jenis tiket tidak dapat dihapus karena sudah dipakai pesanan.');

        $this->assertDatabaseHas('ticket_types', ['id' => $vip->id]);
    }

    public function test_api_memakai_harga_dan_jenis_dari_server(): void
    {
        [$konser, $vip] = $this->siapkanKonser();

        $this->postJson("/api/konser/{$konser->id}/pesanan", [
            'nama_pemesan' => 'Budi',
            'email' => 'budi@mail.test',
            'jumlah' => 2,
            'ticket_type_id' => $vip->id,
            'harga_satuan' => 1,
            'total' => 1,
            'status' => 'lunas',
        ])->assertCreated()
            ->assertJsonPath('data.harga_satuan', 200000)
            ->assertJsonPath('data.total', 400000)
            ->assertJsonPath('data.jenis_tiket', 'VIP')
            ->assertJsonPath('data.status', 'menunggu_bayar');

        $this->assertSame(3, $vip->fresh()->sisa);
    }

    public function test_api_bayar_mengembalikan_status_terbaru(): void
    {
        [$konser, $vip] = $this->siapkanKonser();
        $pesanan = $this->buatPesanan($konser, $vip, 1);
        Sanctum::actingAs(User::factory()->create());

        $this->patchJson("/api/pesanan/{$pesanan->id}/bayar")
            ->assertOk()
            ->assertJsonPath('data.status', 'lunas');

        $this->patchJson("/api/pesanan/{$pesanan->id}/batal")->assertStatus(409);
        $this->assertSame(4, $vip->fresh()->sisa);
    }

    private function siapkanKonser(string $nama = 'Konser Uji'): array
    {
        $konser = $this->buatKonser($nama);

        $vip = TicketType::create([
            'konser_id' => $konser->id, 'nama' => 'VIP', 'harga' => 200000, 'sisa' => 5,
        ]);
        $reguler = TicketType::create([
            'konser_id' => $konser->id, 'nama' => 'Reguler', 'harga' => 100000, 'sisa' => 10,
        ]);

        return [$konser, $vip, $reguler];
    }

    private function buatKonser(string $nama = 'Konser Uji'): Konser
    {
        $promotor = Promotor::create(['nama' => 'Promotor Uji', 'kontak' => 'uji@mail.test']);

        return Konser::create([
            'promotor_id' => $promotor->id,
            'nama' => $nama,
            'kota' => 'Jakarta',
            'harga' => 100000,
            'sisa' => 0,
            'tanggal' => '2027-01-01',
        ]);
    }

    private function buatPesanan(Konser $konser, TicketType $tipe, int $jumlah): Pesanan
    {
        $pesanan = Pesanan::create([
            'konser_id' => $konser->id,
            'ticket_type_id' => $tipe->id,
            'nama_pemesan' => 'Budi',
            'email' => 'budi@mail.test',
            'jumlah' => $jumlah,
            'harga_satuan' => $tipe->harga,
            'total' => $tipe->harga * $jumlah,
            'status' => Pesanan::MENUNGGU_BAYAR,
        ]);
        $tipe->decrement('sisa', $jumlah);

        return $pesanan;
    }
}
