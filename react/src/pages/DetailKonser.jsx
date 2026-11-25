import { useEffect, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import { ambilJson, rupiah } from '../api.js';

export default function DetailKonser() {
    const { id } = useParams();
    const [konser, setKonser] = useState(null);
    const [memuat, setMemuat] = useState(true);
    const [galat, setGalat] = useState(null);

    const [form, setForm] = useState({ nama_pemesan: '', email: '', jumlah: 1 });
    const [mengirim, setMengirim] = useState(false);
    const [pesanSukses, setPesanSukses] = useState(null);
    const [pesanGalat, setPesanGalat] = useState(null);

    useEffect(() => {
        let batal = false;

        ambilJson(`/konser/${id}`)
            .then((hasil) => {
                if (!batal) setKonser(hasil.data);
            })
            .catch((e) => {
                if (!batal) setGalat(e.message);
            })
            .finally(() => {
                if (!batal) setMemuat(false);
            });

        return () => {
            batal = true;
        };
    }, [id]);

    function ubah(perubahan) {
        setForm((lama) => ({ ...lama, ...perubahan }));
    }

    async function kirim(e) {
        e.preventDefault();
        setMengirim(true);
        setPesanSukses(null);
        setPesanGalat(null);

        try {
            const hasil = await ambilJson(`/konser/${id}/pesanan`, {
                method: 'POST',
                body: JSON.stringify({ ...form, jumlah: Number(form.jumlah) }),
            });
            setPesanSukses(hasil.data);
            setKonser((lama) => (lama ? { ...lama, sisa: lama.sisa - hasil.data.jumlah } : lama));
            setForm({ nama_pemesan: '', email: '', jumlah: 1 });
        } catch (err) {
            setPesanGalat(err.message);
        } finally {
            setMengirim(false);
        }
    }

    if (memuat) return <p className="state">Memuat detail konser…</p>;
    if (galat) return <p className="state state-error">Konser tidak ditemukan atau gagal dimuat: {galat}</p>;

    return (
        <>
            <Link to="/" className="tautan-kembali">
                ← Kembali ke daftar
            </Link>

            <article className="detail">
                <h1>{konser.nama}</h1>
                <p className="meta">
                    {konser.kota} · {konser.tanggal}
                    {konser.promotor ? ` · ${konser.promotor}` : ''}
                </p>
                <p className="harga">{rupiah(konser.harga)} / tiket</p>
                <p className={konser.sisa > 0 ? 'stok' : 'stok stok-habis'}>
                    {konser.sisa > 0 ? `Sisa ${konser.sisa} tiket` : 'Tiket habis'}
                </p>
            </article>

            <section className="form-pesanan">
                <h2>Pesan Tiket</h2>

                {konser.sisa === 0 && (
                    <p className="state state-error">Tiket konser ini sudah habis.</p>
                )}

                {pesanSukses && (
                    <p className="state state-sukses">
                        Pesanan #{pesanSukses.id} berhasil dibuat. Status: {pesanSukses.status}, total{' '}
                        {rupiah(pesanSukses.total)}.
                    </p>
                )}
                {pesanGalat && <p className="state state-error">{pesanGalat}</p>}

                <form onSubmit={kirim}>
                    <label>
                        Nama pemesan
                        <input
                            type="text"
                            value={form.nama_pemesan}
                            onChange={(e) => ubah({ nama_pemesan: e.target.value })}
                            required
                            maxLength={100}
                        />
                    </label>
                    <label>
                        Email
                        <input
                            type="email"
                            value={form.email}
                            onChange={(e) => ubah({ email: e.target.value })}
                            required
                            maxLength={100}
                        />
                    </label>
                    <label>
                        Jumlah tiket
                        <input
                            type="number"
                            min="1"
                            max={konser.sisa}
                            value={form.jumlah}
                            onChange={(e) => ubah({ jumlah: e.target.value })}
                            required
                        />
                    </label>
                    <button type="submit" className="tombol" disabled={mengirim || konser.sisa === 0}>
                        {mengirim ? 'Mengirim…' : 'Pesan sekarang'}
                    </button>
                </form>
            </section>
        </>
    );
}
