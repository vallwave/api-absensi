<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AbsenModel;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Ramsey\Uuid\Uuid;
use Carbon\Carbon;

class AbsenController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login']]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tanggal' => 'required|date',
            'clockin' => 'required|date_format:H:i:s',
            'clockout' => 'required|date_format:H:i:s',
            'foto' => 'required|image',
            'foto_out' => 'required|image',
            'alasan' => 'required',
            'confidence' => 'required|numeric',
            'confidence_out' => 'required|numeric',
            'emotion' => 'required',
            'emotion_out' => 'required',
            'tipe' => 'required',
            'latitude' => 'required|numeric',
            'latitude_out' => 'required|numeric',
            'longitude' => 'required|numeric',
            'longitude_out' => 'required|numeric',

        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = JWTAuth::parseToken()->authenticate();
        $uuid = Uuid::uuid4();

        $absensi = $user->absensi()->create([
            'absen_id' => $uuid->toString(),
            'tanggal' => $request->tanggal,
            'clockin' => Carbon::parse($request->tanggal . ' ' . $request->clockin)->format('Y-m-d H:i:s'),
            'clockout' => Carbon::parse($request->tanggal . ' ' . $request->clockout)->format('Y-m-d H:i:s'),
            'foto' => $request->foto->store('photos', 'public'),
            'foto_out' => $request->foto_out->store('photos', 'public'),
            'confidence' => $request->confidence,
            'confidence_out' => $request->confidence_out,
            'emotion' => $request->emotion,
            'emotion_out' => $request->emotion_out,
            'tipe' => $request->tipe,
            'alasan' => $request->alasan,
            'latitude' => $request->latitude,
            'latitude_out' => $request->latitude_out,
            'longitude' => $request->longitude,
            'longitude_out' => $request->longitude_out,
        ]);

        return response()->json($absensi, 200);
    }

    public function index()
    {
        $user = JWTAuth::parseToken()->authenticate();
        $absensi = $user->absensi()->get();

        return response()->json($absensi, 200);
    }
}
