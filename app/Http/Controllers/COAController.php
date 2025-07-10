<?php

namespace App\Http\Controllers;

use App\Models\Akun;
use App\Models\JurnalDetail;
use Illuminate\Http\Request;

class COAController extends Controller
{
    /**
     * Menampilkan daftar akun (COA) dengan filter level/tipe jika ada.
     */
    public function index(Request $request)
    {
        $level = $request->level;
        $type = $request->type;

        $query = Akun::query();
        if ($level) $query->where('level', $level);
        if ($type) $query->where('account_type', $type);

        $akuns = $query->with(['children.children'])->get();

        return response()->json($akuns);
    }

    /**
     * Menampilkan daftar akun COA dengan level 2 dan 3 saja.
     */
    public function getLevel2And3(Request $request)
    {
        $type = $request->type;

        $query = Akun::whereIn('level', [2, 3]);
        if ($type) $query->where('account_type', $type);

        $akuns = $query->with(['parent', 'children'])->get();

        return response()->json($akuns);
    }

    /**
     * Menampilkan struktur tree COA (3 level).
     */
    public function tree()
    {
        $akuns = Akun::where('level', 1)->with(['children.children'])->get();
        return response()->json($akuns);
    }

    /**
     * Menambah akun baru ke COA.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'account_code' => 'required|unique:akuns,account_code',
            'account_name' => 'required',
            'level' => 'required|integer|min:1|max:3',
            'parent_id' => 'nullable|exists:akuns,id',
            'account_type' => 'required|in:Asset,Kewajiban,Ekuitas,Pendapatan,Beban',
            'is_active' => 'boolean'
        ]);
        $akun = Akun::create($data);
        return response()->json($akun, 201);
    }

    /**
     * Menampilkan detail akun COA tertentu.
     */
    public function show($id)
    {
        $akun = Akun::with(['children.children', 'parent'])->findOrFail($id);
        return response()->json($akun);
    }

    /**
     * Mengupdate data akun COA tertentu.
     */
    public function update(Request $request, $id)
    {
        $akun = Akun::findOrFail($id);
        $data = $request->validate([
            'account_code' => 'sometimes|required|unique:akuns,account_code,' . $akun->id,
            'account_name' => 'sometimes|required',
            'level' => 'sometimes|required|integer|min:1|max:3',
            'parent_id' => 'nullable|exists:akuns,id',
            'account_type' => 'sometimes|required|in:Asset,Kewajiban,Ekuitas,Pendapatan,Beban',
            'is_active' => 'boolean'
        ]);
        $akun->update($data);
        return response()->json($akun);
    }

    /**
     * Menghapus akun COA tertentu.
     */
    public function destroy($id)
    {
        $akun = Akun::findOrFail($id);

        // Cek apakah akun sudah digunakan di jurnal
        $usedInJurnal = JurnalDetail::where('akun_id', $id)->exists();

        if ($usedInJurnal) {
            return response()->json([
                'message' => 'Akun tidak dapat dihapus karena sudah digunakan dalam jurnal'
            ], 422);
        }

        $akun->delete();
        return response()->json(['message' => 'Akun berhasil dihapus']);
    }
}
