import { useEffect, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import { ambilJson, rupiah } from '../api.js';

const formAwal = { ticket_type_id: '', nama_pemesan: '', email: '', jumlah: 1 };

export default function DetailKonser() {
    const { id } = useParams();
    const [konser, setKonser] = useState(null);
    const [memuat, setMemuat] = useState(true);
    const [galat, setGalat] = useState(null);

    const [form, setForm] = useState(formAwal);
    const [mengirim, setMengirim] = useState(false);
    const [pesanSukses, setPesanSukses] = useState(null);
    const [pesanGalat, setPesanGalat] = useState(null);

    useEffect(() => {
        let batal = false;

        ambilJson(`/konser/${id}`)
            .then((hasil) => {
                if (batal) return;
                setKonser(hasil.data);
                const tersedia = (hasil.data.ticket_types || []).find((tipe) => tipe.sisa > 0);
                if (tersedia) setForm({ ...formAwal, ticket_type_id: tersedia.id });
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
                body: JSON.stringify({
                    ...form,
                    ticket_type_id: Number(form.ticket_type_id),
                    jumlah: Number(form.jumlah),
                }),
            });
            setPesanSukses(hasil.data);
            setKonser((lama) =>
                lama
                    ? {
                          ...lama,
                          sisa: lama.sisa - hasil.data.jumlah,
                          ticket_types: lama.ticket_types.map((tipe) =>
                              tipe.id === hasil.data.ticket_type_id
                                  ? { ...tipe, sisa: tipe.sisa - hasil.data.jumlah }
                                  : tipe,
                          ),
                      }
                    : lama,
            );
            setForm({ ...formAwal, ticket_type_id: form.ticket_type_id });
        } catch (err) {
            setPesanGalat(err.message);
        } finally {
            setMengirim(false);
        }
    }

    if (memuat) return <p className="state">Memuat detail konser…</p>;
    if (galat) return <p className="state state-error">Konser tidak ditemukan atau gagal dimuat: {galat}</p>;

    const daftarTipe = konser.ticket_types || [];
    const tipeTerpilih = daftarTipe.find((tipe) => tipe.id === Number(form.ticket_type_id));
    const bisaPesan = daftarTipe.some((tipe) => tipe.sisa > 0);

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
                <p className="harga">Total sisa: {konser.sisa} tiket</p>
                {daftarTipe.length === 0 && (
                    <p className="state state-error">Konser ini belum memiliki jenis tiket.</p>
                )}
            </article>

            <section className="form-pesanan">
                <h2>Pesan Tiket</h2>

                {pesanSukses && (
                    <p className="state state-sukses">
                        Pesanan berhasil dibuat. Kode pesanan: <strong>{pesanSukses.kode_pesanan}</strong>.
                        Simpan kode ini untuk mengecek status. Jenis: {pesanSukses.jenis_tiket || '-'}.
                        Status: {pesanSukses.status}, total {rupiah(pesanSukses.total)}.
                    </p>
                )}
                {pesanGalat && <p className="state state-error">{pesanGalat}</p>}

                {daftarTipe.length > 0 && (
                    <form onSubmit={kirim}>
                        <label>
                            Jenis tiket
                            <select
                                value={form.ticket_type_id}
                                onChange={(e) => ubah({ ticket_type_id: e.target.value })}
                                required
                            >
                                {daftarTipe.map((tipe) => (
                                    <option key={tipe.id} value={tipe.id} disabled={tipe.sisa < 1}>
                                        {tipe.nama} — {rupiah(tipe.harga)} (sisa {tipe.sisa})
                                    </option>
                                ))}
                            </select>
                        </label>
                        {tipeTerpilih && (
                            <p className="meta">Harga satuan: {rupiah(tipeTerpilih.harga)}</p>
                        )}
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
                                max={tipeTerpilih?.sisa ?? 1}
                                value={form.jumlah}
                                onChange={(e) => ubah({ jumlah: e.target.value })}
                                required
                            />
                        </label>
                        <button
                            type="submit"
                            className="tombol"
                            disabled={mengirim || !bisaPesan || (tipeTerpilih?.sisa ?? 0) < 1}
                        >
                            {mengirim ? 'Mengirim…' : 'Pesan sekarang'}
                        </button>
                    </form>
                )}
            </section>
        </>
    );
}
