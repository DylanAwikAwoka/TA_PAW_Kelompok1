import { Link, Route, Routes } from 'react-router-dom';
import DaftarKonser from './pages/DaftarKonser.jsx';
import DetailKonser from './pages/DetailKonser.jsx';

export default function App() {
    return (
        <div className="app">
            <header className="topbar">
                <Link to="/" className="brand">
                    TiketWar
                </Link>
                <span className="topbar-note">Pemesanan tiket konser</span>
            </header>

            <main className="container">
                <Routes>
                    <Route path="/" element={<DaftarKonser />} />
                    <Route path="/konser/:id" element={<DetailKonser />} />
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
