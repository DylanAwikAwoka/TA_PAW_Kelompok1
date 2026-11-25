<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Pesanan extends Model
{
    public const MENUNGGU_BAYAR = 'menunggu_bayar';
    public const LUNAS = 'lunas';
    public const BATAL = 'batal';

    protected $fillable = [
        'konser_id',
        'ticket_type_id',
        'nama_pemesan',
        'email',
        'jumlah',
        'harga_satuan',
        'total',
        'status',
    ];

    protected static function booted(): void
    {
        // Satu tempat untuk semua jalan pembuatan pesanan (web, API, seeder, test).
        static::creating(function (Pesanan $pesanan) {
            if ($pesanan->kode_pesanan) {
                return;
            }

            do {
                $kode = Str::upper(Str::random(12));
            } while (static::where('kode_pesanan', $kode)->exists());

            $pesanan->kode_pesanan = $kode;
        });
    }

    public function konser(): BelongsTo
    {
        return $this->belongsTo(Konser::class);
    }

    public function ticketType(): BelongsTo
    {
        return $this->belongsTo(TicketType::class);
    }
}
