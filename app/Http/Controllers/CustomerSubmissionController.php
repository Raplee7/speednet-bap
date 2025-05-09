<?php
namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CustomerSubmissionController extends Controller
{
    public function create()
    {
        $pakets = \App\Models\Paket::all();
        return view('landing.index', compact('pakets'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_customer'   => 'required|string|max:255',
            'alamat_customer' => 'required|string',
            'wa_customer'     => 'required|string|max:20',
            'paket_id'        => 'required|exists:pakets,id_paket',
        ]);

        if ($validator->fails()) {
            return redirect('/#form')
                ->withErrors($validator)
                ->withInput();
        }

        $newId = 'SN' . str_pad(Customer::count() + 1, 4, '0', STR_PAD_LEFT) . date('ym');

        Customer::create([
            'id_customer'     => $newId,
            'nama_customer'   => $request->nama_customer,
            'alamat_customer' => $request->alamat_customer,
            'wa_customer'     => $request->wa_customer,
            'paket_id'        => $request->paket_id,
            'status'          => 'baru',
            'password'        => bcrypt(Str::random(8)),
        ]);

        return redirect('/#form')->with('success', 'Pengajuan berhasil dikirim. Kami akan segera menghubungi Anda!');
    }
}
