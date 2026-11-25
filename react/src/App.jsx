import { Link, Route, Routes } from 'react-router-dom';
import DaftarKonser from './pages/DaftarKonser.jsx';
import DetailKonser from './pages/DetailKonser.jsx';
import CekPesanan from './pages/CekPesanan.jsx';

export default function App() {
    return (
        <div className="app">
            <header className="topbar">
                <Link to="/" className="brand">
                    TiketWar
                </Link>
                <nav className="topbar-nav">
                    <Link to="/">Konser</Link>
                    <Link to="/cek-pesanan">Cek Pesanan</Link>
                </nav>
            </header>

            <main className="container">
                <Routes>
                    <Route path="/" element={<DaftarKonser />} />
                    <Route path="/konser/:id" element={<DetailKonser />} />
                    <Route path="/cek-pesanan" element={<CekPesanan />} />
                    <Route
                        path="*"
                        element={
                            <p className="state state-error">Halaman tidak ditemukan.</p>
                        }
                    />
                </Routes>
            </main>
        </div>
    );
}
