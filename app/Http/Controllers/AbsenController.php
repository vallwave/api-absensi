<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AbsenModel;
use App\Models\AbsenWfhModel;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Ramsey\Uuid\Uuid;
use Carbon\Carbon;
use App\Models\CompanyModel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;

class AbsenController extends Controller
{
    protected $long;
    protected $lat;

    public function __construct()
    {
        $this->middleware('auth:api');
        $company = CompanyModel::where('id', auth()->user()->id_company)->first();
        $this->long = $company->long;
        $this->lat = $company->lat;
    }

    public function clockIn(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lat'           => 'required',
            'long'          => 'required',
            'foto'          => 'required|image',
            'tipe'          => 'required',
            'alasan'        => 'required_if:tipe,!=,1|min:10',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = JWTAuth::user();
        $uuid = Uuid::uuid4();

        $absen = $user->absensi()
            ->where('tanggal', date('Y-m-d'))
            ->first();

        if ($absen && $absen->clockin) {
            return response()->json(['message' => 'You have already clocked in.'], 400);
        }

        $isCheckSchedule = $user->shift()->first();
        $hourNow = strtotime(date('H:i'));

        if ($isCheckSchedule) {
            $shiftStart = strtotime($isCheckSchedule->jam_mulai);
            $shiftEnd = strtotime($isCheckSchedule->jam_selesai);

            if ($hourNow >= $shiftStart && $hourNow <= $shiftEnd) {
                $dataFoto = $request->file('foto');
                $fileName = uniqid() . '.' . $dataFoto->getClientOriginalExtension();
                $dataFoto->storeAs('photos', $fileName, 'public');

                $data = [
                    'absen_id'      => $uuid->toString(),
                    'user_id'       => $user->id,
                    'tanggal'       => date('Y-m-d'),
                    'clockin'       => Carbon::now()->format('Y-m-d H:i:s'),
                    'foto'          => $fileName,
                    'tipe'          => $request->tipe,
                    'alasan'        => $request->alasan,
                    'latitude'      => $request->lat,
                    'longitude'     => $request->long,
                ];

                $jarak = $this->haversineDistance($data['latitude'], $data['longitude'], $this->lat, $this->long);

                if ($jarak > 100 && ($data['tipe'] == 1 || $data['tipe'] == 6)) {
                    return response()->json(['message' => 'Your distance from the office is too far (' . round($jarak) . ' m)'], 400);
                } else {
                    AbsenModel::create($data);
                    return response()->json($data, 200);
                }
            } else {
                if ($hourNow >= strtotime($isCheckSchedule->jam_selesai)) {
                    return response()->json(['message' => 'Your shift has ended. Please clock in between ' . $isCheckSchedule->jam_mulai . ' and ' . $isCheckSchedule->jam_selesai . '.'], 400);
                } else {
                    return response()->json(['message' => 'You cannot clock in yet. Please clock in between ' . $isCheckSchedule->jam_mulai . ' and ' . $isCheckSchedule->jam_selesai . '.'], 400);
                }
            }
        } else {
            return response()->json(['message' => 'Your schedule has not been set. Please contact the administrator.'], 400);
        }
    }

    public function clockOut(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lat'               => 'required',
            'long'              => 'required',
            'foto'              => 'required|image',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = JWTAuth::user();
        $absen = $user->absensi()
            ->whereDate('tanggal', date('Y-m-d'))
            ->whereNotNull('clockin')
            ->whereNull('clockout')
            ->first();

        if (!$absen) {
            return response()->json(['message' => 'You have not clocked in today or have already clocked out.'], 400);
        }

        $dataFoto = $request->file('foto');
        $fileName = uniqid() . '.' . $dataFoto->getClientOriginalExtension();
        $dataFoto->storeAs('photos', $fileName, 'public');

        $data = [
            'clockout'          => Carbon::now()->format('Y-m-d H:i:s'),
            'foto_out'          => $fileName,
            'latitude_out'      => $request->lat,
            'longitude_out'     => $request->long,
        ];

        $jarak = $this->haversineDistance($data['latitude_out'], $data['longitude_out'], $this->lat, $this->long);

        if ($jarak > 100 && ($absen->tipe == 1 || $absen->tipe == 6)) {
            return response()->json(['message' => 'Your distance from the office is too far (' . round($jarak) . ' m)'], 400);
        } else {
            $absen->update($data);
            return response()->json($absen, 200);
        }
    }

    public function clockInWfh(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lat'               => 'required',
            'long'              => 'required',
            'foto'              => 'required|image',
            'tipe'              => 'required',
            'alasan'            => 'required_if:tipe,!=,1|min:10',
            'emotion'           => 'required',
            'confidence'        => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $uuid = Uuid::uuid4();
        $absenWfh = AbsenWfhModel::where('clockin', '>=', Carbon::today())
            ->where('clockout', null)
            ->first();

        if ($absenWfh) {
            return response()->json(['message' => 'You have already clocked in for WFH today.'], 400);
        }

        $dataFoto = $request->file('foto');
        $fileName = uniqid() . '.' . $dataFoto->getClientOriginalExtension();
        Storage::disk('public')->put('photos/' . $fileName, file_get_contents($dataFoto));

        $absenId = Uuid::uuid4()->toString();
        Session::put('absen_id', $absenId);

        $data = [
            'absen_wfh_id'      => $uuid->toString(),
            'absen_id'          => $absenId,
            'clockin'           => Carbon::now()->format('Y-m-d H:i:s'),
            'foto'              => $fileName,
            'tipe'              => $request->tipe,
            'alasan'            => $request->alasan,
            'latitude'          => $request->lat,
            'longitude'         => $request->long,
            'emotion'           => $request->emotion,
            'confidence'        => $request->confidence,
        ];

        AbsenWfhModel::create($data);
        return response()->json($data, 200);
    }

    public function clockOutWfh(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lat'               => 'required|min:5|max:50',
            'long'              => 'required|min:5|max:50',
            'foto'              => 'required|image|mimes:jpg,jpeg,png,gif',
            'confidence_out'    => 'required',
            'emotion_out'       => 'required',
            // 'absen_id'       => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $absenId = Session::get('absen_id');
        $absenWfh = AbsenWfhModel::where('absen_id', $request->post('absen_id'))
            ->where('clockout', null)
            ->first();

        // dd($absenId);

        if (!$absenWfh) {
            return response()->json(['message' => 'Kamu belum clock-in atau kamu sudah melakukan clock-out'], 400);
        }

        $dataFoto = $request->file('foto');
        $fileName = uniqid() . '.' . $dataFoto->getClientOriginalExtension();
        Storage::disk('public')->put('photos/' . $fileName, file_get_contents($dataFoto));

        $data = [
            'clockout'          => Carbon::now()->format('Y-m-d H:i:s'),
            'foto_out'          => $fileName,
            'latitude_out'      => $request->lat,
            'longitude_out'     => $request->long,
            'updated_at'        => Carbon::now()->format('Y-m-d H:i:s'),
            'confidence_out'    => $request->confidence_out,
            'emotion_out'       => $request->emotion_out,
        ];

        $absenWfh->update($data);
        return response()->json($absenWfh, 200);
    }



    private function haversineDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371;
        $latDiff = deg2rad($lat2 - $lat1);
        $lonDiff = deg2rad($lon2 - $lon1);

        $a = sin($latDiff / 2) * sin($latDiff / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($lonDiff / 2) * sin($lonDiff / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    // public function __construct()
    // {
    //     $this->middleware('auth:api', ['except' => ['login']]);
    // }

    // /**
    //  * Store a newly created resource in storage.
    //  *
    //  * @param  \Illuminate\Http\Request  $request
    //  * @return \Illuminate\Http\Response
    //  */
    // public function store(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'tanggal' => 'required|date',
    //         'clockin' => 'required|date_format:H:i:s',
    //         'clockout' => 'required|date_format:H:i:s',
    //         'foto' => 'required|image',
    //         'foto_out' => 'required|image',
    //         'alasan' => 'required',
    //         'confidence' => 'required|numeric',
    //         'confidence_out' => 'required|numeric',
    //         'emotion' => 'required',
    //         'emotion_out' => 'required',
    //         'tipe' => 'required',
    //         'latitude' => 'required|numeric',
    //         'latitude_out' => 'required|numeric',
    //         'longitude' => 'required|numeric',
    //         'longitude_out' => 'required|numeric',

    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json($validator->errors(), 400);
    //     }

    //     $user = JWTAuth::parseToken()->authenticate();
    //     $uuid = Uuid::uuid4();

    //     $absensi = $user->absensi()->create([
    //         'absen_id' => $uuid->toString(),
    //         'tanggal' => $request->tanggal,
    //         'clockin' => Carbon::parse($request->tanggal . ' ' . $request->clockin)->format('Y-m-d H:i:s'),
    //         'clockout' => Carbon::parse($request->tanggal . ' ' . $request->clockout)->format('Y-m-d H:i:s'),
    //         'foto' => $request->foto->store('photos', 'public'),
    //         'foto_out' => $request->foto_out->store('photos', 'public'),
    //         'confidence' => $request->confidence,
    //         'confidence_out' => $request->confidence_out,
    //         'emotion' => $request->emotion,
    //         'emotion_out' => $request->emotion_out,
    //         'tipe' => $request->tipe,
    //         'alasan' => $request->alasan,
    //         'latitude' => $request->latitude,
    //         'latitude_out' => $request->latitude_out,
    //         'longitude' => $request->longitude,
    //         'longitude_out' => $request->longitude_out,
    //     ]);

    //     return response()->json($absensi, 200);
    // }
}
