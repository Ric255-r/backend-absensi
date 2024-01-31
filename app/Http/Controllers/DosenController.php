<?php

namespace App\Http\Controllers;
use JWTAuth;
use DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ModelMatkul;
use App\Models\ModelAbsen;
use Carbon;

class DosenController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $query = DB::table('tbmatkul AS m')
                    ->leftjoin('tbjadwal AS j', 'm.kode_matkul', '=', 'j.kode_matkul')
                    ->join('users AS u', 'm.kode_dosen', '=', 'u.kode_dosen')
                    ->select('m.kode_matkul', 
                        'm.mata_kuliah_dosen', 'm.kode_dosen', 'u.nama', 'u.email', 'j.hari', 'j.jam')
                    ->where('m.kode_dosen', '=', $user->kode_dosen)
                    ->get();

        return $query;
    }

    public function accabsen(Request $request)
    {
        $user = Auth::user()->kode_dosen;
        $cekupdate = ModelAbsen::where('id', $request->id)
                            ->update([
                                'accdosen' => 1,
                                'istolak' => 0
                            ]);
        if($cekupdate){
            $matkul = ModelMatkul::where('kode_dosen', $user)->get();
            $absen = DB::table('tbabsensi AS a')
                            ->join('tbmatkul AS m', 'a.kode_matkul', '=', 'm.kode_matkul')
                            ->select('a.id','a.npm_mahasiswa', 'a.kode_matkul','m.mata_kuliah_dosen', 'm.kode_dosen', 'm.nama_dosen', 'a.hari_absen', 'a.tanggal_absen', 'a.jam_absen_masuk', 'a.jam_absen_selesai', 'a.accdosen', 'a.istolak', 'a.foto_mhs', 'a.foto_mhs_selesai')
                            ->where('m.kode_dosen', '=', $user)
                            ->get();
    
            return response()->json([
                'matkul' => $matkul,
                'absen' => $absen
            ]);
        }
    }

    public function accsemua(Request $request)
    {
        $user = Auth::user()->kode_dosen;
        $mytime = Carbon\Carbon::now();

        // $matkul = $request->kode_matkul;
        // Logika Sementara, dikarenakan 1 Dosen Bisa ambil banyak matkul
        // Tetapi matkul tsb kode matkulnya ga boleh sama, kalau sama bentrok ke dosen lain.
        $banding = ModelMatkul::where('kode_matkul', $request->kode_matkul) //K001
                                ->where('kode_dosen', $user)    //AA001 BB001
                                ->first();

        $cekupdate = ModelAbsen::where('kode_matkul', $banding->kode_matkul)
                                ->where('istolak', '=', 0)
                                ->where('tanggal_absen', '=', $mytime->toDateString())
                                ->update([
                                    'accdosen' => 1,
                                ]);

        if($cekupdate){
            $matkul = ModelMatkul::where('kode_dosen', $user)->get();
            $absen = DB::table('tbabsensi AS a')
                            ->join('tbmatkul AS m', 'a.kode_matkul', '=', 'm.kode_matkul')
                            ->select('a.id','a.npm_mahasiswa', 'a.kode_matkul','m.mata_kuliah_dosen', 'm.kode_dosen', 'm.nama_dosen', 'a.hari_absen', 'a.tanggal_absen', 'a.jam_absen_masuk', 'a.jam_absen_selesai', 'a.accdosen', 'a.istolak', 'a.foto_mhs', 'a.foto_mhs_selesai')
                            ->where('m.kode_dosen', '=', $user)
                            ->get();
    
            return response()->json([
                'matkul' => $matkul,
                'absen' => $absen
            ]);
        }else{
            return response()->json([
                'error' => true
            ], 500);
        }
    }

    public function tolakabsen(Request $request)
    {
        $user = Auth::user()->kode_dosen;
        $cekupdate = ModelAbsen::where('id', $request->id)
                            ->update([
                                'accdosen' => 0,
                                'istolak' => 1
                            ]);
        if($cekupdate){
            $matkul = ModelMatkul::where('kode_dosen', $user)->get();
            $absen = DB::table('tbabsensi AS a')
                            ->join('tbmatkul AS m', 'a.kode_matkul', '=', 'm.kode_matkul')
                            ->select('a.id','a.npm_mahasiswa', 'a.kode_matkul','m.mata_kuliah_dosen', 'm.kode_dosen', 'm.nama_dosen', 'a.hari_absen', 'a.tanggal_absen', 'a.jam_absen_masuk', 'a.jam_absen_selesai', 'a.accdosen','a.istolak', 'a.foto_mhs', 'a.foto_mhs_selesai')
                            ->where('m.kode_dosen', '=', $user)
                            ->get();
    
            return response()->json([
                'matkul' => $matkul,
                'absen' => $absen
            ]);
        }
    }

    public function listabsen()
    {
        $user = Auth::user()->kode_dosen;
        $matkul = ModelMatkul::where('kode_dosen', $user)->get();
        $absen = DB::table('tbabsensi AS a')
                        ->join('tbmatkul AS m', 'a.kode_matkul', '=', 'm.kode_matkul')
                        ->select('a.id','a.npm_mahasiswa', 'a.kode_matkul','m.mata_kuliah_dosen', 'm.kode_dosen', 'm.nama_dosen', 'a.hari_absen', 'a.tanggal_absen', 'a.jam_absen_masuk', 'a.jam_absen_selesai', 'a.accdosen','a.istolak','a.foto_mhs','a.foto_mhs_selesai')
                        ->where('m.kode_dosen', '=', $user)
                        ->get();

        return response()->json([
            'matkul' => $matkul,
            'absen' => $absen
        ]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
