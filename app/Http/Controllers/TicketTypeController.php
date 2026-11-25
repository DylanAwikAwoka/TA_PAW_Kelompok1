<?php

namespace App\Http\Controllers;

use App\Models\Konser;
use App\Models\TicketType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TicketTypeController extends Controller
{
    public function index(Konser $konser): View
    {
        return view('jenis-tiket', ['konser' => $konser->load('ticketTypes')]);
    }

    public function store(Request $request, Konser $konser): RedirectResponse
    {
        $data = $request->validate($this->aturan());
        $data['konser_id'] = $konser->id;

        DB::transaction(fn () => TicketType::create($data));

        return back()->with('success', 'Jenis tiket berhasil ditambahkan.');
    }

    public function update(Request $request, TicketType $ticketType): RedirectResponse
    {
        $data = $request->validate($this->aturan());

        // Stok konser ikut berubah lewat event model, jadi update dan event harus satu transaksi.
        DB::transaction(fn () => $ticketType->update($data));

        return back()->with('success', 'Jenis tiket berhasil diubah.');
    }

    public function destroy(TicketType $ticketType): RedirectResponse
    {
        if ($ticketType->pesanans()->exists()) {
            return back()->with('error', 'Jenis tiket tidak dapat dihapus karena sudah dipakai pesanan.');
        }

        DB::transaction(fn () => $ticketType->delete());

        return back()->with('success', 'Jenis tiket berhasil dihapus.');
    }

    private function aturan(): array
    {
        return [
            'nama' => ['required', 'string', 'max:100'],
            'harga' => ['required', 'integer', 'min:0'],
            'sisa' => ['required', 'integer', 'min:0'],
        ];
    }
}
