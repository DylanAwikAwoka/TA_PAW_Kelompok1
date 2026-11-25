@extends('layouts.main')
@section('title', 'Jenis Tiket')
@section('content')
<h1 class="mb-3">Jenis Tiket: {{ $konser->nama }}</h1>

@if ($errors->any())
    <div class="alert alert-danger">
        @foreach ($errors->all() as $pesan)
            <div>{{ $pesan }}</div>
        @endforeach
    </div>
@endif

@if ($konser->ticketTypes->isEmpty())
    <div class="alert alert-warning">Belum ada jenis tiket. Tambahkan minimal satu jenis agar konser bisa dipesan.</div>
@endif

<table class="table table-bordered align-middle">
    <thead class="table-light">
    <tr>
        <th>Nama</th>
        <th>Harga</th>
        <th>Sisa</th>
        <th>Aksi</th>
    </tr>
    </thead>
    <tbody>
    @forelse ($konser->ticketTypes as $tipe)
        <tr>
            <td colspan="4">
                <div class="d-flex flex-wrap gap-2 align-items-end">
                    <form action="{{ route('jenis-tiket.update', $tipe) }}" method="POST" class="row g-2 align-items-end">
                        @csrf
                        @method('PUT')
                        <div class="col-auto">
                            <label class="form-label">Nama</label>
                            <input type="text" name="nama" class="form-control" value="{{ $tipe->nama }}" required maxlength="100">
                        </div>
                        <div class="col-auto">
                            <label class="form-label">Harga (Rp)</label>
                            <input type="number" name="harga" class="form-control" min="0" value="{{ $tipe->harga }}" required>
                        </div>
                        <div class="col-auto">
                            <label class="form-label">Sisa</label>
                            <input type="number" name="sisa" class="form-control" min="0" value="{{ $tipe->sisa }}" required>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-sm btn-primary">Simpan</button>
                        </div>
                    </form>
                    <form action="{{ route('jenis-tiket.destroy', $tipe) }}" method="POST"
                          onsubmit="return confirm('Hapus jenis tiket ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                    </form>
                </div>
            </td>
        </tr>
    @empty
        <tr><td colspan="4" class="text-center">Belum ada jenis tiket.</td></tr>
    @endforelse
    </tbody>
</table>

<h2 class="h5 mt-4">Tambah Jenis Tiket</h2>
<form action="{{ route('jenis-tiket.store', $konser) }}" method="POST" class="row g-2 align-items-end">
    @csrf
    <div class="col-md-4">
        <label for="nama" class="form-label">Nama</label>
        <input type="text" name="nama" id="nama" class="form-control @error('nama') is-invalid @enderror"
               value="{{ old('nama') }}" placeholder="mis. VIP" required maxlength="100">
        @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-3">
        <label for="harga" class="form-label">Harga (Rp)</label>
        <input type="number" name="harga" id="harga" class="form-control @error('harga') is-invalid @enderror"
               min="0" value="{{ old('harga') }}" required>
        @error('harga') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-2">
        <label for="sisa" class="form-label">Sisa</label>
        <input type="number" name="sisa" id="sisa" class="form-control @error('sisa') is-invalid @enderror"
               min="0" value="{{ old('sisa') }}" required>
        @error('sisa') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-3">
        <button type="submit" class="btn btn-success">Tambah</button>
    </div>
</form>

<a href="{{ route('konser.index') }}" class="btn btn-link mt-3">&larr; Kembali ke konser</a>
@endsection
