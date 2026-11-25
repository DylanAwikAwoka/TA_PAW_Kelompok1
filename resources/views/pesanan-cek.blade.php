@extends('layouts.main')
@section('title', 'Cek Pesanan')
@section('content')
<h1 class="mb-3">Cek Pesanan</h1>

<p class="text-muted">Masukkan kode 12 karakter yang muncul setelah memesan tiket.</p>

<form action="{{ route('pesanan.cek') }}" method="GET" class="row g-2 mb-3">
    <div class="col-md-4">
        <input type="text" name="kode" class="form-control" value="{{ $kode }}"
               placeholder="mis. A1B2C3D4E5F6" maxlength="12" required>
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-primary">Cek status</button>
    </div>
</form>

@if ($galat)
    <div class="alert alert-danger">{{ $galat }}</div>
@endif

@if (! $kode && ! $galat)
    <div class="alert alert-secondary">Belum ada pesanan yang dicek.</div>
@endif

@if ($pesanan)
    <div class="card">
        <div class="card-body">
            <h2 class="h5">{{ $pesanan->konser->nama }}</h2>
            <p class="mb-1">Kode pesanan: <code>{{ $pesanan->kode_pesanan }}</code></p>
            <p class="mb-1">Jenis tiket: {{ $pesanan->ticketType->nama ?? '-' }}</p>
            <p class="mb-1">Jumlah: {{ $pesanan->jumlah }} tiket</p>
            <p class="mb-1">Total: Rp{{ number_format($pesanan->total, 0, ',', '.') }}</p>
            <p class="mb-1">Dibuat: {{ $pesanan->created_at->format('d-m-Y H:i') }}</p>
            <p class="mb-0">Status: @include('partials.badge-status', ['status' => $pesanan->status])</p>
        </div>
    </div>
@endif
@endsection
