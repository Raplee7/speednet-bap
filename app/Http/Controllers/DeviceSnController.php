<?php
namespace App\Http\Controllers;

use App\Models\Device_model;
use App\Models\Device_sn;
use Illuminate\Http\Request;

class DeviceSnController extends Controller
{
    public function index()
    {
        $device_sns = Device_sn::with('deviceModel')->get();
        return view('device_sns.index', [
            'device_sns' => $device_sns,
            'pageTitle'  => 'Serial Number Perangkat',
        ]);
    }

    public function create()
    {
        $device_models = Device_model::all();
        return view('device_sns.create', [
            'device_models' => $device_models,
            'pageTitle'     => 'Tambah Serial Number Perangkat',
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nomor'    => 'required|unique:device_sns,nomor',
            'model_id' => 'required|exists:device_models,id_dm',
            'status'   => 'required|in:tersedia,dipakai,rusak',
        ]);

        Device_sn::create($request->all());

        return redirect()->route('device_sns.index')->with('success', 'Serial Number berhasil ditambahkan!');
    }

    public function edit(Device_sn $device_sn)
    {
        $device_models = Device_model::all();
        return view('device_sns.edit', [
            'device_models' => $device_models,
            'device_sn'     => $device_sn,
            'pageTitle'     => 'Edit Serial Number Perangkat',
        ]);

    }

    public function update(Request $request, Device_sn $device_sn)
    {
        $request->validate([
            'nomor'    => 'required|unique:device_sns,nomor,' . $device_sn->id_dsn . ',id_dsn',
            'model_id' => 'required|exists:device_models,id_dm',
            'status'   => 'required|in:tersedia,dipakai,rusak',
        ]);

        $device_sn->update($request->all());

        return redirect()->route('device_sns.index')->with('success', 'Serial Number berhasil diperbarui!');
    }

    public function destroy(Device_sn $device_sn)
    {
        $device_sn->delete();
        return redirect()->route('device_sns.index')->with('success', 'Serial Number berhasil dihapus!');
    }
}
