<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use JWTAuth;
use App\Models\ModelJoinMatkul;



class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',

        ]);
        $credentials = $request->only('email', 'password');
        $token = Auth::attempt($credentials);

        
        if (!$token) {
            User::where('email', $request->email)
                ->update([
                    'isLogin' => 0
                ]);

            return response()->json([
                'message' => 'Tidak Terauth',
            ], 401);
        }

        $user = Auth::user();

        $validasi = User::where('id', $user->id)
            ->update([
                'isLogin' => 1
            ]);

        $buatreturn = User::find($user->id);

        return response()->json([
            'user' => $buatreturn,
            'authorization' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);

    }

    public function me()
    {
        $me = Auth::user();
        $matkul = ModelJoinMatkul::where('npm', $me->npm)->get();
        return response()->json([
            'saya' => $me,
            'matkulsaya' => $matkul
        ], 200);
        
        // return $me;
    }

    public function register(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            // 'npm' => 'nullable|integer',
            'password' => 'required|string|min:6',
            'isDosen' => 'required',
            // 'kode_dosen' => 'nullable'
        ]);

        $ceknpm = $request->npm;
        $cekkodedosen = $request->kode_dosen;

        if($ceknpm != null && $cekkodedosen != null){
            return response()->json(['message'=>'Pilih Salah Satu, Dosen / Siswa'], 401);
        }
        if($ceknpm == null && $cekkodedosen == null){
            return response()->json(['message'=>'Isi Salah Satu Woi'], 500);
        }

        if($ceknpm != null){
            $user = User::create([
                'nama' => $request->nama,
                'email' => $request->email,
                'npm' => $request->npm,
                'password' => Hash::make($request->password),
                'isDosen' => $request->isDosen
            ]);
        }

        if($cekkodedosen != null){
            $user = User::create([
                'nama' => $request->nama,
                'email' => $request->email,
                'kode_dosen' => $request->kode_dosen,
                'password' => Hash::make($request->password),
                'isDosen' => $request->isDosen
            ]);
        }

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user
        ]);
    }

    public function logout(Request $request)
    {
        // Auth::logout();
        // return response()->json([
        //     'message' => 'Successfully logged out',
        // ]);

        // // Maybe set below to `false`,
        // // else cache may take too much storage.
        $forever = true;
        $user = Auth::user();
        User::where('id', $user->id)
            ->update([
                'isLogin' => $request->isLogin
            ]);

        // // Both loads and blacklists
        // // (the token, if it's set, else may raise exception).
        $check = JWTAuth::parseToken()->invalidate( $forever );
        return $check ? 'Bisa Logout' : 'Belum Logout';
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $val = $request->validate([
            'nama' => 'required|string|max:255',
            'npm' => 'nullable',
            'semester' => 'nullable',
            'alamat' => 'nullable',
            'program_studi' => 'nullable',
            'kelas' => 'nullable',
            'jenjang_studi' => 'nullable'
        ]);

        $cek = User::where('id', $user->id)
            ->update($val);
        
        $getuser = User::find($user->id);

        if($cek){
            return response()->json([
                'saya'=>$getuser
            ], 200);
        }else{
            return 'gagal';
        }
    }

    public function refresh()
    {
        return response()->json([
            'user' => Auth::user(),
            'authorization' => [
                'token' => Auth::refresh(),
                'type' => 'bearer',
            ]
        ]);
    }
}
