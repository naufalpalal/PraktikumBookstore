<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use Illuminate\Http\Request;

class BukuController extends Controller
{
    public function index()
    {
        return Buku::with('kategori')->get();
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'judul' => 'required|string',
                'penulis' => 'required|string',
                'harga' => 'required|numeric|min:1000', // Validasi harga minimal
                'stok' => 'required|integer|min:0',
                'kategori_id' => 'required|exists:kategoris,id',
            ]);

            $buku = Buku::create($request->all());
            return response()->json($buku, 201);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return response()->json(['error' => 'Data tidak valid'], 400);
        }
    }

    public function show($id)
    {
        return Buku::with('kategori')->find($id);
    }

    public function update(Request $request, $id)
    {
        $buku = Buku::findOrFail($id);
        $buku->update($request->all());
        return response()->json($buku, 200);
    }

    public function destroy($id)
    {
        Buku::destroy($id);
        return response()->json(null, 204);
    }

    // Tambahkan metode search di sini
    public function search(Request $request)
{
    $query = $request->input('query');

    // Tambahkan log untuk memeriksa nilai query
    \Log::info("Searching for: $query");

    // Pencarian berdasarkan judul dan kategori
    $bukus = Buku::with('kategori')
        ->where('judul', 'LIKE', "%$query%")
        ->orWhereHas('kategori', function($q) use ($query) {
            $q->where('nama_kategori', 'LIKE', "%$query%");
        })
        ->get();

    // Tambahkan log untuk memeriksa hasil pencarian
    \Log::info("Search results: ", $bukus->toArray());

    return response()->json($bukus, 200);
}
}