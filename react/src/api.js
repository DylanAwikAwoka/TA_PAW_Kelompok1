const BASE = import.meta.env.VITE_API_BASE ?? '/api';

// Satu pintu untuk semua permintaan API: header JSON, parsing respons, dan pesan error yang terbaca.
export async function ambilJson(path, opsi = {}) {
    const res = await fetch(`${BASE}${path}`, {
        headers: {
            Accept: 'application/json',
            ...(opsi.body ? { 'Content-Type': 'application/json' } : {}),
        },
        ...opsi,
    });

    const data = await res.json().catch(() => ({}));

    if (!res.ok) {
        // 422 Laravel mengirim {errors: {kolom: [pesan]}}; ambil semua pesan agar pengguna tahu penyebabnya.
        const pesanValidasi = data.errors ? Object.values(data.errors).flat().join(' ') : null;
        throw new Error(pesanValidasi || data.message || 'Terjadi kesalahan. Coba lagi.');
    }

    return data;
}

export function rupiah(nilai) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(nilai);
}
