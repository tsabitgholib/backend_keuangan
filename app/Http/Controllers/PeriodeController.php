<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Periode;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class PeriodeController extends Controller
{
    /**
     * Menampilkan daftar periode.
     */
    public function index()
    {
        $data = Periode::all();
        return response()->json([
            'message' => 'Daftar periode berhasil diambil',
            'data' => $data
        ]);
    }

    /**
     * Menyimpan periode baru.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date',
            'status' => 'required|in:Aktif,Tutup'
        ]);
        $periode = Periode::create($data);
        return response()->json([
            'message' => 'Periode berhasil ditambahkan',
            'data' => $periode
        ], 201);
    }

    /**
     * Menampilkan detail periode tertentu.
     */
    public function show($id)
    {
        $periode = Periode::findOrFail($id);
        return response()->json($periode);
    }

    /**
     * Update data periode tertentu.
     */
    public function update(Request $request, $id)
    {
        $periode = Periode::findOrFail($id);
        $data = $request->validate([
            'nama' => 'sometimes|required',
            'tanggal_mulai' => 'sometimes|required|date',
            'tanggal_selesai' => 'sometimes|required|date',
            'status' => 'sometimes|required|in:Aktif,Tutup'
        ]);
        $periode->update($data);
        return response()->json($periode);
    }

    /**
     * Hapus periode tertentu.
     */
    public function destroy($id)
    {
        $periode = Periode::findOrFail($id);
        $periode->delete();
        return response()->json(['message' => 'Periode berhasil dihapus']);
    }

    /**
     * Menutup periode tertentu.
     */
    public function tutup(Request $request, $id)
    {
        $periode = Periode::findOrFail($id);
        $periode->update(['status' => 'Tutup']);
        return response()->json(['message' => 'Periode berhasil ditutup']);
    }
}
