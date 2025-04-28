<?php
namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function index()
    {
        $devices = Device::all();
        return view('devices.index', [
            'devices'   => $devices,
            'pageTitle' => 'Perangkat',
        ]);
    }

    public function create()
    {
        return view('devices.create', [
            'pageTitle' => 'Tambah Device',
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_perangkat' => 'required|string|max:255',
        ]);

        Device::create($request->all());

        return redirect()->route('devices.index')->with('success', 'Device berhasil ditambahkan.');
    }

    public function edit(Device $device)
    {
        return view('devices.edit', [
            'device'    => $device,
            'pageTitle' => 'Edit Device',
        ]);
    }

    public function update(Request $request, Device $device)
    {
        $request->validate([
            'nama_perangkat' => 'required|string|max:255',
        ]);

        $device->update($request->all());

        return redirect()->route('devices.index')->with('success', 'Device berhasil diupdate.');
    }

    public function destroy(Device $device)
    {
        $device->delete();
        return redirect()->route('devices.index')->with('success', 'Device berhasil dihapus.');
    }
}
