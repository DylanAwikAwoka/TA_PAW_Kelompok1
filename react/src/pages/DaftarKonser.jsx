import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { ambilJson, rupiah } from '../api.js';

export default function DaftarKonser() {
    const [konser, setKonser] = useState([]);
    const [memuat, setMemuat] = useState(true);
    const [galat, setGalat] = useState(null);

    useEffect(() => {
        let batal = false;

        ambilJson('/konser')
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
    }, []);

    if (memuat) return <p className="state">Memuat daftar konser…</p>;
    if (galat) return <p className="state state-error">Gagal memuat konser: {galat}</p>;
    if (konser.length === 0) {
        return <p className="state">Belum ada konser yang tersedia.</p>;
    }

    return (
        <>
            <h1>Daftar Konser</h1>
            <div className="grid">
                {konser.map((item) => (
                    <article key={item.id} className="card">
                        <h2>{item.nama}</h2>
                        <p className="meta">
                            {item.kota} · {item.tanggal}
                        </p>
                        <p className="harga">{rupiah(item.harga)}</p>
                        <p className={item.sisa > 0 ? 'stok' : 'stok stok-habis'}>
                            {item.sisa > 0 ? `Sisa ${item.sisa} tiket` : 'Tiket habis'}
                        </p>
                        <Link className="tombol" to={`/konser/${item.id}`}>
                            Lihat detail
                        </Link>
                    </article>
                ))}
            </div>
        </>
    );
}
