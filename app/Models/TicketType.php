<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketType extends Model
{
    protected $fillable = [
        'konser_id',
        'nama',
        'harga',
        'sisa',
    ];

    protected static function booted(): void
    {
        // konser.sisa hanya cerminan total stok semua jenis, jadi tidak diubah manual lagi.
        static::created(function (TicketType $tipe) {
            $tipe->konser()->increment('sisa', $tipe->sisa);
        });

        static::updating(function (TicketType $tipe) {
            if ($tipe->isDirty('sisa')) {
                $tipe->konser()->increment('sisa', $tipe->sisa - $tipe->getRawOriginal('sisa'));
            }
        });

        static::deleted(function (TicketType $tipe) {
            $tipe->konser()->decrement('sisa', $tipe->sisa);
        });
    }

    public function konser(): BelongsTo
    {
        return $this->belongsTo(Konser::class);
    }

    public function pesanans(): HasMany
    {
        return $this->hasMany(Pesanan::class);
    }
}
