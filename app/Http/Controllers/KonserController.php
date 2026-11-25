<?php

namespace App\Http\Controllers;

use App\Models\Konser;
use App\Models\Promotor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KonserController extends Controller
{
    public function index(Request $request): View
    {
        $daftarKonser = Konser::with('promotor')
            ->when($request->query('kota'), fn ($query, $kota) => $query->where('kota', $kota))
            ->orderBy('tanggal')
            ->get();

        $daftarKota = Konser::select('kota')->distinct()->orderBy('kota')->pluck('kota');

        return view('konser', compact('daftarKonser', 'daftarKota'));
    }

    public function show($id): View
    {
        $konser = Konser::with('promotor', 'ticketTypes')->findOrFail($id);
        return view('konser-detail', compact('konser'));
    }

    public function create(): View
    {
        $daftarPromotor = Promotor::orderBy('nama')->get();
        return view('konser-create', compact('daftarPromotor'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'promotor_id' => ['required', 'exists:promotors,id'],
            'nama' => ['required', 'string', 'max:255'],
            'kota' => ['required', 'string', 'max:100'],
            'harga' => ['required', 'integer', 'min:0'],
            'tanggal' => ['required', 'date'],
            'deskripsi' => ['nullable', 'string'],
        ]);

        // Stok kini diisi lewat jenis tiket, bukan langsung pada konser.
        $data['sisa'] = 0;
        Konser::create($data);

        return redirect()->route('konser.index')
            ->with('success', 'Konser berhasil ditambahkan.');
    }

    public function edit($id): View
    {
        $konser = Konser::findOrFail($id);
        $daftarPromotor = Promotor::orderBy('nama')->get();
        return view('konser-edit', compact('konser', 'daftarPromotor'));
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $konser = Konser::findOrFail($id);

        $data = $request->validate([
            'promotor_id' => ['required', 'exists:promotors,id'],
            'nama' => ['required', 'string', 'max:255'],
            'kota' => ['required', 'string', 'max:100'],
            'harga' => ['required', 'integer', 'min:0'],
            'tanggal' => ['required', 'date'],
            'deskripsi' => ['nullable', 'string'],
        ]);

        $konser->update($data);

        return redirect()->route('konser.index')
            ->with('success', 'Konser berhasil diubah.');
    }

    public function destroy($id): RedirectResponse
    {
        $konser = Konser::findOrFail($id);

        // Pesanan merujuk konser ini; tanpa pesan ini pengguna hanya melihat error 500 dari database.
        if ($konser->pesanans()->exists()) {
            return redirect()->route('konser.index')
                ->with('error', 'Konser tidak dapat dihapus karena masih memiliki pesanan.');
        }

        $konser->delete();

        return redirect()->route('konser.index')
            ->with('success', 'Konser berhasil dihapus.');
    }
}