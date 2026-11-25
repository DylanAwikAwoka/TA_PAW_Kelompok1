<?php

namespace App\Http\Controllers;

use App\Http\Requests\PesananRequest;
use App\Models\Konser;
use App\Models\Pesanan;
use App\Models\TicketType;
use App\Services\PesananService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PesananController extends Controller
{
    public function __construct(private PesananService $layanan)
    {
    }

    public function index(): View
    {
        $daftarPesanan = Pesanan::with('konser', 'ticketType')->latest()->get();

        return view('pesanan', compact('daftarPesanan'));
    }

    public function create(Konser $konser): View
    {
        $daftarTipe = $konser->ticketTypes()->orderBy('nama')->get();

        return view('pesanan-create', compact('konser', 'daftarTipe'));
    }

    public function cek(Request $request): View
    {
        $kode = strtoupper(trim((string) $request->query('kode', '')));
        $pesanan = null;
        $galat = null;

        if ($kode !== '') {
            if (! preg_match('/^[A-Z0-9]{12}$/', $kode)) {
                $galat = 'Kode pesanan harus 12 karakter huruf atau angka.';
            } else {
                $pesanan = Pesanan::with('konser', 'ticketType')
                    ->where('kode_pesanan', $kode)
                    ->first();

                if (! $pesanan) {
                    $galat = 'Kode pesanan tidak ditemukan.';
                }
            }
        }

        return view('pesanan-cek', compact('kode', 'pesanan', 'galat'));
    }

    public function store(PesananRequest $request, Konser $konser): RedirectResponse
    {
        $data = $request->validated();

        $this->layanan->buat(
            $konser,
            TicketType::findOrFail($data['ticket_type_id']),
            $data
        );

        return redirect()->route('pesanan.index')
            ->with('success', 'Pesanan berhasil dibuat. Status: menunggu bayar.');
    }

    public function bayar(Pesanan $pesanan): RedirectResponse
    {
        if (! $this->layanan->bayar($pesanan)) {
            return redirect()->route('pesanan.index')
                ->with('error', 'Hanya pesanan menunggu bayar yang bisa dibayar.');
        }

        return redirect()->route('pesanan.index')
            ->with('success', 'Pesanan ditandai lunas.');
    }

    public function batal(Pesanan $pesanan): RedirectResponse
    {
        if (! $this->layanan->batal($pesanan)) {
            return redirect()->route('pesanan.index')
                ->with('error', 'Hanya pesanan menunggu bayar yang bisa dibatalkan.');
        }

        return redirect()->route('pesanan.index')
            ->with('success', 'Pesanan dibatalkan dan stok dikembalikan.');
    }
}
