<?php

namespace App\Http\Controllers;

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

    public function store(Request $request, Konser $konser): RedirectResponse
    {
        $data = $request->validate([
            'nama_pemesan' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100'],
            'jumlah' => ['required', 'integer', 'min:1'],
            'ticket_type_id' => ['required', 'exists:ticket_types,id'],
        ]);

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
