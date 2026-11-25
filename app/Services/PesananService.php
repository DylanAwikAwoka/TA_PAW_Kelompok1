<?php

namespace App\Services;

use App\Models\Konser;
use App\Models\Pesanan;
use App\Models\TicketType;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PesananService
{
    // Satu tempat untuk membuat, membayar, dan membatalkan pesanan agar web dan API tidak beda aturan.
    public function buat(Konser $konser, TicketType $tipe, array $data): Pesanan
    {
        if (! $konser->ticketTypes()->exists()) {
            throw ValidationException::withMessages([
                'ticket_type_id' => 'Konser ini belum memiliki jenis tiket.',
            ]);
        }

        return DB::transaction(function () use ($konser, $tipe, $data) {
            // Yang dikunci adalah baris jenis tiket, karena stok ada di sana.
            $terkunci = TicketType::lockForUpdate()->findOrFail($tipe->id);

            if ($terkunci->konser_id !== $konser->id) {
                throw ValidationException::withMessages([
                    'ticket_type_id' => 'Jenis tiket ini tidak tersedia pada konser tersebut.',
                ]);
            }

            if ($data['jumlah'] > $terkunci->sisa) {
                throw ValidationException::withMessages([
                    'jumlah' => "Sisa tiket {$terkunci->nama} hanya {$terkunci->sisa}.",
                ]);
            }

            $pesanan = Pesanan::create([
                'konser_id' => $konser->id,
                'ticket_type_id' => $terkunci->id,
                'nama_pemesan' => $data['nama_pemesan'],
                'email' => $data['email'],
                'jumlah' => $data['jumlah'],
                'harga_satuan' => $terkunci->harga,
                'total' => $terkunci->harga * $data['jumlah'],
                'status' => Pesanan::MENUNGGU_BAYAR,
            ]);

            $terkunci->decrement('sisa', $data['jumlah']);

            return $pesanan;
        });
    }

    public function bayar(Pesanan $pesanan): bool
    {
        return DB::transaction(function () use ($pesanan) {
            $terkunci = Pesanan::lockForUpdate()->findOrFail($pesanan->id);

            if ($terkunci->status !== Pesanan::MENUNGGU_BAYAR) {
                return false;
            }

            $terkunci->update(['status' => Pesanan::LUNAS]);

            return true;
        });
    }

    public function batal(Pesanan $pesanan): bool
    {
        return DB::transaction(function () use ($pesanan) {
            $terkunci = Pesanan::lockForUpdate()->findOrFail($pesanan->id);

            if ($terkunci->status !== Pesanan::MENUNGGU_BAYAR) {
                return false;
            }

            if ($terkunci->ticket_type_id) {
                $jenis = TicketType::lockForUpdate()->findOrFail($terkunci->ticket_type_id);
                $jenis->increment('sisa', $terkunci->jumlah);
            }

            $terkunci->update(['status' => Pesanan::BATAL]);

            return true;
        });
    }
}
