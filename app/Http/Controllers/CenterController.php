<?php

namespace App\Http\Controllers;

use App\Models\Center;
use App\Models\Mosque;
use Illuminate\Http\Request;

class CenterController extends Controller
{
    public function index()
    {
        $centers = Center::with('mosque')->latest()->paginate(15);
        return view('centers.index', compact('centers'));
    }

    public function create()
    {
        $mosques = Mosque::pluck('name', 'id');
        return view('centers.create', compact('mosques'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'mosque_id' => 'required|exists:mosques,id',
            'notes' => 'nullable|string',
        ]);

        Center::create($request->all());

        return redirect()->route('centers.index')
            ->with('success', 'تم إنشاء المركز');
    }

    public function show(Center $center)
    {
        $center->load('mosque');
        return view('centers.show', compact('center'));
    }

    public function edit(Center $center)
    {
        $mosques = Mosque::pluck('name', 'id');
        return view('centers.edit', compact('center', 'mosques'));
    }

    public function update(Request $request, Center $center)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'mosque_id' => 'required|exists:mosques,id',
            'notes' => 'nullable|string',
        ]);

        $center->update($request->all());

        return redirect()->route('centers.index')
            ->with('success', 'تم تحديث المركز');
    }

    public function destroy(Center $center)
    {
        $center->delete();

        return redirect()->route('centers.index')
            ->with('success', 'تم حذف المركز');
    }
}
