<?php
namespace App\Http\Controllers;

use App\Models\Mosque;
use Illuminate\Http\Request;

class MosqueController extends Controller
{
    public function index(Request $request)
    {
        $mosques = Mosque::all(); // Fetch all mosques
        return response()->json($mosques);
    }

    public function getRegions()
    {
        // Implement logic to get regions
        return response()->json([]); // Placeholder
    }

    public function search(Request $request)
    {
        $searchTerm = $request->input('term');
        $mosques = Mosque::where('name', 'LIKE', "%{$searchTerm}%")->get();
        return response()->json($mosques);
    }

    public function store(Request $request)
    {
        $mosque = Mosque::create($request->all());
        return response()->json($mosque, 201);
    }

    public function update(Request $request, $id)
    {
        $mosque = Mosque::findOrFail($id);
        $mosque->update($request->all());
        return response()->json($mosque);
    }

    public function destroy($id)
    {
        $mosque = Mosque::findOrFail($id);
        $mosque->delete();
        return response()->json(null, 204);
    }

    public function destroyMultiple(Request $request)
    {
        $ids = $request->input('ids');
        Mosque::destroy($ids);
        return response()->json(null, 204);
    }
}