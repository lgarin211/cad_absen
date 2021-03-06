<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LogAbsenController extends Controller
{

    public function __construct()
    {
        // parent::__construct();
        $par = DB::table('generalsettings')->get();
        $this->danss = [];
        foreach ($par as $key => $value) {
            $this->danss[$value->setting_name] = $value;
        }
        // dd($danss);
    }

    public function read()
    {
        $id = Auth::user()->id;
        $dam = DB::table('log_absens')->where('id_user', '=', $id)->orderBy('id', 'desc')->get();
        $data['dam'] = $dam;
        $data['setting'] = $this->danss;
        return view('absen/index', \compact('data'));
    }
    public function keluar()
    {
        date_default_timezone_set('Asia/Jakarta');
        $info = Auth::user();
        $date = date('m-d-Y H:i:s');
        DB::table('log_absens')
            ->where('id_user', $info->id)
            ->update(['jam_keluar' => $date]);
        $dam = DB::table('log_absens')->where('id_user', '=', $info->id)->orderBy('id', 'desc')->first();
        $val = strtotime($date) - strtotime($dam->jam_masuk);
        $menit = ($val / 60);
        $jam = floor($menit / 60);
        $das = $info->jumlah_jam_kerja + $jam;
        DB::table('users')
            ->where('id', $info->id)
            ->update(['jumlah_jam_kerja' => $das]);
            // dd($_GET);
            if (!empty($_GET['dar'])) {
                return redirect('/absen');
            }
        return \response(['status' => $info->name . ' Telah Melakukan Absensi Keluar']);
    }
    public function cekon()
    {
        $la = false;
        $ban = DB::table('log_absens')
            ->where('id_user', '=', Auth::user()->id)
            ->where('jam_masuk', 'like', '%' . $_GET['vas'] . '%')
            ->get();
        // dd($ban);
        if (\count($ban) > 0) {
            $la = true;
        } else {
            $la = false;
        }
        return response(['status' => $la, 'data' => $ban]);
    }
    public function retable()
    {
        $ban = DB::table('log_absens')
            ->where('id_user', '=', Auth::user()->id)
            ->get();
        $pas = [];
        foreach ($ban as $key => $value) {
            $wx1 = explode(' ', $value->jam_masuk);
            if ($value->jam_keluar == 0) {
                $wx2[1] = $wx1[1];
            } else {
                $wx2 = explode(' ', $value->jam_keluar);
            }
            $vas = (strtotime($wx2[1]) - strtotime($wx1[1])) / 60 / 60;
            $pas[$key] = ([
                "jam_masuk" => $wx1[1],
                "tgl_masuk" => $wx1[0],
                "jam_keluar" => $wx2[1],
                "status" => $value->keterangan,
                "akumu" => $vas,
            ]);
        }
        // dd($pas);
        return view('absen/table', ['data' => $pas]);
    }
    public function cekon2()
    {
        $la = false;
        $ban = DB::table('log_absens')
            ->where('id_user', '=', Auth::user()->id)
            ->where('jam_keluar', 'like', '%' . $_GET['vas'] . '%')
            ->get();
        if (\count($ban) > 0) {
            $la = true;
        } else {
            $la = false;
        }
       
        return response(['status' => $la, 'data' => count($ban)]);
    }
    public function create(Request $request)
    {
        $info = Auth::user();
        date_default_timezone_set('Asia/Jakarta');
        $date = date('m-d-Y H:i:s');
        $date2 = md5(uniqid($date, true));
        // dd($date2);
        if (!empty($_POST)) {
            $bukti = '';
            if ($bukti == '') {
                $request->file('foto');
                $imageName = $date2. '.png';
                $request->foto->move(public_path('img/' . Auth::user()->nip . '/'), $imageName);
                $bukti = 'img/' . Auth::user()->nip . '/' . $imageName;
            }
            $data = [
                'id_user' => $info->id,
                'jam_masuk' => $date,
                'jam_keluar' => 0,
                'bukti_masuk' => $bukti,
                'keterangan' => $_POST['keterangan'],
            ];
            DB::table('log_absens')->insert($data);
            return \redirect('/absen');
        }
    }
    public function capture()
    {
        return view('absen/capture');
    }
    public function capture2()
    {
        return view('absen/capture2');
    }
    public function capturePost(Request $request)
    {
        $thumb = $request->file('foto');
        $thumb->move(public_path() . "/capture" . '/', $thumb->getClientOriginalName());
    }
}
