<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redis;

class EmployeeController extends Controller
{
    public function index() {
        return Employee::all();
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'nomor' => 'required|unique:employees',
            'nama' => 'required',
            'jabatan' => 'nullable',
            'talahir' => 'nullable|date',
            'photo' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('photos', 's3');
            $validated['photo_upload_path'] = Storage::disk('s3')->url($path);
        }

        $validated['created_on'] = now();

        $employee = Employee::create($validated);
        Redis::set("emp_{$employee->nomor}", $employee->toJson());

        return response()->json($employee);
    }

    public function show($id) {
        return Employee::findOrFail($id);
    }

    public function update(Request $request, $id) {
        $employee = Employee::findOrFail($id);

        $validated = $request->validate([
            'nama' => 'required',
            'jabatan' => 'nullable',
            'talahir' => 'nullable|date',
            'photo' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('photos', 's3');
            $validated['photo_upload_path'] = Storage::disk('s3')->url($path);
        }

        $validated['updated_on'] = now();
        $employee->update($validated);

        Redis::set("emp_{$employee->nomor}", $employee->fresh()->toJson());

        return response()->json($employee);
    }

    public function destroy($id) {
        $employee = Employee::findOrFail($id);
        Redis::del("emp_{$employee->nomor}");
        $employee->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
