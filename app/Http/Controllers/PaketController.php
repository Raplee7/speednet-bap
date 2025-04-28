<?php
namespace App\Http\Controllers;

use App\Models\Paket;
use Illuminate\Http\Request;

class PaketController extends Controller
{
    public function index()
    {
        $pakets = Paket::all();
        return view('pakets.index', [
            'pakets'    => $pakets,
            'pageTitle' => 'Paket',
        ]);
    }

    public function create()
    {
        return view('pakets.create', [
            'pageTitle' => 'Tambah Paket',
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'kecepatan_paket' => 'required|string|max:255',
            'harga_paket'     => 'required|integer',
        ]);

        Paket::create($request->all());

        return redirect()->route('pakets.index')->with('success', 'Paket berhasil ditambahkan.');
    }

    public function edit(Paket $paket)
    {
        return view('pakets.edit', [
            'paket'     => $paket,
            'pageTitle' => 'Edit Paket',
        ]);
    }

    public function update(Request $request, Paket $paket)
    {
        $request->validate([
            'kecepatan_paket' => 'required|string|max:255',
            'harga_paket'     => 'required|integer',
        ]);

        $paket->update($request->all());

        return redirect()->route('pakets.index')->with('success', 'Paket berhasil diupdate.');
    }

    public function destroy(Paket $paket)
    {
        $paket->delete();
        return redirect()->route('pakets.index')->with('success', 'Paket berhasil dihapus.');
    }
}
