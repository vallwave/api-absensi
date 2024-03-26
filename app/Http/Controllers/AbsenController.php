<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\AbsenModel;

class AbsenController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function clockIn(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tanggal' => 'required|date',
            'foto' => 'required|image',
            'confidence' => 'required|numeric',
            'emotion' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'tipe' => 'required|string',
            'alasan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $user = JWTAuth::user();

        $absen = AbsenModel::create([
            'user_id' => $user->id,
            'tanggal' => $request->tanggal,
            'clockin' => now(),
            'foto' => $request->foto,
            'confidence' => $request->confidence,
            'emotion' => $request->emotion,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'tipe' => $request->tipe,
            'alasan' => $request->alasan,
        ]);

        return response()->json($absen, 201);
    }

    public function clockOut(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'absen_id' => 'required|exists:absensi,absen_id',
            'foto' => 'required|image',
            'confidence' => 'required|numeric',
            'emotion' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $absen = AbsenModel::findOrFail($request->absen_id);
        $absen->clockout = now();
        $absen->foto_out = $request->foto;
        $absen->confidence_out = $request->confidence;
        $absen->emotion_out = $request->emotion;
        $absen->latitude_out = $request->latitude;
        $absen->longitude_out = $request->longitude;
        $absen->save();

        return response()->json($absen, 200);
    }

    public function getAbsensi(Request $request)
    {
        $user = JWTAuth::user();
        $absensi = AbsenModel::where('user_id', $user->id)
            ->orderBy('tanggal', 'desc')
            ->get();

        return response()->json($absensi, 200);
    }
}
