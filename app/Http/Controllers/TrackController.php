<?php

namespace App\Http\Controllers;

use App\Models\Track;
use Illuminate\Http\Request;
class TrackController extends Controller
{
    public function index()
    {
        $tracks = Track::latest()->get();
        return view('tracks.index', compact('tracks'));
    }

    public function create()
    {
        return view('tracks.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required'
        ]);

        Track::create($request->all());

        return redirect()->route('tracks.index')
            ->with('success','تم إنشاء المسار');
    }

    public function show(Track $track)
    {
        return view('tracks.show', compact('track'));
    }

    public function edit(Track $track)
    {
        return view('tracks.edit', compact('track'));
    }

    public function update(Request $request, Track $track)
    {
        $request->validate([
            'name' => 'required'
        ]);

        $track->update($request->all());

        return redirect()->route('tracks.index')
            ->with('success','تم التعديل');
    }

    public function destroy(Track $track)
    {
        $track->delete();
        return back()->with('success','تم الحذف');
    }
}
