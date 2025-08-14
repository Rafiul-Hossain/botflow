<?php

// app/Http/Controllers/Admin/ServiceController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::orderBy('category')->get();
        return view('admin.services.index', compact('services'));
    }

    public function edit(Service $service)
    {
        return view('admin.services.edit', compact('service'));
    }

    public function update(Request $request, Service $service)
    {
        $request->validate([
            'custom_rate' => 'required|numeric|min:0'
        ]);

        $service->update([
            'custom_rate' => $request->custom_rate
        ]);

        return redirect()->route('admin.services.index')->with('success', 'Service price updated.');
    }
}
