<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PesananRequest;
use App\Http\Resources\PesananResource;
use App\Models\Konser;
use App\Models\Pesanan;
use App\Models\TicketType;
use App\Services\PesananService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class PesananController extends Controller
{
    public function __construct(private PesananService $layanan)
    {
    }

    public function index()
    {
        return PesananResource::collection(Pesanan::with('konser', 'ticketType')->latest()->get());
    }

    public function cek(string $kode): PesananResource|JsonResponse
    {
        $pesanan = Pesanan::with('konser', 'ticketType')
            ->where('kode_pesanan', Str::upper($kode))
            ->first();

        if (! $pesanan) {
            return response()->json(['message' => 'Kode pesanan tidak ditemukan.'], 404);
        }

        return new PesananResource($pesanan);
    }

    public function bayar(Pesanan $pesanan): PesananResource|JsonResponse
    {
        if (! $this->layanan->bayar($pesanan)) {
            return response()->json([
                'message' => 'Hanya pesanan berstatus menunggu_bayar yang bisa dibayar.',
            ], 409);
        }

        // Service memakai instance lain, jadi baca ulang agar status di respons tidak basi.
        return new PesananResource($pesanan->fresh('ticketType'));
    }

    public function batal(Pesanan $pesanan): PesananResource|JsonResponse
    {
        if (! $this->layanan->batal($pesanan)) {
            return response()->json([
                'message' => 'Hanya pesanan berstatus menunggu_bayar yang bisa dibatalkan.',
            ], 409);
        }

        return new PesananResource($pesanan->fresh('ticketType'));
    }

    public function store(PesananRequest $request, Konser $konser): JsonResponse
    {
        $data = $request->validated();

        $pesanan = $this->layanan->buat(
            $konser,
            TicketType::findOrFail($data['ticket_type_id']),
            $data
        );

        return (new PesananResource($pesanan->load('ticketType')))
            ->response()
            ->setStatusCode(201);
    }
}
