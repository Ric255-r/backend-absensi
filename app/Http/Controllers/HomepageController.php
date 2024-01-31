<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ModelAbsen;
use DB;
use Auth;
use Str;
use Carbon;
use App\Models\ModelMatkul;
use App\Models\ModelJoinMatkul;
use Illuminate\Database\Query\Builder;
use App\Models\ModelJadwal;


class HomepageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($id)
    {
        $get = ModelHomepage::find($id);
        return $get;
    }

    public function dashboard($kode_matkul = null)
    {
        $mytime = Carbon\Carbon::now();
        $mydate = Carbon\Carbon::now()->locale('id')->dayName;

        $user = Auth::user()->npm;

        $get = DB::table('tbjoin_matkul AS j')
                ->join('tbmatkul AS m', 'j.kode_matkul', '=', 'm.kode_matkul')
                ->leftjoin('tbjadwal AS jj', 'j.kode_matkul', '=', 'jj.kode_matkul') //leftjoin sementara. hrs pk join ori.
                ->join('users AS u', 'j.npm', '=', 'u.npm')
                ->select('j.id','m.kode_matkul','m.mata_kuliah_dosen', 'm.nama_dosen', 'u.nama', 'u.npm', 'jj.hari', 'jj.jam', 'jj.jam_selesai', 'u.isDosen')
                ->where('u.npm', '=', $user)
                ->get();

        $matkulnow = DB::table('tbjadwal AS j')
                        ->join('tbjoin_matkul AS jm', 'j.kode_matkul', '=', 'jm.kode_matkul')
                        ->join('tbmatkul AS m', 'j.kode_matkul', '=', 'm.kode_matkul')
                        ->join('users AS u', 'jm.npm', '=', 'u.npm')
                        ->select('j.id', 'j.hari', 'j.jam', 'j.jam_selesai', 'j.kode_matkul', 'jm.npm', 'm.mata_kuliah_dosen', 'u.nama')
                        ->where('j.hari', '=', $mydate)
                        ->where('jm.npm', '=', $user)
                        ->get();
        
        $buatreturn = DB::table('tbabsensi AS a')
                            ->join('tbmatkul AS m', 'a.kode_matkul', '=', 'm.kode_matkul')
                            ->select('a.npm_mahasiswa', 'a.kode_matkul','m.mata_kuliah_dosen', 'a.hari_absen', 'a.tanggal_absen', 'a.jam_absen_masuk', 'a.jam_absen_selesai', 'a.accdosen','a.istolak', 'a.foto_mhs', 'a.foto_mhs_selesai')
                            ->where('a.npm_mahasiswa', '=', $user)
                            ->get();

        return response()->json([
            'joinmatkul' => $get,
            'rekapabsen' => $buatreturn,
            'matkulnow' => $matkulnow
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function absen(Request $request)
    {
        $user = Auth::user()->npm;

        $request->validate([
            'npm_mahasiswa' => 'nullable|string|max:255',
            'kode_matkul' => 'required|string|max:255',
            'hari_absen' => 'required',
            'tanggal_absen' => 'nullable',
            'jam_absen_masuk' => 'nullable',
            'jam_absen_selesai' => 'nullable',
            'accdosen' => 'nullable',
            'istolak' => 'nullable',
            'foto_mhs' => 'nullable',
            'foto_mhs_selesai' => 'nullable'
        ]);

        $mytime = Carbon\Carbon::now();
        $mydate = Carbon\Carbon::now()->locale('id')->dayName;


        // $file = mb_convert_encoding($request['foto_mhs'], 'UTF-8', 'UTF-8');;
        // $safeName = $user.'_'.Str::random(5).'.'.'jpeg';
        // $success = file_put_contents(public_path().'/uploads/'.$safeName, $file);

        $cekduplikat = ModelAbsen::where('npm_mahasiswa', '=', $user)
                        ->where('kode_matkul', '=', $request->kode_matkul)
                        ->where('tanggal_absen', '=', $mytime->toDateString())
                        ->where('jam_absen_masuk', '!=', null)
                        ->where('jam_absen_selesai', '!=', null)
                        ->exists();

        if($cekduplikat){
            return response()->json(['message' => 'Data Sudah Ada'], 422);
        }else{
            $cekabsen = ModelAbsen::where('npm_mahasiswa', '=', $user)
                    ->where('kode_matkul', '=', $request->kode_matkul)
                    ->where('tanggal_absen', '=', $mytime->toDateString())
                    ->where('jam_absen_masuk', '!=', null)
                    ->first();

            $jadwal = ModelJadwal::where('kode_matkul', $request->kode_matkul)
                                ->where('hari', $mydate)
                                ->first();

            if($cekabsen){
                // Update
                // Cek Apakah Jam selesai sesuai dengan jam Sekarang
                if($mytime->toTimeString() >= $jadwal->jam_selesai){

                    $file = mb_convert_encoding($request['foto_mhs'], 'UTF-8', 'UTF-8');;
                    $safeName = $user.'_'.Str::random(5).'.'.'jpeg';
                    $success = file_put_contents(public_path().'/uploads/'.$safeName, $file);

                    ModelAbsen::where('id', $cekabsen->id)
                        ->update([
                            'npm_mahasiswa'=>$user,
                            'kode_matkul' => $request->kode_matkul,
                            'hari_absen'=>$request->hari_absen,
                            'tanggal_absen' => $mytime->toDateString(),
                            // 'jam_absen_masuk' => $mytime->toTimeString(),
                            'jam_absen_selesai' => $mytime->toTimeString(),
                            // 'foto_mhs'=>$file,
                            'foto_mhs_selesai'=>$file,
                            'accdosen' => 0,
                            'istolak' => 0
                        ]);
                }else{
                    return response()->json([
                        'Gagal' => 'Absen Hanya Boleh Pas Mendekati / Melewati Jam Tsb'
                    ], 404);
                }
            }else{
                // Insert
                if($mytime->toTimeString() >= $jadwal->jam){

                    $file = mb_convert_encoding($request['foto_mhs'], 'UTF-8', 'UTF-8');;
                    $safeName = $user.'_'.Str::random(5).'.'.'jpeg';
                    $success = file_put_contents(public_path().'/uploads/'.$safeName, $file);
                    
                    ModelAbsen::create([
                        'npm_mahasiswa'=>$user,
                        'kode_matkul' => $request->kode_matkul,
                        'hari_absen'=>$request->hari_absen,
                        'tanggal_absen' => $mytime->toDateString(),
                        'jam_absen_masuk' => $mytime->toTimeString(),
                        'jam_absen_selesai' => null,
                        'foto_mhs'=>$file,
                        'foto_mhs_selesai'=>null,
                        'accdosen' => 0,
                        'istolak' => 0
                    ]);
                }else{
                    return response()->json([
                        'Gagal' => 'Absen Hanya Boleh Pas Mendekati / Melewati Jam Tsb'
                    ], 404);
                }
            }
        }

        // $buatreturn = ModelAbsen::where('npm_mahasiswa', '=', $user)
        //                 ->select('npm_mahasiswa', 'kode_matkul', 'hari_absen', 'tanggal_absen', 'jam_absen_masuk', 'jam_absen_selesai', 'accdosen', 'foto_mhs')
        //                 ->where('kode_matkul', '=', $request->kode_matkul)
        //                 ->where('tanggal_absen', '=', $mytime->toDateString())
        //                 ->where(function($q){
        //                     $q->where('jam_absen_masuk', '!=', null)
        //                         ->orWhere('jam_absen_selesai', '!=', null);
        //                 })
        //                 ->get();

        // Ambil dari FUnction dashboard, untuk Usestate di Frontend setelah Axios post
        $get = DB::table('tbjoin_matkul AS j')
                    ->join('tbmatkul AS m', 'j.kode_matkul', '=', 'm.kode_matkul')
                    //sementara. hrs pk join ori.
                    ->leftjoin('tbjadwal AS jj', 'j.kode_matkul', '=', 'jj.kode_matkul') 
                    ->join('users AS u', 'j.npm', '=', 'u.npm')
                    ->select('j.id','m.kode_matkul','m.mata_kuliah_dosen', 'm.nama_dosen', 'u.nama', 'u.npm', 'jj.hari', 'jj.jam', 'u.isDosen')
                    ->where('u.npm', '=', $user)
                    ->get();

        $buatreturn = DB::table('tbabsensi AS a')
                        ->join('tbmatkul AS m', 'a.kode_matkul', '=', 'm.kode_matkul')
                        ->select('a.npm_mahasiswa', 'a.kode_matkul','m.mata_kuliah_dosen', 'a.hari_absen', 'a.tanggal_absen', 'a.jam_absen_masuk', 'a.jam_absen_selesai', 'a.accdosen','a.istolak', 'a.foto_mhs', 'a.foto_mhs_selesai')
                        ->where('a.npm_mahasiswa', '=', $user)
                        ->get();

        $matkulnow = DB::table('tbjadwal AS j')
                ->join('tbjoin_matkul AS jm', 'j.kode_matkul', '=', 'jm.kode_matkul')
                ->join('tbmatkul AS m', 'j.kode_matkul', '=', 'm.kode_matkul')
                ->join('users AS u', 'jm.npm', '=', 'u.npm')
                ->select('j.id', 'j.hari', 'j.jam', 'j.jam_selesai', 'j.kode_matkul', 'jm.npm', 'm.mata_kuliah_dosen', 'u.nama')
                ->where('j.hari', '=', $mydate)
                ->where('jm.npm', '=', $user)
                ->get();

        return response()->json([
            'joinmatkul' => $get,
            'rekapabsen' => $buatreturn,
            'matkulnow' => $matkulnow
        ]);    
    }
    
    public function create()
    {
        //
    }

    public function getAllMatkul()
    {
        // $get = ModelMatkul::all();
        $get = DB::table('tbmatkul AS m')
                    ->leftjoin('tbjadwal AS j', 'm.kode_matkul', '=', 'j.kode_matkul')
                    ->select('m.*', 'j.hari', 'j.jam', 'j.jam_selesai')
                    ->get();

        $user = Auth::user();
        $defaultsks = 20;

        return response()->json([
            'matkulall'=>$get,
            'joinmatkul'=> $user,
            'sks'=>$defaultsks
        ]);
    }

    public function regisMatkul(Request $request)
    {
        $user = Auth::user()->npm;

        $request->validate([
            'kode_matkul' => 'required|string',
        ]);

        $cek = ModelJoinMatkul::where('kode_matkul', $request->kode_matkul)
                            ->where('npm', $user)
                            ->exists();

        $defaultsks = 20;

        if(!$cek){
            ModelJoinMatkul::create([
                'kode_matkul' => $request->kode_matkul,
                'npm' => $user
            ]);

            // Masih Bug
            $ceksks = DB::select("SELECT IFNULL(SUM(m.sks), 0) AS sisasks FROM tbmatkul m JOIN tbjoin_matkul j ON m.kode_matkul = j.kode_matkul WHERE j.npm = :npm ", ['npm'=>$user]);

            $totalsks = $defaultsks - $ceksks[0]->sisasks;

            if($totalsks > $defaultsks){
                return response()->json([
                    'message' => 'sks anda sudah lebih dari budget'
                ], 401);   
            }
            // End Masih Bug

            $get = DB::select("SELECT m.*, j.hari, j.jam, j.jam_selesai FROM tbmatkul m LEFT JOIN tbjadwal j ON m.kode_matkul = j.kode_matkul WHERE m.kode_matkul NOT IN (SELECT jm.kode_matkul FROM tbjoin_matkul jm WHERE jm.npm = :u )", ['u'=>$user]);
    
            $selectjoin = DB::table('tbjoin_matkul AS j')
                    ->join('tbmatkul AS m', 'j.kode_matkul', '=', 'm.kode_matkul')
                    //sementara. hrs pk join ori.
                    ->leftjoin('tbjadwal AS jj', 'j.kode_matkul', '=', 'jj.kode_matkul') 
                    ->join('users AS u', 'j.npm', '=', 'u.npm')
                    ->select('j.id','m.kode_matkul','m.mata_kuliah_dosen', 'm.sks','m.nama_dosen', 'u.nama', 'u.npm', 'jj.hari', 'jj.jam','jj.jam_selesai','u.isDosen')
                    ->where('j.npm', '=', $user)
                    ->get();

            return response()->json([
                'matkulall'=>$get,
                'joinmatkul'=> $user,
                'selectjoin' => $selectjoin,
                'sks' => $totalsks
            ]);
        }else{
            return response()->json([
                'error'=>'Matkul Sudah Ada'
            ], 422);
        }

    }

    public function rekapanAbsen($kode_matkul)
    {
        $mytime = Carbon\Carbon::now();
        $user = Auth::user()->npm;

        $buatreturn = ModelAbsen::where('npm_mahasiswa', '=', $user)
                        ->select('npm_mahasiswa', 'kode_matkul', 'hari_absen', 'tanggal_absen', 'jam_absen_masuk', 'jam_absen_selesai', 'accdosen', 'foto_mhs')
                        ->where('kode_matkul', '=', $kode_matkul)
                        // ->where('tanggal_absen', '=', $mytime->toDateString())
                        // ->where(function($q){
                        //     $q->where('jam_absen_masuk', '!=', null)
                        //         ->orWhere('jam_absen_selesai', '!=', null);
                        // })
                        ->get();

        return $buatreturn;
    }

    public function testapi(Request $request)
    {
        // $mytime = Carbon\Carbon::now();
        // // echo $mytime->toDateString();

        // $user = Auth::user()->npm;
        // $cekduplikat = ModelAbsen::where('npm_mahasiswa', '=', $user)
        //                 ->where('kode_matkul', '=', $request->kode_matkul)
        //                 ->where('tanggal_absen', '=', $mytime->toDateString())
        //                 ->where('jam_absen_masuk', '!=', null)
        //                 ->where('jam_absen_selesai', '!=', null)
        //                 ->first();

        // return $cekduplikat;

        // $ceksks = DB::select("SELECT IFNULL(SUM(m.sks), 0) AS sisasks FROM tbmatkul m JOIN tbjoin_matkul j ON m.kode_matkul = j.kode_matkul WHERE j.npm = :npm ", ['npm'=>$user]);

        // return $ceksks[0]->sisasks;

    }
    /**
     * Store a newly created resource in storage.
     */

    public function deleteMatkul()
    {
        $npm = Auth::user()->npm;
        $delete = ModelJoinMatkul::where('npm', $npm)->delete();

        $get = DB::table('tbmatkul AS m')
                    ->leftjoin('tbjadwal AS j', 'm.kode_matkul', '=', 'j.kode_matkul')
                    ->select('m.*', 'j.hari', 'j.jam', 'j.jam_selesai')
                    ->get();
        $user = Auth::user();

        $defaultsks = 20;


        if($delete){
            return response()->json([
                'matkulall'=>$get,
                'joinmatkul'=> $user,
                'selectjoin'=>null,
                'sks'=>$defaultsks
            ]);
        }
    }

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
