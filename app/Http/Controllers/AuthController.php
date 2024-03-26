<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;


class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_nik' => 'required',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $credentials = $request->only('user_nik', 'password');

        if (!$token = Auth::attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60,
            'user' => auth()->user()
        ]);
    }

    // public function register(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'user_nik' => 'required',
    //         'user_name' => 'required',
    //         'user_email' => 'required|string|email|unique:users',
    //         'password' => 'required|string|confirmed',
    //         'user_position' => 'required', // Ubah menjadi user_position
    //         'user_phone' => 'required',
    //         'user_admin' => 'required',
    //         'user_skema' => 'required',
    //         'user_status' => 'required',
    //         'date_of_birth' => 'required',
    //         'date_in_company' => 'required',
    //         'photo' => 'required|image|mimes:jpg|max:2048'
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json($validator->errors()->toJson(), 400);
    //     }

    //     // Dapatkan role berdasarkan nama atau kode yang diberikan
    //     $role = Role::where('code', $request->user_position)
    //         ->orWhere('name', $request->user_position)
    //         ->first();

    //     // Jika role tidak ditemukan, kembalikan respon dengan pesan error
    //     if (!$role) {
    //         return response()->json(['message' => 'Role tidak ditemukan'], 404);
    //     }

    //     // Proses upload gambar
    //     $photo = $request->file('photo');
    //     $photoName = Uuid::uuid4()->toString() . '.' . $photo->getClientOriginalExtension(); // Buat nama file baru dengan UUID
    //     $photo->move(public_path('photos'), $photoName); // Pindahkan gambar ke direktori public/photos

    //     $uuid = Uuid::uuid4(); // Generate UUID
    //     $user = User::create(array_merge(
    //         $validator->validated(),
    //         [
    //             'user_id' => $uuid->toString(), // Assign UUID to user_id
    //             'password' => bcrypt($request->user_password), // Hash password
    //             'user_trello' => null, // Set user_trello to null
    //             'email_verified_at' => now(), // Set email_verified_at to current timestamp
    //             'id_role' => $role->id, // Set id_role based on retrieved role
    //             'photo' => $photoName // Simpan nama file gambar
    //         ]
    //     ));
    //     $user->markEmailAsVerified();

    //     return response()->json([
    //         'Pesan' => 'Berhasil Register',
    //         'User' => $user
    //     ], 201);
    // }
}
