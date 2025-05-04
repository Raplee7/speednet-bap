<?php
namespace App\Http\Controllers;

use App\Models\Device_model;
use Illuminate\Http\Request;

class DeviceModelController extends Controller
{
    public function index()
    {
        $device_models = Device_model::all();
        return view('device_models.index', [
            'device_models' => $device_models,
            'pageTitle'     => 'Model Perangkat',
        ]);
    }

    public function create()
    {
        return view('device_models.create', [
            'pageTitle' => 'Tambah Model Perangkat',
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_model' => 'required|string|max:255',
        ]);

        Device_model::create($request->all());

        return redirect()->route('device_models.index')->with('success', 'Model berhasil ditambahkan!');
    }

    public function edit(Device_model $device_model)
    {
        return view('device_models.edit', [
            'device_model' => $device_model,
            'pageTitle'    => 'Edit Model Perangkat',
        ]);
    }

    public function update(Request $request, Device_model $device_model)
    {
        $request->validate([
            'nama_model' => 'required|string|max:255',
        ]);

        $device_model->update($request->all());

        return redirect()->route('device_models.index')->with('success', 'Model berhasil diperbarui!');
    }

    public function destroy(Device_model $device_model)
    {
        $device_model->delete();
        return redirect()->route('device_models.index')->with('success', 'Model berhasil dihapus!');
    }
}
