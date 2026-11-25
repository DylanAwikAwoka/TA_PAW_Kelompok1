<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PesananResource;
use App\Models\Konser;
use App\Models\Pesanan;
use App\Models\TicketType;
use App\Services\PesananService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PesananController extends Controller
{
    public function __construct(private PesananService $layanan)
    {
    }

    public function index()
    {
        return PesananResource::collection(Pesanan::with('ticketType')->latest()->get());
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

    public function store(Request $request, Konser $konser): JsonResponse
    {
        $data = $request->validate([
            'nama_pemesan' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100'],
            'jumlah' => ['required', 'integer', 'min:1'],
            'ticket_type_id' => ['required', 'exists:ticket_types,id'],
        ]);

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
