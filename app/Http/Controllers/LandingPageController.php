<?php
namespace App\Http\Controllers;

use App\Models\Paket;

class LandingPageController extends Controller
{
    /**
     * Menampilkan halaman landing page utama.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Ambil semua data paket, diurutkan berdasarkan harga (misalnya)
        $pakets = Paket::orderBy('harga_paket', 'asc')->get();

        return view('landing.index', compact('pakets'));
    }

}
