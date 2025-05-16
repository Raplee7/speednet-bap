<?php
namespace App\Http\Controllers;

use App\Models\Ewallet;
use Illuminate\Http\Request;

class EwalletController extends Controller
{
    public function index()
    {
        $ewallets = Ewallet::all();
        return view('ewallets.index', [
            'ewallets'  => $ewallets,
            'pageTitle' => 'E-Wallet',
        ]);
    }

    public function create()
    {
        return view('ewallets.create', [
            'pageTitle' => 'Tambah E-Wallet',
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_ewallet' => 'required|string|max:255',
            'no_ewallet'   => 'required|string|max:255',
            'atas_nama'    => 'required|string|max:255',
        ]);

        Ewallet::create($request->all());

        return redirect()->route('ewallets.index')->with('success', 'E-Wallet berhasil ditambahkan.');
    }

    public function edit(Ewallet $ewallet)
    {
        return view('ewallets.edit', [
            'ewallet'   => $ewallet,
            'pageTitle' => 'Edit E-Wallet',
        ]);
    }

    public function update(Request $request, Ewallet $ewallet)
    {
        $request->validate([
            'nama_ewallet' => 'required|string|max:255',
            'no_ewallet'   => 'required|string|max:255',
            'atas_nama'    => 'required|string|max:255',
        ]);

        $ewallet->update($request->all());

        return redirect()->route('ewallets.index')->with('success', 'E-Wallet berhasil diupdate.');
    }

    public function destroy(Ewallet $ewallet)
    {
        $ewallet->delete();
        return redirect()->route('ewallets.index')->with('success', 'E-Wallet berhasil dihapus.');
    }

    public function toggleStatus($id)
    {
        $ewallet            = Ewallet::findOrFail($id);
        $ewallet->is_active = ! $ewallet->is_active;
        $ewallet->save();

        return redirect()->back()->with('success', 'Status e-wallet berhasil diperbarui.');
    }

}
