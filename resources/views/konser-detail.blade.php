@extends('layouts.main')
@section('title', $konser->nama)
@section('content')
<a href="{{ route('konser.index') }}"
   class="btn btn-outline-secondary mb-3">&larr; Kembali ke daftar</a>
<div class="card">
    <div class="card-body">
        <h1 class="card-title h3">{{ $konser->nama }}</h1>
        <p class="text-muted mb-1">Promotor: {{ $konser->promotor->nama }}</p>
        <p class="text-muted mb-1">Kota: {{ $konser->kota }}</p>
        <p class="mb-1">Tanggal: {{ date('d-m-Y', strtotime($konser->tanggal)) }}</p>
        <p class="mb-1">Harga dasar: Rp{{ number_format($konser->harga, 0, ',', '.') }}</p>
        <p class="mb-1">Total sisa tiket: {{ $konser->sisa }}</p>
        @if ($konser->ticketTypes->isNotEmpty())
            <ul class="mb-2">
                @foreach ($konser->ticketTypes as $tipe)
                    <li>{{ $tipe->nama }} — Rp{{ number_format($tipe->harga, 0, ',', '.') }},
                        sisa {{ $tipe->sisa }}</li>
                @endforeach
            </ul>
        @else
            <div class="alert alert-warning">Konser ini belum memiliki jenis tiket.</div>
        @endif
        @if ($konser->deskripsi)
            <p>{!! nl2br(e($konser->deskripsi)) !!}</p>
        @endif
        @if ($konser->sisa > 0 && $konser->ticketTypes->isNotEmpty())
            <a href="{{ route('pesanan.create', $konser) }}"
               class="btn btn-primary">War Tiket Sekarang</a>
        @else
            <button class="btn btn-secondary" disabled>Sold Out</button>
        @endif
    </div>
</div>
@endsection