import { useState } from 'react';
import { ambilJson, rupiah } from '../api.js';

const labelStatus = {
    menunggu_bayar: 'Menunggu bayar',
    lunas: 'Lunas',
    batal: 'Dibatalkan',
};

export default function CekPesanan() {
    const [kode, setKode] = useState('');
    const [hasil, setHasil] = useState(null);
    const [galat, setGalat] = useState(null);
    const [mencari, setMencari] = useState(false);

    async function cari(e) {
        e.preventDefault();
        const kodeBersih = kode.trim().toUpperCase();
        setHasil(null);
        setGalat(null);
        setMencari(true);

        try {
            const data = await ambilJson(`/pesanan/cek/${kodeBersih}`);
            setHasil(data.data);
        } catch (err) {
            setGalat(err.message);
        } finally {
            setMencari(false);
        }
    }

    return (
        <>
            <h1>Cek Pesanan</h1>
            <p className="meta">Masukkan kode 12 karakter yang muncul setelah memesan tiket.</p>

            <section className="form-pesanan">
                <form onSubmit={cari}>
                    <label>
                        Kode pesanan
                        <input
                            type="text"
                            value={kode}
                            onChange={(e) => setKode(e.target.value)}
                            placeholder="mis. A1B2C3D4E5F6"
                            minLength={12}
                            maxLength={12}
                            pattern="[A-Za-z0-9]{12}"
                            required
                        />
                    </label>
                    <button type="submit" className="tombol" disabled={mencari}>
                        {mencari ? 'Mencari…' : 'Cek status'}
                    </button>
                </form>

                {galat && <p className="state state-error">{galat}</p>}
                {!hasil && !galat && !mencari && <p className="state">Belum ada pesanan yang dicek.</p>}

                {hasil && (
                    <article className="detail hasil-cek">
                        <h2>{hasil.konser || 'Konser'}</h2>
                        <p className="kode">Kode: {hasil.kode_pesanan}</p>
                        <p className="meta">Jenis tiket: {hasil.jenis_tiket || '-'}</p>
                        <p className="meta">Jumlah: {hasil.jumlah} tiket</p>
                        <p className="harga">Total: {rupiah(hasil.total)}</p>
                        <p className="meta">Dibuat: {hasil.dibuat}</p>
                        <p
                            className={
                                hasil.status === 'batal'
                                    ? 'state state-error'
                                    : hasil.status === 'lunas'
                                      ? 'state state-sukses'
                                      : 'state'
                            }
                        >
                            Status: {labelStatus[hasil.status] || hasil.status}
                        </p>
                    </article>
                )}
            </section>
        </>
    );
}
