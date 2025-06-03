<?php
namespace App\Controllers\Admin;

date_default_timezone_set("Asia/Jakarta");

use App\Controllers\BaseController;
use PhpOffice\PhpSpreadsheet\Calculation\Calculation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use App\Models\Am21Model;
use App\Models\Am21bModel;
use App\Models\Bm06Model;
use App\Models\TransaksiModel;
use App\Models\NegaraTujuanModel;
use App\Models\PersonilModel;
use App\Models\BiayaModel;
use App\Models\KategoriModel;
use App\Models\PumModel;
use App\Models\ValasModel;
use App\Models\PjumModel;
use App\Models\PbModel;
use App\Models\KursModel;
use App\Models\LogEmailModel;
use App\Models\LogEmailAllModel;

class Dashboard extends BaseController
{
    public function __construct()
    {
        $this->m_am21 = new Am21Model();
        $this->m_am21b = new Am21bModel();
        $this->m_bm06 = new Bm06Model();
        $this->m_id = new TransaksiModel();
        $this->m_negara_tujuan = new NegaraTujuanModel();
        $this->m_personil = new PersonilModel();
        $this->m_biaya = new BiayaModel();
        $this->m_kategori = new KategoriModel();
        $this->m_pum = new PumModel();
        $this->m_valas = new ValasModel();
        $this->m_pjum = new PjumModel();
        $this->m_pb = new PbModel();
        $this->m_kurs = new KursModel();
        $this->m_log_email = new LogEmailModel();
        $this->m_log_email_all = new LogEmailAllModel();

        helper('global_fungsi_helper');
        helper('url');
    }

    public function dashboard($id_transaksi)
    {   
        $nik= session()->get('akun_nik');
        $niknm= session()->get('niknm');
        $role= session()->get('akun_role');
        $strorg = session()->get('strorg');
        $submit_pjum = $this->m_id->where('id_transaksi', $id_transaksi)->select('submit_pjum, kirim_pjum')->first();
        $submit_pb = $this->m_id->where('id_transaksi', $id_transaksi)->select('submit_pb, kirim_pb')->first();
        $kota = $this->m_id->where('id_transaksi', $id_transaksi)->select('kota')->first();

        $login = $this->m_id->where('id_transaksi', $id_transaksi)->select('login_by')->first();

        if ($role == 'admin' && $login['login_by'] != $niknm || $role == 'user' && $login['login_by'] != $niknm) {
            session()->setFlashdata('warning', ['Id transaksi sedang diedit, harap menunggu beberapa saat lagi']);
            return redirect()->to("transaksi");
        } else if ($role == 'treasury' || $role == 'gs'){
            
        }

        if($role == 'admin'){
            $dataPost=$this->m_id->getPostId($id_transaksi, substr($strorg,0,4));
        } else if($role == 'user'){
            $dataPost=$this->m_id->getId($id_transaksi, $nik);
        } else if($role == 'treasury'){
            $dataPost=$this->m_id->getTreasuryDashboard($id_transaksi);
        } else if($role == 'gs'){
            $dataPost=$this->m_id->getGSDashboard($id_transaksi);
        }
        if(empty($dataPost)) {
            return redirect()-> to("transaksi");
        }
        $data = $dataPost;

        if ($role == 'gs' && $submit_pjum['submit_pjum'] < 2 && $submit_pb['submit_pb'] < 2) {
            return redirect()-> to("transaksi");
        }

        $personil = $this->m_personil->getDataAllId($id_transaksi);
        $negara = $this->m_negara_tujuan->getDataAllId($id_transaksi);

        if(empty($personil)) {
            session()->setFlashdata('warning', ['Silahkan lengkapi data perjalanan dinas luar negeri']);
            return redirect()-> to("tambahpersonil/".$id_transaksi);
        }

        if(empty($negara)) {
            session()->setFlashdata('warning', ['Silahkan lengkapi data perjalanan dinas luar negeri']);
            return redirect()-> to("tambahnegara/".$id_transaksi);
        }

        $id_transaksi = [
            'id_transaksi' => $id_transaksi,
        ];
        session()->set($id_transaksi);

        $id_transaksi = session()->get('id_transaksi');
        
        if($role == 'user'){
            $hasil=$this->m_id->listId($id_transaksi, $nik);
        } else {
            $hasil=$this->m_id->listIdTransaksi($id_transaksi);
        }

        $cek = $this->m_kategori->cek($id_transaksi);

        if($role == 'treasury' || $role == 'gs'){
            if(empty($cek)) {
                return redirect()-> to("transaksi");
            }
        }

        $cekdatapb = $this->m_kategori->cekpb($id_transaksi, 'PB');

        if($role == 'treasury' && $submit_pjum['submit_pjum'] == 0 && empty($cekdatapb)){
            return redirect()-> to("transaksi");
        }

        $nopb = $this->m_pb->where('id_transaksi', $id_transaksi)->select('created_by')->findAll();

        session()->set('url_transaksi', current_url());
        
        $data=[
            'header' => "Dashboard",
            'id_transaksi'=> $this->m_id->getDataAll(),
            'hasil' => $hasil,
            'neg'=> $this->m_negara_tujuan->getDataAllId($id_transaksi),
            'submit_pjum' => $submit_pjum['submit_pjum'],
            'submit_pb' => $submit_pb['submit_pb'],
            'kirim_pjum' => $submit_pjum['kirim_pjum'],
            'kirim_pb' => $submit_pb['kirim_pb'],
            'role' => $role,
            'kota1' => $this->m_id->kota($id_transaksi),
            'solo' => $kota['kota'],
            'role' => $role,
            'id' => $id_transaksi,
            'cek' => $cek,
            'cekpjum' => $this->m_kategori->alldataId($id_transaksi, 'PJUM'),
            'cekpb' => $this->m_kategori->alldataId($id_transaksi, 'PB'),
            'biayapb' => $cekdatapb,
            'nopb' => $nopb,
        ];
        echo view('ui/v_header', $data);
        echo view('admin/v_dashboard', $data);
        echo view('ui/v_footer', $data);
        // print_r(session()->get());
    }

    public function biayaxls()
    {
        return $this->response->download('./formatExcel/Format Biaya Perjalanan Dinas Luar Negeri.xls', null);
    }

    public function biayaxlsx()
    {
        return $this->response->download('./formatExcel/Format Biaya Perjalanan Dinas Luar Negeri.xlsx', null);
    }

    public function supportxls()
    {
        return $this->response->download('./formatExcel/Format Biaya Support Perjalanan Dinas Luar Negeri.xls', null);
    }

    public function support($id_transaksi)
    {
        $nik= session()->get('akun_nik');
        $role= session()->get('akun_role');
        $kota = $this->m_id->where('id_transaksi', $id_transaksi)->select('kota')->first();
        if($role != 'gs'){
            return redirect()-> to("transaksi");
        }
        if($kota['kota'] == 'Surakarta'){
            return redirect()-> to("transaksi");
        }

        $strorg = session()->get('strorg');
        if($role == 'gs'){
            $dataPost=$this->m_id->getGSDashboard($id_transaksi);
        } else {
            return redirect()-> to("transaksi");
        }

        if(empty($dataPost)) {
            return redirect()-> to("transaksi");
        }
        $data = $dataPost;

        if($role == 'gs'){
            $id=$this->m_id->getGS($id_transaksi);
        }

        $submit = $this->m_id->where('id_transaksi', $id_transaksi)->select('submit_pjum, submit_pb')->first();

        if ($role == 'gs' && $submit['submit_pjum'] == 4 && $submit['submit_pb'] > 2) {
            
        } else {
            return redirect()-> to("transaksi");
        }

        if ($role == 'gs') {
            if($this->request->getMethod()=='post') {
                $data = [
                    'id_transaksi' => $id_transaksi,
                    'submit_pb' => 2,
                ];
                $this->m_id->save($data);
                session()->setFlashdata('success', 'Silahkan Revisi Data PB');
                return redirect()->to('dashboard/'.$id_transaksi);
            }
        }

        $sumBiaya = $this->m_biaya->totalsupport($id_transaksi);

        $submit_pjum = $this->m_id->where('id_transaksi', $id_transaksi)->select('submit_pjum')->first();
        $submit_pb = $this->m_id->where('id_transaksi', $id_transaksi)->select('submit_pb')->first();

        $cek = $this->m_kategori->cek($id_transaksi);

        if($role == 'treasury' || $role == 'gs'){
            if(empty($cek)) {
                return redirect()-> to("transaksi");
            }
        }

        $nopb = $this->m_pb->where('id_transaksi', $id_transaksi)->select('created_by')->findAll();

        session()->set('url_transaksi', current_url());

        $data=[
            'header' => "Biaya Support",
            'id_transaksi'=> $this->m_id->getDataAll(),
            'id' => $id,
            'neg'=> $this->m_negara_tujuan->getDataAllId($id_transaksi),
            'dataPost' => $dataPost,
            'submit' => $submit['submit_pb'],
            'kota' => $this->m_id->kota($id_transaksi),
            'solo' => $kota['kota'],
            'kategori' => $this->m_kategori->alldatasupport($id_transaksi),
            'biaya' => $this->m_biaya->alldatasupport($id_transaksi),
            'total' => $sumBiaya,
            'role' => $role,
            'submit_pjum' => $submit_pjum['submit_pjum'],
            'submit_pb' => $submit_pb['submit_pb'],
            'cek' => $cek,
            'cekpjum' => $this->m_kategori->alldataId($id_transaksi, 'PJUM'),
            'cekpb' => $this->m_kategori->alldataId($id_transaksi, 'PB'),
            'nopb' => $nopb,
        ];

        echo view('admin/v_support', $data);
        // print_r(session()->get());
    }

    public function gsselesaisupport($jenis_biaya, $id_transaksi)
    {
        $role= session()->get('akun_role');
        if($role != 'gs'){
            return redirect()-> to("transaksi");
        }
        // $ceksup = $this->m_kategori->cekpb($id_transaksi, 'Support');

        // if(empty($ceksup)){
        //     session()->setFlashdata('warning', ['Tambahkan biaya support terlebih dahulu untuk melakukan submit data']);
        //     return redirect()->to('support/'.$id_transaksi);
        // } else {
            
        // }

        $data = [
            'id_transaksi' => $id_transaksi,
            'submit_pb' => 4,
        ];

        $timestamp = date('Y-m-d H:i:s');

        $nikuser = $this->m_id->where('id_transaksi', $id_transaksi)->select('nik, strorgnm')->first();

        $cek_log_email = $this->m_log_email->where('id_transaksi', $id_transaksi)->select('id_log_email')->first();

        if (empty($cek_log_email)) {
            $log_email = [
                'id_transaksi' => $id_transaksi,
                'title' => 'Data PB Telah Selesai',
                'nik' => $nikuser['nik'],
                'submit_pb' => 4,
                'kirim_pb' => 1,
                'waktu_kirim' => $timestamp,
            ];
            $this->m_log_email->insert($log_email);
        } else {
            $log_email = [
                'id_log_email' => $cek_log_email['id_log_email'],
                'submit_pb' => 4,
                'kirim_pb' => 1,
                'waktu_kirim' => $timestamp,
            ];
            $this->m_log_email->save($log_email);
        }

        $log_email_all = [
            'id_transaksi' => $id_transaksi,
            'title' => 'Data PB Telah Selesai',
            'nik' => $nikuser['nik'],
            'submit_pb' => 4,
            'waktu_kirim' => $timestamp,
        ];
        $this->m_log_email_all->insert($log_email_all);
        $this->m_id->save($data);
        session()->setFlashdata('success', 'Biaya Support berhasil disubmit');
        return redirect()->to('support/'.$id_transaksi);
    }

    public function editbiayasupport($id_biaya, $id_kategori, $id_transaksi, $jenis_biaya)
    {
        $nik= session()->get('akun_nik');
        $niknm= session()->get('niknm');
        $role= session()->get('akun_role');
        $strorg = session()->get('strorg');

        $submit_pjum = $this->m_id->where('id_transaksi', $id_transaksi)->select('submit_pjum, kirim_pjum')->first();
        $submit_pb = $this->m_id->where('id_transaksi', $id_transaksi)->select('submit_pb, kirim_pb')->first();

        if($role == 'admin'){
            $dataPost=$this->m_id->getPostId($id_transaksi, substr($strorg,0,4));
        } else if($role == 'user'){
            $dataPost=$this->m_id->getId($id_transaksi, $nik);
        } else if($role == 'treasury' && $submit_pb['submit_pb'] == 0){
            $dataPost=$this->m_id->getTreasuryDash($id_transaksi);
        } else if($role == 'treasury' && $submit_pb['submit_pb'] == 1){
            $dataPost=$this->m_id->getTreasuryDashboard($id_transaksi);
        } else if($role == 'gs'){
            $dataPost=$this->m_id->getGSDashboard($id_transaksi);
        }

        if(empty($dataPost)) {
            return redirect()-> to("transaksi");
        }

        $data = $dataPost;

        if($role == 'treasury'){
            $id=$this->m_id->getTreasury($id_transaksi);
        } else if($role == 'gs'){
            $id=$this->m_id->getGS($id_transaksi);
        } else {
            $id=$this->m_id->getPostId($id_transaksi, substr($strorg,0,4));
        }

        $ses = [
            'id_transaksi' => $id_transaksi,
            'jenis_biaya' => $jenis_biaya,
        ];
        
        session()->set($ses);

        if ($role == 'treasury' && $submit_pjum['submit_pjum'] == 0 && $submit_pb['submit_pb'] == 0) {
            return redirect()-> to("transaksi");
        } else if ($role == 'gs' && $submit_pb['submit_pb'] != 3) {
            return redirect()-> to("transaksi");
        }

        $valas = $this->m_biaya->valassupport($id_transaksi);
        $kode_valas = $this->m_biaya->kode_valassupport($id_transaksi);

        $kota = $this->m_id->where('id_transaksi', $id_transaksi)->select('kota')->first();
        $cek = $this->m_kategori->cek($id_transaksi);

        if($role == 'treasury' || $role == 'gs'){
            if(empty($cek)) {
                return redirect()-> to("transaksi");
            }
        }

        if($this->request->getMethod() == 'post') {
            $nik= session()->get('akun_nik');
            $timestamp = date('Y-m-d H:i:s');
            $biaya = $this->request->getVar('biaya');
            $comma = ',';
            $number = preg_replace('/[^0-9\\-]+/','', $biaya);
            if( strpos($biaya, $comma) !== false ) {
                $string = $number/100;
            } else {
                $string = $number;
            }
            
            $data = [
                'id_biaya' => $id_biaya,
                'biaya' => $string,
            ];
            $this->m_biaya->save($data);

            session()->setFlashdata('success', 'Biaya Support berhasil diubah');
            return redirect()->to('support/'.$id_transaksi);
        }

        $nopb = $this->m_pb->where('id_transaksi', $id_transaksi)->select('created_by')->findAll();

        session()->set('url_transaksi', current_url());
        
        $data=[
            'header' => "Edit Biaya Support",
            'id' => $id,
            'dataPost' => $dataPost,
            'kategori' => $this->m_kategori->alldatasupport($id_transaksi),
            'biaya' => $this->m_biaya->alldatasupportedit($id_transaksi, $id_biaya),
            'role' => $role,
            'kota' => $this->m_id->kota($id_transaksi),
            'solo' => $kota['kota'],
            'submit' => $submit_pb['submit_pb'],
            'submit_pjum' => $submit_pjum['submit_pjum'],
            'submit_pb' => $submit_pb['submit_pb'],
            'cek' => $cek,
            'cekpjum' => $this->m_kategori->alldataId($id_transaksi, 'PJUM'),
            'cekpb' => $this->m_kategori->alldataId($id_transaksi, 'PB'),
            'index' => count((array)$valas),
            'kode_valas' => $kode_valas,
            'neg' => $this->m_negara_tujuan->getDataAllId($id_transaksi),
            'nopb' => $nopb,
        ];
        echo view('proses/support/v_biayasupport', $data);
        // print_r(session()->get());
        // echo Currencies::getSymbol('THB');
    }

    public function importsupport($id_transaksi)
    {
        $file = $this->request->getFile('file_excel_support');
        $ext = $file->getClientExtension();

        if($ext == 'xls'){
            $render = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
        } else{
            $render = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        }
        $spreadsheet = $render->load($file);
        $sheet = $spreadsheet->getActiveSheet()->toArray();
        $worksheet = $spreadsheet->getActiveSheet();
        $highestRow = $worksheet->getHighestRow(); // e.g. 10
        $highestColumn = $worksheet->getHighestColumn(); // e.g 'F'
        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

        $a=1;
        $b=1;
        $c=1;
        $d=1;

        for($r = 7; $r < $highestRow; $r++) {
            if(!empty($sheet[$r][4])){
                $tanggal = $sheet[$r][1];
                if (empty($tanggal)) {
                    $tanggal = null;
                }
                $kategori = $sheet[$r][2];
                if (empty($kategori)) {
                    $kategori = null;
                }
                $jumlah_personil = $sheet[$r][3];
                if (empty($jumlah_personil)) {
                    $jumlah_personil = null;
                }

                $cekdata = $this->m_kategori->ceksupport($id_transaksi, $r+1);

                $data = [];
                if(empty($cekdata)) {
                    $data = [
                        'baris' => $r+1,
                        'id_transaksi' => $id_transaksi,
                        'jenis_biaya' => 'Support',
                        'tanggal' => $tanggal,
                        'kategori' => $kategori,
                        'jumlah_personil' => $jumlah_personil,
                    ];
                    $this->m_kategori->insert($data);
                } else if(empty($cekdata['baris'])) {
                    continue;
                } else if($id_transaksi == $cekdata['id_transaksi'] && $r+1 == $cekdata['baris']) {
                    $resultKategori = $this->m_kategori->where('id_transaksi', $id_transaksi)->where('baris', $r+1)->where('jenis_biaya', 'Support')->select('id_kategori as id_kategori')->first();
                    $data = [
                        'id_kategori' => $resultKategori['id_kategori'],
                        'jenis_biaya' => 'Support',
                        'tanggal' => $tanggal,
                        'kategori' => $kategori,
                        'jumlah_personil' => $jumlah_personil,
                    ];
                    $this->m_kategori->distinct($data['id_kategori']);
                    $this->m_kategori->save($data);
                }

                $data = [];
                $kode_valas = $sheet[6][4];
                $kode_valas = strtoupper($kode_valas);
                $biaya = preg_replace("/[^0-9\.]/", "", $sheet[$r][4]);
                $id_valas = $this->m_valas->where('kode_valas', $kode_valas)->select('id_valas as id_valas')->first();
                $simbol = $this->m_valas->where('kode_valas', $kode_valas)->select('simbol as simbol')->first();

                if (empty($biaya)) {
                    $biaya = 0;
                }
                if (empty($kode_valas)) {
                    $kode_valas = null;
                    $id_valas = null;
                    $simbol = null;
                }
                
                $resultKategori = $this->m_kategori->where('id_transaksi', $id_transaksi)->where('baris', $r+1)->where('jenis_biaya', 'Support')->select('id_kategori as id_kategori')->first();

                $cekvaluta = $this->m_biaya->ceksupport($id_transaksi, $r+1);
                if(empty($cekvaluta)) {
                    $data = [
                        'id_kategori' => $resultKategori['id_kategori'],
                        'baris' => $r+1,
                        'kolom' => 5,
                        'id_transaksi' => $id_transaksi,
                        'jenis_biaya' => 'Support',
                        'id_valas' => $id_valas,
                        'kode_valas' => $kode_valas,
                        'simbol' => $simbol,
                        'biaya' => $biaya,
                        'kategori' => $kategori,
                        'tanggal' => $tanggal,
                    ];
                    $this->m_biaya->insert($data);
                } else if(empty($cekvaluta['baris'])) {
                    
                } else if($id_transaksi == $cekvaluta['id_transaksi'] && $r+1 == $cekvaluta['baris']) {
                    $resultBiaya = $this->m_biaya->where('id_transaksi', $id_transaksi)->where('baris', $r+1)->where('jenis_biaya', 'Support')->select('id_biaya as id_biaya')->first();
                    $data = [
                        'id_biaya' => $resultBiaya['id_biaya'],
                        'id_kategori' => $resultKategori['id_kategori'],
                        'jenis_biaya' => 'Support',
                        'id_valas' => $id_valas,
                        'kode_valas' => $kode_valas,
                        'simbol' => $simbol,
                        'biaya' => $biaya,
                        'kategori' => $kategori,
                        'tanggal' => $tanggal,
                    ];
                    $this->m_biaya->distinct($data['id_biaya']);
                    $this->m_biaya->save($data);
                }
            } else {
                $cekidkategori = $this->m_kategori->where('id_transaksi', $id_transaksi)->where('jenis_biaya', 'Support')->select('id_kategori as id_kategori')->findAll();
                foreach ($cekidkategori as $key => $value) {
                    $data_id = [
                        'id_kategori' => $value['id_kategori'],
                    ];
                    $this->m_kategori->deleteKategori($data_id['id_kategori']);
                }
            }
        }
        session()->setFlashdata('success', 'Biaya Support berhasil diimport');
        return redirect()->to('support/'.$id_transaksi);
    }

    public function importbiaya($id_transaksi)
    {
        $file = $this->request->getFile('file_excel_all');
        $ext = $file->getClientExtension();

        if($ext == 'xls'){
            $render = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
        } else{
            $render = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        }
        $spreadsheet = $render->load($file);
        $sheet = $spreadsheet->getActiveSheet()->toArray();
        $worksheet = $spreadsheet->getActiveSheet();
        $highestRow = $worksheet->getHighestRow(); // e.g. 10
        $highestColumn = $worksheet->getHighestColumn(); // e.g 'F'
        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

        $a=1;
        $b=1;
        $c=1;
        $d=1;

        for($kk = 9; $kk < $highestColumnIndex; ++$kk) {
            $nopjum[$kk] = $sheet[1][$kk];
            $nopb[$kk] = $sheet[2][$kk];
            $ko_val[$kk] = $sheet[6][$kk];

            $array = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ','BA','BB');

            if(empty($nopjum[$kk]) && empty($nopb[$kk])){
                $alpha = $array[$kk];
                session()->setFlashdata('warning', ['Silahkan isi salah satu antara No PJUM atau No PB pada kolom '.$alpha.' baris ke 2 atau 3, lalu upload ulang file excel']);
                return redirect()->to('dashboard/'.$id_transaksi);
            }

            if(!empty($nopjum[$kk]) && !empty($nopb[$kk])){
                $alpha = $array[$kk];
                session()->setFlashdata('warning', ['Silahkan isi salah satu antara No PJUM atau No PB pada kolom '.$alpha.' baris ke 2 atau 3, lalu upload ulang file excel']);
                return redirect()->to('dashboard/'.$id_transaksi);
            }

            if (empty($ko_val[$kk])) {
                $alpha = $array[$kk];
                session()->setFlashdata('warning', ['Silahkan isi Valas pada kolom '.$alpha.' baris ke 7, lalu upload ulang file excel']);
                return redirect()->to('dashboard/'.$id_transaksi);
            }

            if (!empty($nopjum[$kk])) {
                $jenis_biaya[$kk] = 'PJUM';
            } else if (!empty($nopb[$kk])) {
                $jenis_biaya[$kk] = 'PB';
            }
        }

        for($r = 7; $r < $highestRow; $r++) {
            $tanggal[$r] = $sheet[$r][1];
            $kategori[$r] = $sheet[$r][2];
            $status[$r] = $sheet[$r][3];
            $ref[$r] = $sheet[$r][4];
            $note[$r] = $sheet[$r][5];
            $negara_tujuan[$r] = $sheet[$r][6];
            $negara_trading[$r] = $sheet[$r][7];
            $jumlah_personil[$r] = $sheet[$r][8];

            if(empty($tanggal[$r])){
                $bar = $r+1;
                session()->setFlashdata('warning', ['Silahkan isi tanggal pada kolom B baris ke '.$bar.', lalu upload ulang file excel']);
                return redirect()-> to('dashboard/'.$id_transaksi);
            }

            if(empty($kategori[$r])){
                $bar = $r+1;
                session()->setFlashdata('warning', ['Silahkan isi kategori pada kolom C baris ke '.$bar.', lalu upload ulang file excel']);
                return redirect()-> to('dashboard/'.$id_transaksi);
            }

            if(empty($negara_tujuan[$r]) && empty($negara_trading[$r])){
                $bar = $r+1;
                session()->setFlashdata('warning', ['Silahkan isi antara negara tujuan atau negara transit pada kolom G atau kolom H baris ke '.$bar.', lalu upload ulang file excel']);
                return redirect()-> to('dashboard/'.$id_transaksi);
            }

            if(empty($jumlah_personil[$r])){
                $bar = $r+1;
                session()->setFlashdata('warning', ['Silahkan isi jumlah personil pada kolom I baris ke '.$bar.', lalu upload ulang file excel']);
                return redirect()-> to('dashboard/'.$id_transaksi);
            }

            for($k = 9; $k < $highestColumnIndex; ++$k) {
                if(!empty($sheet[$r][$k])){
                    $kode_valas = $sheet[6][$k];
                    $kode_valas = strtoupper($kode_valas);
                    $id_valas = $this->m_valas->where('kode_valas', $kode_valas)->select('id_valas as id_valas')->first();
                    $simbol = $this->m_valas->where('kode_valas', $kode_valas)->select('simbol as simbol')->first();

                    $biaya = preg_replace("/[^0-9\.]/", "", $sheet[$r][$k]);

                    if(empty($id_valas['id_valas'])){
                        $alpha = $array[$k];
                        session()->setFlashdata('warning', ['Silahkan mengisikan valas pada kolom '.$alpha.' sesuai dengan list pada sheet Master, lalu upload ulang file excel']);
                        return redirect()-> to('dashboard/'.$id_transaksi);
                    }

                    $pum = preg_replace("/[^0-9\.]/", "", $sheet[3][$k]);
                    if (empty($pum)) {
                        $pum = 0;
                    }
                    $uang_kembali = preg_replace("/[^0-9\.]/", "", $sheet[4][$k]);
                    if (empty($uang_kembali)) {
                        $uang_kembali = 0;
                    }

                    $array = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ','BA','BB');

                    $cekdata = $this->m_kategori->cekdata($id_transaksi, $r+1, $jenis_biaya[$k]);
                    $cekdatapjum = $this->m_kategori->cekdata($id_transaksi, $r+1, 'PJUM');
                    $cekdatapb = $this->m_kategori->cekdata($id_transaksi, $r+1, 'PB');
                    $cekdata1 = $this->m_kategori->cekdata1($id_transaksi, $r+1);

                    $cekvaluta = $this->m_biaya->cekvaluta($id_transaksi, $r+1, $k+1, $jenis_biaya[$k]);
                    $cekvalutapjum = $this->m_biaya->cekvaluta($id_transaksi, $r+1, $k+1, 'PJUM');
                    $cekvalutapb = $this->m_biaya->cekvaluta($id_transaksi, $r+1, $k+1, 'PB');
                    $cekvaluta1 = $this->m_biaya->cekvaluta1($id_transaksi, $r+1, $k+1);
                   
                    $ceknomorpjum = $this->m_pjum->ceknomor($id_transaksi, $k+1);
                    $ceknomorpb = $this->m_pb->ceknomor($id_transaksi, $k+1);

                    $nik= session()->get('akun_nik');
                    $role= session()->get('akun_role');
                    $timestamp = date('Y-m-d H:i:s');

                    $submit_pjum = $this->m_id->where('id_transaksi', $id_transaksi)->select('submit_pjum, kirim_pjum')->first();
                    $submit_pb = $this->m_id->where('id_transaksi', $id_transaksi)->select('submit_pb, kirim_pb')->first();

                    if($jenis_biaya[$k] == 'PJUM') {
                        // echo '(PJUM - ';
                        if($role == 'admin' || $role == 'user') {

                            if(empty($cekdatapb['jenis_biaya'])) {
                                // echo 'PB KOSONG - ';
                            } else {
                                if ($submit_pjum['kirim_pjum'] == 1 && $cekdatapb['created_by'] != '05080' && $cekdatapb['tanggal'] == $tanggal[$r] && $cekdatapb['kategori'] == $kategori[$r] && $cekdatapb['status'] == $status[$r] && $cekdatapb['ref'] == $ref[$r] && $cekdatapb['note'] == $note[$r] && $cekdatapb['negara_tujuan'] == $negara_tujuan[$r] && $cekdatapb['negara_trading'] == $negara_trading[$r] && $cekdatapb['jumlah_personil'] == $jumlah_personil[$r]) {
                                            
                                } else {
                                    $bar = $r+1;
                                    session()->setFlashdata('warning', ['Pada baris ke '.$bar.' telah ada data yang diupload oleh Treasury, silahkan export file kembali dan tambahkan data pada baris yang kosong lalu upload ulang file excel']);
                                    return redirect()-> to('dashboard/'.$id_transaksi);
                                }
                            }

                            if(empty($ceknomorpjum)){
                                $nomor_pjum = [
                                    'id_transaksi' => $id_transaksi,
                                    'kolom' => $k+1,
                                    'nomor' => $nopjum[$k],
                                    'id_valas' => $id_valas['id_valas'],
                                    'kode_valas' => $kode_valas,
                                    'created_by' => $nik,
                                ];
                                $this->m_pjum->insert($nomor_pjum);
                            } else if($id_transaksi == $ceknomorpjum['id_transaksi'] && $k+1 == $ceknomorpjum['kolom']) {
                                $ceknompjum = $this->m_pjum->ceknopjum($id_transaksi, $k+1);
                                foreach ($ceknompjum as $key => $value) {
                                    $val_pjum = $this->m_pjum->where('id_pjum', $value['id_pjum'])->select('id_valas')->first();

                                    if($id_valas['id_valas'] != $val_pjum['id_valas']){
                                        $tanggal_pjum_edit = [
                                            'id_pjum' => $value['id_pjum'],
                                            'tanggal' => null,
                                        ];
                                        $this->m_pjum->save($tanggal_pjum_edit);
                                        $this->m_kurs->deletekurspjum($tanggal_pjum_edit['id_pjum']);
                                        $this->m_kurs->query('ALTER TABLE kurs AUTO_INCREMENT 1');
                                    }

                                    $nomor_pjum_edit = [
                                        'id_pjum' => $value['id_pjum'],
                                        'nomor' => $nopjum[$k],
                                        'id_valas' => $id_valas['id_valas'],
                                        'kode_valas' => $kode_valas,
                                        'edited_at' => $timestamp,
                                        'edited_by' => $nik,
                                    ];
                                    $this->m_pjum->save($nomor_pjum_edit);
                                }
                            }

                            $cekpum = $this->m_pum->cekpum($id_transaksi, $k+1);
                            if(empty($cekpum)) {
                                $ceknopjum = $this->m_pjum->ceknomorpjumvalas($id_transaksi, $nopjum[$k], $id_valas['id_valas']);
                                foreach ($ceknopjum as $key => $value) {
                                    $data_pum = [
                                        'id_pjum' => $value['id_pjum'],
                                        'kolom' => $k+1,
                                        'pum' => $pum,
                                        'uang_kembali' => $uang_kembali,
                                        'id_transaksi' => $id_transaksi,
                                        'id_valas' => $id_valas['id_valas'],
                                        'kode_valas' => $kode_valas,
                                        'simbol' => $simbol['simbol'],
                                        'created_by' => $nik,
                                    ];
                                    $this->m_pum->insert($data_pum);
                                }
                            } else if($id_transaksi == $cekpum['id_transaksi'] && $k+1 == $cekpum['kolom']) {
                                $resultPum = $this->m_pum->where('id_transaksi', $id_transaksi)->where('kolom', $k+1)->select('id_pum as id_pum')->first();
                                $kirim = $this->m_id->where('id_transaksi', $id_transaksi)->select('kirim_pjum')->first();
                                $ceknopjum = $this->m_pjum->ceknomorpjumvalas($id_transaksi, $nopjum[$k], $id_valas['id_valas']);
                                foreach ($ceknopjum as $key => $value) {
                                    if ($kirim['kirim_pjum'] == 0) {
                                        $data_pum_edit = [
                                            'id_pum' => $resultPum['id_pum'],
                                            'id_pjum' => $value['id_pjum'],
                                            'pum' => $pum,
                                            'uang_kembali' => $uang_kembali,
                                            'id_valas' => $id_valas['id_valas'],
                                            'kode_valas' => $kode_valas,
                                            'simbol' => $simbol['simbol'],
                                        ];
                                        $this->m_pum->distinct($data_pum_edit['id_pum']);
                                        $this->m_pum->save($data_pum_edit);
                                    } else {
                                        $data_pum_edit = [
                                            'id_pum' => $resultPum['id_pum'],
                                            'id_pjum' => $value['id_pjum'],
                                            'pum' => $pum,
                                            'uang_kembali' => $uang_kembali,
                                            'id_valas' => $id_valas['id_valas'],
                                            'kode_valas' => $kode_valas,
                                            'simbol' => $simbol['simbol'],
                                            'edited_at' => $timestamp,
                                            'edited_by' => $nik,
                                        ];
                                        $this->m_pum->distinct($data_pum_edit['id_pum']);
                                        $this->m_pum->save($data_pum_edit);
                                    }
                                }
                            }

                            if(empty($cekdata1)) {
                                // echo 'KATEGORI KOSONG - ';
                                $ceknopjum = $this->m_pjum->ceknomorpjumvalas($id_transaksi, $nopjum[$k], $id_valas['id_valas']);
                                foreach ($ceknopjum as $key => $value) {
                                    $data_kategori_pjum = [
                                        'baris' => $r+1,
                                        'id_transaksi' => $id_transaksi,
                                        'id_pjum' => $value['id_pjum'],
                                        'id_pb' => null,
                                        'jenis_biaya' => 'PJUM',
                                        'kategori' => $kategori[$r],
                                        'tanggal' => $tanggal[$r],
                                        'note' => $note[$r],
                                        'ref' => $ref[$r],
                                        'jumlah_personil' => $jumlah_personil[$r],
                                        'negara_tujuan' => $negara_tujuan[$r],
                                        'negara_trading' => $negara_trading[$r],
                                        'created_by' => $nik,
                                    ];
                                    $this->m_kategori->insert($data_kategori_pjum);
                                }
                            } else {
                                // echo 'KATEGORI ISI - ';
                                if(empty($cekdatapb['jenis_biaya'])) {
                                    // echo 'PB KOSONG - ';
                                } else {
                                    // echo 'PB ISI - ';
                                    if($id_transaksi == $cekdatapb['id_transaksi'] && $r+1 == $cekdatapb['baris'] && 'PB' == $cekdatapb['jenis_biaya']) {
                                        $resultKategori = $this->m_kategori->where('id_transaksi', $id_transaksi)->where('baris', $r+1)->where('jenis_biaya', 'PB')->select('id_kategori as id_kategori')->first();
                                        $kirim = $this->m_id->where('id_transaksi', $id_transaksi)->select('kirim_pjum')->first();
                                        $ceknopjum = $this->m_pjum->ceknomorpjumvalas($id_transaksi, $nopjum[$k], $id_valas['id_valas']);
                                        foreach ($ceknopjum as $key => $value) {
                                            if ($kirim['kirim_pjum'] == 0) {
                                                $data_kategori_pjum_edit = [
                                                    'id_kategori' => $resultKategori['id_kategori'],
                                                    'baris' => $r+1,
                                                    'id_pjum' => $value['id_pjum'],
                                                    'id_pb' => null,
                                                    'jenis_biaya' => 'PJUM',
                                                    'kategori' => $kategori[$r],
                                                    'tanggal' => $tanggal[$r],
                                                    'note' => $note[$r],
                                                    'ref' => $ref[$r],
                                                    'jumlah_personil' => $jumlah_personil[$r],
                                                    'negara_tujuan' => $negara_tujuan[$r],
                                                    'negara_trading' => $negara_trading[$r],
                                                ];
                                                $this->m_kategori->distinct($data_kategori_pjum_edit['id_kategori']);
                                                $this->m_kategori->save($data_kategori_pjum_edit);
                                            } else {
                                                $data_kategori_pjum_edit = [
                                                    'id_kategori' => $resultKategori['id_kategori'],
                                                    'baris' => $r+1,
                                                    'id_pjum' => $value['id_pjum'],
                                                    'id_pb' => null,
                                                    'jenis_biaya' => 'PJUM',
                                                    'kategori' => $kategori[$r],
                                                    'tanggal' => $tanggal[$r],
                                                    'note' => $note[$r],
                                                    'ref' => $ref[$r],
                                                    'jumlah_personil' => $jumlah_personil[$r],
                                                    'negara_tujuan' => $negara_tujuan[$r],
                                                    'negara_trading' => $negara_trading[$r],
                                                    'edited_at' => $timestamp,
                                                    'edited_by' => $nik,
                                                ];
                                                $this->m_kategori->distinct($data_kategori_pjum_edit['id_kategori']);
                                                $this->m_kategori->save($data_kategori_pjum_edit);
                                            }
                                        }
                                    }
                                }

                                if(empty($cekdatapjum['jenis_biaya'])) {
                                    // echo 'PJUM KOSONG) ';
                                } else {
                                    // echo 'PJUM ISI) ';
                                    if($id_transaksi == $cekdatapjum['id_transaksi'] && $r+1 == $cekdatapjum['baris'] && 'PJUM' == $cekdatapjum['jenis_biaya']) {
                                        $resultKategori = $this->m_kategori->where('id_transaksi', $id_transaksi)->where('baris', $r+1)->where('jenis_biaya', 'PJUM')->select('id_kategori as id_kategori')->first();
                                        $kirim = $this->m_id->where('id_transaksi', $id_transaksi)->select('kirim_pjum')->first();
                                        $ceknopjum = $this->m_pjum->ceknomorpjumvalas($id_transaksi, $nopjum[$k], $id_valas['id_valas']);
                                        foreach ($ceknopjum as $key => $value) {
                                            if ($kirim['kirim_pjum'] == 0) {
                                                $data_kategori_pjum_edit = [
                                                    'id_kategori' => $resultKategori['id_kategori'],
                                                    'baris' => $r+1,
                                                    'id_pjum' => $value['id_pjum'],
                                                    'id_pb' => null,
                                                    'jenis_biaya' => 'PJUM',
                                                    'kategori' => $kategori[$r],
                                                    'tanggal' => $tanggal[$r],
                                                    'note' => $note[$r],
                                                    'ref' => $ref[$r],
                                                    'jumlah_personil' => $jumlah_personil[$r],
                                                    'negara_tujuan' => $negara_tujuan[$r],
                                                    'negara_trading' => $negara_trading[$r],
                                                ];
                                                $this->m_kategori->distinct($data_kategori_pjum_edit['id_kategori']);
                                                $this->m_kategori->save($data_kategori_pjum_edit);
                                            } else {
                                                $data_kategori_pjum_edit = [
                                                    'id_kategori' => $resultKategori['id_kategori'],
                                                    'baris' => $r+1,
                                                    'id_pjum' => $value['id_pjum'],
                                                    'id_pb' => null,
                                                    'jenis_biaya' => 'PJUM',
                                                    'kategori' => $kategori[$r],
                                                    'tanggal' => $tanggal[$r],
                                                    'note' => $note[$r],
                                                    'ref' => $ref[$r],
                                                    'jumlah_personil' => $jumlah_personil[$r],
                                                    'negara_tujuan' => $negara_tujuan[$r],
                                                    'negara_trading' => $negara_trading[$r],
                                                    'edited_at' => $timestamp,
                                                    'edited_by' => $nik,
                                                ];
                                                $this->m_kategori->distinct($data_kategori_pjum_edit['id_kategori']);
                                                $this->m_kategori->save($data_kategori_pjum_edit);
                                            }
                                        }
                                    }
                                }
                            }

                            if(empty($cekvaluta1)) {
                                // echo 'BIAYA KOSONG - ';
                                $ceknopjum = $this->m_pjum->ceknomorpjumvalas($id_transaksi, $nopjum[$k], $id_valas['id_valas']);
                                $resultKategori = $this->m_kategori->where('id_transaksi', $id_transaksi)->where('baris', $r+1)->where('jenis_biaya', 'PJUM')->select('id_kategori as id_kategori')->first();
                                $resultPum = $this->m_pum->where('id_transaksi', $id_transaksi)->where('kolom', $k+1)->select('id_pum as id_pum')->first();
                                foreach ($ceknopjum as $key => $value) {
                                    $data_biaya_pjum = [
                                        'id_kategori' => $resultKategori['id_kategori'],
                                        'id_pum' => $resultPum['id_pum'],
                                        'id_pjum' => $value['id_pjum'],
                                        'id_pb' => null,
                                        'baris' => $r+1,
                                        'kolom' => $k+1,
                                        'kategori' => $kategori[$r],
                                        'id_transaksi' => $id_transaksi,
                                        'id_valas' => $id_valas['id_valas'],
                                        'kode_valas' => $kode_valas,
                                        'simbol' => $simbol['simbol'],
                                        'jenis_biaya' => 'PJUM',
                                        'biaya' => $biaya,
                                        'tanggal' => $tanggal[$r],
                                        'created_by' => $nik,
                                    ];
                                    $this->m_biaya->insert($data_biaya_pjum);
                                }
                            } else {
                                // echo 'BIAYA ISI - ';
                                if(empty($cekvalutapb['jenis_biaya'])) {
                                    // echo 'PB KOSONG - ';
                                } else {
                                    // echo 'PB ISI - ';
                                    // if ($submit_pjum['kirim_pjum'] == 1 && $cekvalutapb['created_by'] == '05080' && $cekvalutapb['biaya'] == $biaya) {
                                        
                                    // } else {
                                    //     $bar = $r+1;
                                    //     session()->setFlashdata('warning', ['Tidak dapat melakukan edit biaya pada baris ke '.$bar.', karena merupakan data yang diupload oleh Treasury']);
                                    //     return redirect()-> to('dashboard/'.$id_transaksi);
                                    // }

                                    // if ($submit_pjum['kirim_pjum'] == 1 && $cekvalutapb['created_by'] == '05080' && $cekvalutapb['id_valas'] == $id_valas['id_valas']) {
                                        
                                    // } else {
                                    //     $alpha = $array[$k];
                                    //     session()->setFlashdata('warning', ['Tidak dapat melakukan edit valas pada kolom '.$alpha.', karena merupakan data yang diupload oleh Treasury']);
                                    //     return redirect()-> to('dashboard/'.$id_transaksi);
                                    // }

                                    if($id_transaksi == $cekvalutapb['id_transaksi'] && $r+1 == $cekvalutapb['baris'] && $k+1 == $cekvalutapb['kolom'] && 'PB' == $cekvalutapb['jenis_biaya']) {
                                        $resultBiaya = $this->m_biaya->where('id_transaksi', $id_transaksi)->where('baris', $r+1)->where('kolom', $k+1)->where('jenis_biaya', 'PB')->select('id_biaya as id_biaya')->first();
                                        $resultPum = $this->m_pum->where('id_transaksi', $id_transaksi)->where('kolom', $k+1)->select('id_pum as id_pum')->first();
                                        $kirim = $this->m_id->where('id_transaksi', $id_transaksi)->select('kirim_pjum')->first();
                                        $ceknopjum = $this->m_pjum->ceknomorpjumvalas($id_transaksi, $nopjum[$k], $id_valas['id_valas']);
                                        foreach ($ceknopjum as $key => $value) {
                                            if ($kirim['kirim_pjum'] == 0) {    
                                                $data_biaya_pjum_edit = [
                                                    'id_biaya' => $resultBiaya['id_biaya'],
                                                    'id_pum' => $resultPum['id_pum'],
                                                    'id_pjum' => $value['id_pjum'],
                                                    'id_pb' => null,
                                                    'kategori' => $kategori[$r],
                                                    'id_valas' => $id_valas['id_valas'],
                                                    'kode_valas' => $kode_valas,
                                                    'simbol' => $simbol['simbol'],
                                                    'jenis_biaya' => 'PJUM',
                                                    'biaya' => $biaya,
                                                    'tanggal' => $tanggal[$r],
                                                ];
                                                $this->m_biaya->distinct($data_biaya_pjum_edit['id_biaya']);
                                                $this->m_biaya->save($data_biaya_pjum_edit);
                                            } else {
                                                $data_biaya_pjum_edit = [
                                                    'id_biaya' => $resultBiaya['id_biaya'],
                                                    'id_pum' => $resultPum['id_pum'],
                                                    'id_pjum' => $value['id_pjum'],
                                                    'id_pb' => null,
                                                    'kategori' => $kategori[$r],
                                                    'id_valas' => $id_valas['id_valas'],
                                                    'kode_valas' => $kode_valas,
                                                    'simbol' => $simbol['simbol'],
                                                    'jenis_biaya' => 'PJUM',
                                                    'biaya' => $biaya,
                                                    'tanggal' => $tanggal[$r],
                                                    'edited_at' => $timestamp,
                                                    'edited_by' => $nik,
                                                ];
                                                $this->m_biaya->distinct($data_biaya_pjum_edit['id_biaya']);
                                                $this->m_biaya->save($data_biaya_pjum_edit);
                                            }
                                        }
                                    }
                                }

                                if(empty($cekvalutapjum['jenis_biaya'])) {
                                    // echo 'PJUM KOSONG) ';
                                } else {
                                    // echo 'PJUM ISI) ';
                                    if($id_transaksi == $cekvalutapjum['id_transaksi'] && $r+1 == $cekvalutapjum['baris'] && $k+1 == $cekvalutapjum['kolom'] && 'PJUM' == $cekvalutapjum['jenis_biaya']) {
                                        $resultBiaya = $this->m_biaya->where('id_transaksi', $id_transaksi)->where('baris', $r+1)->where('kolom', $k+1)->where('jenis_biaya', 'PJUM')->select('id_biaya as id_biaya')->first();
                                        $resultPum = $this->m_pum->where('id_transaksi', $id_transaksi)->where('kolom', $k+1)->select('id_pum as id_pum')->first();
                                        $kirim = $this->m_id->where('id_transaksi', $id_transaksi)->select('kirim_pjum')->first();
                                        $ceknopjum = $this->m_pjum->ceknomorpjumvalas($id_transaksi, $nopjum[$k], $id_valas['id_valas']);
                                        foreach ($ceknopjum as $key => $value) {
                                            if ($kirim['kirim_pjum'] == 0) {    
                                                $data_biaya_pjum_edit = [
                                                    'id_biaya' => $resultBiaya['id_biaya'],
                                                    'id_pum' => $resultPum['id_pum'],
                                                    'id_pjum' => $value['id_pjum'],
                                                    'id_pb' => null,
                                                    'kategori' => $kategori[$r],
                                                    'id_valas' => $id_valas['id_valas'],
                                                    'kode_valas' => $kode_valas,
                                                    'simbol' => $simbol['simbol'],
                                                    'jenis_biaya' => 'PJUM',
                                                    'biaya' => $biaya,
                                                    'tanggal' => $tanggal[$r],
                                                ];
                                                $this->m_biaya->distinct($data_biaya_pjum_edit['id_biaya']);
                                                $this->m_biaya->save($data_biaya_pjum_edit);
                                            } else {
                                                $data_biaya_pjum_edit = [
                                                    'id_biaya' => $resultBiaya['id_biaya'],
                                                    'id_pum' => $resultPum['id_pum'],
                                                    'id_pjum' => $value['id_pjum'],
                                                    'id_pb' => null,
                                                    'kategori' => $kategori[$r],
                                                    'id_valas' => $id_valas['id_valas'],
                                                    'kode_valas' => $kode_valas,
                                                    'simbol' => $simbol['simbol'],
                                                    'jenis_biaya' => 'PJUM',
                                                    'biaya' => $biaya,
                                                    'tanggal' => $tanggal[$r],
                                                    'edited_at' => $timestamp,
                                                    'edited_by' => $nik,
                                                ];
                                                $this->m_biaya->distinct($data_biaya_pjum_edit['id_biaya']);
                                                $this->m_biaya->save($data_biaya_pjum_edit);
                                            }
                                        }
                                    }
                                }
                            }
                        } else {
                            $cekpjum = $this->m_kategori->cekdata2($id_transaksi, 'PJUM');

                            // if(empty($ceknomorpjum)){
                            //     if($submit_pjum['kirim_pjum'] == 1 && empty($cekpjum)){
                                    
                            //     } else if($submit_pjum['kirim_pjum'] == 1 && !empty($nopjum[$k]) && $cekpjum['created_by'] != '05080' && $cekpjum['tanggal'] == $tanggal[$r] && $cekpjum['kategori'] == $kategori[$r] && $cekpjum['status'] == $status[$r] && $cekpjum['ref'] == $ref[$r] && $cekpjum['note'] == $note[$r] && $cekpjum['negara_tujuan'] == $negara_tujuan[$r] && $cekpjum['negara_trading'] == $negara_trading[$r] && $cekpjum['jumlah_personil'] == $jumlah_personil[$r]){
                                                                        
                            //     } else if($submit_pjum['submit_pjum'] == 4 && !empty($nopjum[$k]) && $cekpjum['created_by'] != '05080' && $cekpjum['tanggal'] == $tanggal[$r] && $cekpjum['kategori'] == $kategori[$r] && $cekpjum['status'] == $status[$r] && $cekpjum['ref'] == $ref[$r] && $cekpjum['note'] == $note[$r] && $cekpjum['negara_tujuan'] == $negara_tujuan[$r] && $cekpjum['negara_trading'] == $negara_trading[$r] && $cekpjum['jumlah_personil'] == $jumlah_personil[$r]){
                                                                        
                            //     } else {
                            //         session()->setFlashdata('warning', ['Tidak dapat menambahkan data PJUM']);
                            //         return redirect()-> to('dashboard/'.$id_transaksi);
                            //     }
                            // } else if($id_transaksi == $ceknomorpjum['id_transaksi'] && $k+1 == $ceknomorpjum['kolom'] && $nopjum[$k] == $ceknomorpjum['nomor']) {
                            //     if($submit_pjum['kirim_pjum'] == 1 && empty($cekpjum)){
                                    
                            //     } else if($submit_pjum['kirim_pjum'] == 1 && !empty($nopjum[$k]) && $cekpjum['created_by'] != '05080' && $cekpjum['tanggal'] == $tanggal[$r] && $cekpjum['kategori'] == $kategori[$r] && $cekpjum['status'] == $status[$r] && $cekpjum['ref'] == $ref[$r] && $cekpjum['note'] == $note[$r] && $cekpjum['negara_tujuan'] == $negara_tujuan[$r] && $cekpjum['negara_trading'] == $negara_trading[$r] && $cekpjum['jumlah_personil'] == $jumlah_personil[$r]){
                                                                        
                            //     } else if($submit_pjum['submit_pjum'] == 4 && !empty($nopjum[$k]) && $cekpjum['created_by'] != '05080' && $cekpjum['tanggal'] == $tanggal[$r] && $cekpjum['kategori'] == $kategori[$r] && $cekpjum['status'] == $status[$r] && $cekpjum['ref'] == $ref[$r] && $cekpjum['note'] == $note[$r] && $cekpjum['negara_tujuan'] == $negara_tujuan[$r] && $cekpjum['negara_trading'] == $negara_trading[$r] && $cekpjum['jumlah_personil'] == $jumlah_personil[$r]){
                                                                        
                            //     } else {
                            //         session()->setFlashdata('warning', ['Tidak dapat menambahkan data PJUM']);
                            //         return redirect()-> to('dashboard/'.$id_transaksi);
                            //     }
                            // }

                            $ceknompjum = $this->m_pjum->ceknopjum($id_transaksi, $k+1);
                            foreach ($ceknompjum as $key => $value) {
                                $val_pjum = $this->m_pjum->where('id_pjum', $value['id_pjum'])->select('created_by, nomor')->first();

                                if($submit_pjum['kirim_pjum'] == 1 && $val_pjum['created_by'] != '05080' && $val_pjum['nomor'] == $nopjum[$k]){
                                            
                                } else if($submit_pjum['kirim_pjum'] == 1 && $val_pjum['created_by'] != '05080' && $val_pjum['nomor'] != $nopjum[$k]){
                                    $alpha = $array[$k];
                                    session()->setFlashdata('warning', ['Tidak dapat melakukan edit no PJUM pada kolom '.$alpha.', karena merupakan data yang diupload oleh User Bagian']);
                                    return redirect()-> to('dashboard/'.$id_transaksi);
                                }
                            }

                            $cekpum = $this->m_pum->cekpum($id_transaksi, $k+1);
                            if(empty($cekpum)) {
                                
                            } else {
                                if ($submit_pjum['kirim_pjum'] == 1 && $cekpum['created_by'] != '05080' && $cekpum['uang_kembali'] == $uang_kembali && $cekpum['pum'] == $pum) {

                                } else {
                                    $alpha = $array[$k];
                                    session()->setFlashdata('warning', ['Tidak dapat melakukan edit data PJUM pada kolom '.$alpha.', karena merupakan data yang diupload oleh User Bagian']);
                                    return redirect()-> to('dashboard/'.$id_transaksi);
                                }
                            }

                            if(empty($cekdatapjum['jenis_biaya'])) {
                                // echo 'PJUM KOSONG - ';
                            } else {
                                if ($submit_pjum['kirim_pjum'] == 1 && $cekdatapjum['created_by'] != '05080' && $cekdatapjum['tanggal'] == $tanggal[$r] && $cekdatapjum['kategori'] == $kategori[$r] && $cekdatapjum['status'] == $status[$r] && $cekdatapjum['ref'] == $ref[$r] && $cekdatapjum['note'] == $note[$r] && $cekdatapjum['negara_tujuan'] == $negara_tujuan[$r] && $cekdatapjum['negara_trading'] == $negara_trading[$r] && $cekdatapjum['jumlah_personil'] == $jumlah_personil[$r]) {
                                            
                                } else {
                                    $bar = $r+1;
                                    session()->setFlashdata('warning', ['Pada baris ke '.$bar.' telah ada data yang diupload oleh User Bagian, silahkan export file kembali dan tambahkan data pada baris yang kosong lalu upload ulang file excel']);
                                    return redirect()-> to('dashboard/'.$id_transaksi);
                                }
                            }

                            if(empty($cekvalutapjum['jenis_biaya'])) {
                                // echo 'PJUM KOSONG - ';
                            } else {
                                // echo 'PJUM ISI - ';
                                if ($submit_pjum['kirim_pjum'] == 1 && $cekvalutapjum['created_by'] != '05080' && $cekvalutapjum['biaya'] == $biaya) {
                                    
                                } else {
                                    $bar = $r+1;
                                    session()->setFlashdata('warning', ['Tidak dapat melakukan edit biaya pada baris ke '.$bar.', karena merupakan data yang diupload oleh User Bagian']);
                                    return redirect()-> to('dashboard/'.$id_transaksi);
                                }

                                if ($submit_pjum['kirim_pjum'] == 1 && $cekvalutapjum['created_by'] != '05080' && $cekvalutapjum['id_valas'] == $id_valas['id_valas']) {
                                    
                                } else {
                                    $alpha = $array[$k];
                                    session()->setFlashdata('warning', ['Tidak dapat melakukan edit valas pada kolom '.$alpha.', karena merupakan data yang diupload oleh User Bagian']);
                                    return redirect()-> to('dashboard/'.$id_transaksi);
                                }
                            }
                        }
                    } else {
                        // Biaya PB
                        // echo '(PB - ';
                        $bar = $r+1;
                        if(empty($status[$r])){
                            session()->setFlashdata('warning', ['Silahkan isi apakah Dibelikan GS atau Beli Sendiri pada kolom D baris ke '.$bar.', lalu upload ulang file excel']);
                            return redirect()-> to('dashboard/'.$id_transaksi);
                        }

                        if($kategori[$r] != 'Tiket Pesawat' && $kategori[$r] != 'Bagasi Pesawat' && $kategori[$r] != 'Porter Pesawat' && $kategori[$r] != 'Hotel' && $kategori[$r] != 'Makan dan Minum' && $kategori[$r] != 'Transportasi' && $kategori[$r] != 'Laundry' && $kategori[$r] != 'Lain-lain' && $kategori[$r] != 'Tukar Uang Keluar' && $kategori[$r] != 'Tukar Uang Masuk' && $kategori[$r] != 'Kembalian') {
                            session()->setFlashdata('warning', ['Silahkan memilih kategori pada kolom C baris ke '.$bar.' sesuai dengan list pada sheet Master, lalu upload ulang file excel']);
                            return redirect()-> to('dashboard/'.$id_transaksi);
                        } else if($status[$r] != 'Dibelikan GS' && $status[$r] != 'Beli Sendiri') {
                            session()->setFlashdata('warning', ['Silahkan isi status apakah Dibelikan GS atau Beli Sendiri pada kolom D baris ke '.$bar.', lalu upload ulang file excel']);
                            return redirect()-> to('dashboard/'.$id_transaksi);
                        } else {
                            
                        }

                        if($role == 'admin' || $role == 'user') {
                            if(empty($cekdatapb['jenis_biaya'])) {
                                // 'PB KOSONG) ';
                            } else {
                                // 'PB ISI) ';                                
                                // if ($submit_pjum['kirim_pjum'] == 1 && $cekdatapb['created_by'] == '05080' && $cekdatapb['tanggal'] == $tanggal[$r] && $cekdatapb['kategori'] == $kategori[$r] && $cekdatapb['status'] == $status[$r] && $cekdatapb['ref'] == $ref[$r] && $cekdatapb['note'] == $note[$r] && $cekdatapb['negara_tujuan'] == $negara_tujuan[$r] && $cekdatapb['negara_trading'] == $negara_trading[$r] && $cekdatapb['jumlah_personil'] == $jumlah_personil[$r]) {
                                   
                                // } else if ($submit_pjum['kirim_pjum'] == 0) {

                                // } else {
                                //     $bar = $r+1;
                                //     session()->setFlashdata('warning', ['Tidak dapat melakukan edit data pada baris ke '.$bar.', karena merupakan data yang diupload oleh Treasury']);
                                //     return redirect()-> to('dashboard/'.$id_transaksi);
                                // }
                            }

                            $cekpb = $this->m_kategori->cekdata2($id_transaksi, 'PB');

                            if(empty($ceknomorpb)){
                                if($submit_pjum['kirim_pjum'] == 1 && empty($cekpb)){

                                } else {
                                    $nomor_pb = [
                                        'id_transaksi' => $id_transaksi,
                                        'kolom' => $k+1,
                                        'nomor' => $nopb[$k],
                                        'id_valas' => $id_valas['id_valas'],
                                        'kode_valas' => $kode_valas,
                                        'created_by' => $nik,
                                    ];
                                    $this->m_pb->insert($nomor_pb);
                                }
                                // } else if($submit_pjum['kirim_pjum'] == 1 && $cekpb['created_by'] == '05080'){
                                //     // session()->setFlashdata('warning', ['Silahkan hubungi Treasury untuk menambahkan data PB']);
                                //     // return redirect()-> to('dashboard/'.$id_transaksi);
                                // }
                            } else if($id_transaksi == $ceknomorpb['id_transaksi'] && $k+1 == $ceknomorpb['kolom']) {
                                $ceknompb = $this->m_pb->ceknopb($id_transaksi, $k+1);
                                foreach ($ceknompb as $key => $value) {
                                    $val_pb = $this->m_pb->where('id_pb', $value['id_pb'])->select('id_valas, created_by, nomor')->first();

                                    if($id_valas['id_valas'] != $val_pb['id_valas']){
                                        $tanggal_pb_edit = [
                                            'id_pb' => $value['id_pb'],
                                            'tanggal' => null,
                                        ];
                                        $this->m_pb->save($tanggal_pb_edit);
                                        $this->m_kurs->deletekurspb($tanggal_pb_edit['id_pb']);
                                        $this->m_kurs->query('ALTER TABLE kurs AUTO_INCREMENT 1');
                                    }

                                    
                                    if($submit_pjum['kirim_pjum'] == 1 && $val_pb['created_by'] == '05080' && $val_pb['nomor'] == $nopb[$k]){
                                        
                                    } else {
                                        $nomor_pb_edit = [
                                            'id_pb' => $value['id_pb'],
                                            'nomor' => $nopb[$k],
                                            'id_valas' => $id_valas['id_valas'],
                                            'kode_valas' => $kode_valas,
                                            'edited_at' => $timestamp,
                                            'edited_by' => $nik,
                                        ];
                                        $this->m_pb->save($nomor_pb_edit);
                                    }
                                    // } else if($submit_pjum['kirim_pjum'] == 1 && $val_pb['created_by'] == '05080' && $val_pb['nomor'] != $nopb[$k]){
                                    //     $alpha = $array[$k];
                                    //     // session()->setFlashdata('warning', ['Tidak dapat melakukan edit no PB pada kolom '.$alpha.', karena merupakan data yang diupload oleh Treasury']);
                                    //     // return redirect()-> to('dashboard/'.$id_transaksi);
                                    // }
                                }
                            }

                            if(empty($cekdata1)) {
                                // echo 'KATEGORI KOSONG - ';
                                $ceknopb = $this->m_pb->ceknomorpbvalas($id_transaksi, $nopb[$k], $id_valas['id_valas']);
                                foreach ($ceknopb as $key => $value) {
                                    $data_kategori_pb = [
                                        'baris' => $r+1,
                                        'id_transaksi' => $id_transaksi,
                                        'id_pjum' => null,
                                        'id_pb' => $value['id_pb'],
                                        'jenis_biaya' => 'PB',
                                        'kategori' => $kategori[$r],
                                        'status' => $status[$r],
                                        'tanggal' => $tanggal[$r],
                                        'note' => $note[$r],
                                        'ref' => $ref[$r],
                                        'jumlah_personil' => $jumlah_personil[$r],
                                        'negara_tujuan' => $negara_tujuan[$r],
                                        'negara_trading' => $negara_trading[$r],
                                        'created_by' => $nik,
                                    ];
                                    $this->m_kategori->insert($data_kategori_pb);
                                }
                            } else {
                                // echo 'KATEGORI ISI - ';
                                if(empty($cekdatapjum['jenis_biaya'])) {
                                    // echo 'PJUM KOSONG - ';
                                } else {
                                    // echo 'PJUM ISI - ';
                                    if($id_transaksi == $cekdatapjum['id_transaksi'] && $r+1 == $cekdatapjum['baris'] && 'PJUM' == $cekdatapjum['jenis_biaya']) {
                                        $resultKategori = $this->m_kategori->where('id_transaksi', $id_transaksi)->where('baris', $r+1)->where('jenis_biaya', 'PJUM')->select('id_kategori as id_kategori')->first();
                                        $kirim = $this->m_id->where('id_transaksi', $id_transaksi)->select('kirim_pb')->first();
                                        $ceknopb = $this->m_pb->ceknomorpbvalas($id_transaksi, $nopb[$k], $id_valas['id_valas']);
                                        foreach ($ceknopb as $key => $value) {
                                            $val_pb = $this->m_pb->where('id_pb', $value['id_pb'])->select('created_by')->first();
                                            if($val_pb['created_by'] == '05080'){
                                                continue;
                                            } else {
                                                if ($kirim['kirim_pb'] == 0) {
                                                    $data_kategori_pb_edit = [
                                                        'id_kategori' => $resultKategori['id_kategori'],
                                                        'baris' => $r+1,
                                                        'id_pjum' => null,
                                                        'id_pb' => $value['id_pb'],
                                                        'jenis_biaya' => 'PB',
                                                        'kategori' => $kategori[$r],
                                                        'status' => $status[$r],
                                                        'tanggal' => $tanggal[$r],
                                                        'note' => $note[$r],
                                                        'ref' => $ref[$r],
                                                        'jumlah_personil' => $jumlah_personil[$r],
                                                        'negara_tujuan' => $negara_tujuan[$r],
                                                        'negara_trading' => $negara_trading[$r],
                                                    ];
                                                    $this->m_kategori->distinct($data_kategori_pb_edit['id_kategori']);
                                                    $this->m_kategori->save($data_kategori_pb_edit);
                                                } else {
                                                    $data_kategori_pb_edit = [
                                                        'id_kategori' => $resultKategori['id_kategori'],
                                                        'baris' => $r+1,
                                                        'id_pjum' => null,
                                                        'id_pb' => $value['id_pb'],
                                                        'jenis_biaya' => 'PB',
                                                        'kategori' => $kategori[$r],
                                                        'status' => $status[$r],
                                                        'tanggal' => $tanggal[$r],
                                                        'note' => $note[$r],
                                                        'ref' => $ref[$r],
                                                        'jumlah_personil' => $jumlah_personil[$r],
                                                        'negara_tujuan' => $negara_tujuan[$r],
                                                        'negara_trading' => $negara_trading[$r],
                                                        'edited_at' => $timestamp,
                                                        'edited_by' => $nik,
                                                    ];
                                                    $this->m_kategori->distinct($data_kategori_pb_edit['id_kategori']);
                                                    $this->m_kategori->save($data_kategori_pb_edit);
                                                }
                                            }
                                        }
                                    }
                                }

                                if(empty($cekdatapb['jenis_biaya'])) {
                                    // 'PB KOSONG) ';
                                } else {
                                    // 'PB ISI) ';
                                    if($id_transaksi == $cekdatapb['id_transaksi'] && $r+1 == $cekdatapb['baris'] && 'PB' == $cekdatapb['jenis_biaya']) {
                                        $resultKategori = $this->m_kategori->where('id_transaksi', $id_transaksi)->where('baris', $r+1)->where('jenis_biaya', 'PB')->select('id_kategori as id_kategori')->first();
                                        $kirim = $this->m_id->where('id_transaksi', $id_transaksi)->select('kirim_pb')->first();
                                        $ceknopb = $this->m_pb->ceknomorpbvalas($id_transaksi, $nopb[$k], $id_valas['id_valas']);
                                        foreach ($ceknopb as $key => $value) {
                                            $val_pb = $this->m_pb->where('id_pb', $value['id_pb'])->select('created_by')->first();
                                            if($val_pb['created_by'] == '05080'){
                                                continue;
                                            } else {
                                                if ($kirim['kirim_pb'] == 0) {
                                                    $data_kategori_pb_edit = [
                                                        'id_kategori' => $resultKategori['id_kategori'],
                                                        'baris' => $r+1,
                                                        'id_pjum' => null,
                                                        'id_pb' => $value['id_pb'],
                                                        'jenis_biaya' => 'PB',
                                                        'kategori' => $kategori[$r],
                                                        'status' => $status[$r],
                                                        'tanggal' => $tanggal[$r],
                                                        'note' => $note[$r],
                                                        'ref' => $ref[$r],
                                                        'jumlah_personil' => $jumlah_personil[$r],
                                                        'negara_tujuan' => $negara_tujuan[$r],
                                                        'negara_trading' => $negara_trading[$r],
                                                    ];
                                                    $this->m_kategori->distinct($data_kategori_pb_edit['id_kategori']);
                                                    $this->m_kategori->save($data_kategori_pb_edit);
                                                } else {
                                                    $data_kategori_pb_edit = [
                                                        'id_kategori' => $resultKategori['id_kategori'],
                                                        'baris' => $r+1,
                                                        'id_pjum' => null,
                                                        'id_pb' => $value['id_pb'],
                                                        'jenis_biaya' => 'PB',
                                                        'kategori' => $kategori[$r],
                                                        'status' => $status[$r],
                                                        'tanggal' => $tanggal[$r],
                                                        'note' => $note[$r],
                                                        'ref' => $ref[$r],
                                                        'jumlah_personil' => $jumlah_personil[$r],
                                                        'negara_tujuan' => $negara_tujuan[$r],
                                                        'negara_trading' => $negara_trading[$r],
                                                        'edited_at' => $timestamp,
                                                        'edited_by' => $nik,
                                                    ];
                                                    $this->m_kategori->distinct($data_kategori_pb_edit['id_kategori']);
                                                    $this->m_kategori->save($data_kategori_pb_edit);
                                                }
                                            }
                                        }
                                    }
                                }
                            }

                            if(empty($cekvaluta1)) {
                                // echo 'BIAYA KOSONG - ';
                                $ceknopb = $this->m_pb->ceknomorpbvalas($id_transaksi, $nopb[$k], $id_valas['id_valas']);
                                $resultKategori = $this->m_kategori->where('id_transaksi', $id_transaksi)->where('baris', $r+1)->where('jenis_biaya', 'PB')->select('id_kategori as id_kategori')->first();
                                foreach ($ceknopb as $key => $value) {
                                    $data_biaya_pb = [
                                        'id_kategori' => $resultKategori['id_kategori'],
                                        'id_pum' => null,
                                        'id_pjum' => null,
                                        'id_pb' => $value['id_pb'],
                                        'baris' => $r+1,
                                        'kolom' => $k+1,
                                        'kategori' => $kategori[$r],
                                        'id_transaksi' => $id_transaksi,
                                        'id_valas' => $id_valas['id_valas'],
                                        'kode_valas' => $kode_valas,
                                        'simbol' => $simbol['simbol'],
                                        'jenis_biaya' => 'PB',
                                        'biaya' => $biaya,
                                        'tanggal' => $tanggal[$r],
                                        'created_by' => $nik,
                                    ];
                                    $this->m_biaya->insert($data_biaya_pb);
                                }
                            } else {
                                // echo 'BIAYA ISI - ';
                                if(empty($cekvalutapjum['jenis_biaya'])) {
                                    // echo 'PJUM KOSONG - ';
                                } else {
                                    // echo 'PJUM ISI - ';
                                    if($id_transaksi == $cekvalutapjum['id_transaksi'] && $r+1 == $cekvalutapjum['baris'] && $k+1 == $cekvalutapjum['kolom'] && 'PJUM' == $cekvalutapjum['jenis_biaya']) {
                                        $resultBiaya = $this->m_biaya->where('id_transaksi', $id_transaksi)->where('baris', $r+1)->where('kolom', $k+1)->where('jenis_biaya', 'PJUM')->select('id_biaya as id_biaya')->first();
                                        $kirim = $this->m_id->where('id_transaksi', $id_transaksi)->select('kirim_pb')->first();
                                        $ceknopb = $this->m_pb->ceknomorpbvalas($id_transaksi, $nopb[$k], $id_valas['id_valas']);
                                        foreach ($ceknopb as $key => $value) {
                                            $val_pb = $this->m_pb->where('id_pb', $value['id_pb'])->select('created_by')->first();
                                            if($val_pb['created_by'] == '05080'){
                                                continue;
                                            } else {
                                                if ($kirim['kirim_pb'] == 0) {
                                                    $data_biaya_pb_edit = [
                                                        'id_biaya' => $resultBiaya['id_biaya'],
                                                        'id_pum' => null,
                                                        'id_pjum' => null,
                                                        'id_pb' => $value['id_pb'],
                                                        'kategori' => $kategori[$r],
                                                        'id_valas' => $id_valas['id_valas'],
                                                        'kode_valas' => $kode_valas,
                                                        'simbol' => $simbol['simbol'],
                                                        'jenis_biaya' => 'PB',
                                                        'biaya' => $biaya,
                                                        'tanggal' => $tanggal[$r],
                                                    ];
                                                    $this->m_biaya->distinct($data_biaya_pb_edit['id_biaya']);
                                                    $this->m_biaya->save($data_biaya_pb_edit);
                                                } else {
                                                    $data_biaya_pb_edit = [
                                                        'id_biaya' => $resultBiaya['id_biaya'],
                                                        'id_pum' => null,
                                                        'id_pjum' => null,
                                                        'id_pb' => $value['id_pb'],
                                                        'kategori' => $kategori[$r],
                                                        'id_valas' => $id_valas['id_valas'],
                                                        'kode_valas' => $kode_valas,
                                                        'simbol' => $simbol['simbol'],
                                                        'jenis_biaya' => 'PB',
                                                        'biaya' => $biaya,
                                                        'tanggal' => $tanggal[$r],
                                                        'edited_at' => $timestamp,
                                                        'edited_by' => $nik,
                                                    ];
                                                    $this->m_biaya->distinct($data_biaya_pb_edit['id_biaya']);
                                                    $this->m_biaya->save($data_biaya_pb_edit);
                                                }
                                            }
                                        }
                                    }
                                }

                                if(empty($cekvalutapb['jenis_biaya'])) {
                                    // echo 'PB KOSONG) ';
                                } else {
                                    // echo 'PB ISI) ';
                                    // if ($submit_pjum['kirim_pjum'] == 1 && $cekvalutapb['created_by'] == '05080' && $cekvalutapb['biaya'] == $biaya) {

                                    // } else if ($submit_pjum['kirim_pjum'] == 0) {
                                        
                                    // } else {
                                    //     $bar = $r+1;
                                    //     session()->setFlashdata('warning', ['Tidak dapat melakukan edit biaya pada baris ke '.$bar.', karena merupakan data yang diupload oleh Treasury']);
                                    //     return redirect()-> to('dashboard/'.$id_transaksi);
                                    // }

                                    // if ($submit_pjum['kirim_pjum'] == 1 && $cekvalutapb['created_by'] == '05080' && $cekvalutapb['id_valas'] == $id_valas['id_valas']) {
                                        
                                    // } else if ($submit_pjum['kirim_pjum'] == 0) {

                                    // } else {
                                    //     $alpha = $array[$k];
                                    //     session()->setFlashdata('warning', ['Tidak dapat melakukan edit valas pada kolom '.$alpha.', karena merupakan data yang diupload oleh Treasury']);
                                    //     return redirect()-> to('dashboard/'.$id_transaksi);
                                    // }

                                    if($id_transaksi == $cekvalutapb['id_transaksi'] && $r+1 == $cekvalutapb['baris'] && $k+1 == $cekvalutapb['kolom'] && 'PB' == $cekvalutapb['jenis_biaya']) {
                                        $resultBiaya = $this->m_biaya->where('id_transaksi', $id_transaksi)->where('baris', $r+1)->where('kolom', $k+1)->where('jenis_biaya', 'PB')->select('id_biaya as id_biaya')->first();
                                        $kirim = $this->m_id->where('id_transaksi', $id_transaksi)->select('kirim_pb')->first();
                                        $ceknopb = $this->m_pb->ceknomorpbvalas($id_transaksi, $nopb[$k], $id_valas['id_valas']);
                                        foreach ($ceknopb as $key => $value) {
                                            $val_pb = $this->m_pb->where('id_pb', $value['id_pb'])->select('created_by')->first();
                                            if($val_pb['created_by'] == '05080'){
                                                continue;
                                            } else {
                                                if ($kirim['kirim_pb'] == 0) {
                                                    $data_biaya_pb_edit = [
                                                        'id_biaya' => $resultBiaya['id_biaya'],
                                                        'id_pum' => null,
                                                        'id_pjum' => null,
                                                        'id_pb' => $value['id_pb'],
                                                        'kategori' => $kategori[$r],
                                                        'id_valas' => $id_valas['id_valas'],
                                                        'kode_valas' => $kode_valas,
                                                        'simbol' => $simbol['simbol'],
                                                        'jenis_biaya' => 'PB',
                                                        'biaya' => $biaya,
                                                        'tanggal' => $tanggal[$r],
                                                    ];
                                                    $this->m_biaya->distinct($data_biaya_pb_edit['id_biaya']);
                                                    $this->m_biaya->save($data_biaya_pb_edit);
                                                } else {
                                                    $data_biaya_pb_edit = [
                                                        'id_biaya' => $resultBiaya['id_biaya'],
                                                        'id_pum' => null,
                                                        'id_pjum' => null,
                                                        'id_pb' => $value['id_pb'],
                                                        'kategori' => $kategori[$r],
                                                        'id_valas' => $id_valas['id_valas'],
                                                        'kode_valas' => $kode_valas,
                                                        'simbol' => $simbol['simbol'],
                                                        'jenis_biaya' => 'PB',
                                                        'biaya' => $biaya,
                                                        'tanggal' => $tanggal[$r],
                                                        'edited_at' => $timestamp,
                                                        'edited_by' => $nik,
                                                    ];
                                                    $this->m_biaya->distinct($data_biaya_pb_edit['id_biaya']);
                                                    $this->m_biaya->save($data_biaya_pb_edit);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        } else if($role == 'treasury') {
                            if(empty($cekdatapjum['jenis_biaya'])) {
                                // echo 'PJUM KOSONG - ';
                            } else {
                                if ($submit_pjum['kirim_pjum'] == 1 && $cekdatapjum['created_by'] != '05080' && $cekdatapjum['tanggal'] == $tanggal[$r] && $cekdatapjum['kategori'] == $kategori[$r] && $cekdatapjum['status'] == $status[$r] && $cekdatapjum['ref'] == $ref[$r] && $cekdatapjum['note'] == $note[$r] && $cekdatapjum['negara_tujuan'] == $negara_tujuan[$r] && $cekdatapjum['negara_trading'] == $negara_trading[$r] && $cekdatapjum['jumlah_personil'] == $jumlah_personil[$r]) {
                                            
                                } else {
                                    $bar = $r+1;
                                    session()->setFlashdata('warning', ['Pada baris ke '.$bar.' telah ada data yang diupload oleh User Bagian, silahkan export file kembali dan tambahkan data pada baris yang kosong lalu upload ulang file excel']);
                                    return redirect()-> to('dashboard/'.$id_transaksi);
                                }
                            }

                            if(empty($ceknomorpb)){
                                $nomor_pb = [
                                    'id_transaksi' => $id_transaksi,
                                    'kolom' => $k+1,
                                    'nomor' => $nopb[$k],
                                    'id_valas' => $id_valas['id_valas'],
                                    'kode_valas' => $kode_valas,
                                    'created_by' => '05080',
                                ];
                                $this->m_pb->insert($nomor_pb);

                                // $data = [
                                //     'id_transaksi' => $id_transaksi,
                                //     'kirim_pb' => 1,
                                // ];
                                // $this->m_id->save($data);
                            } else if($id_transaksi == $ceknomorpb['id_transaksi'] && $k+1 == $ceknomorpb['kolom']) {
                                $ceknompb = $this->m_pb->ceknopb($id_transaksi, $k+1);
                                foreach ($ceknompb as $key => $value) {
                                    $val_pb = $this->m_pb->where('id_pb', $value['id_pb'])->select('id_valas')->first();

                                    if($id_valas['id_valas'] != $val_pb['id_valas']){
                                        $tanggal_pb_edit = [
                                            'id_pb' => $value['id_pb'],
                                            'tanggal' => null,
                                        ];
                                        $this->m_pb->save($tanggal_pb_edit);
                                        $this->m_kurs->deletekurspb($tanggal_pb_edit['id_pb']);
                                        $this->m_kurs->query('ALTER TABLE kurs AUTO_INCREMENT 1');
                                    }
                                    
                                    $nomor_pb_edit = [
                                        'id_pb' => $value['id_pb'],
                                        'nomor' => $nopb[$k],
                                        'id_valas' => $id_valas['id_valas'],
                                        'kode_valas' => $kode_valas,
                                        'edited_at' => $timestamp,
                                        // 'edited_by' => '05080',
                                    ];
                                    $this->m_pb->save($nomor_pb_edit);
                                }
                            }

                            if(empty($cekdata1)) {
                                // echo 'KATEGORI KOSONG - ';
                                $ceknopb = $this->m_pb->ceknomorpbvalas($id_transaksi, $nopb[$k], $id_valas['id_valas']);
                                foreach ($ceknopb as $key => $value) {
                                    $data_kategori_pb = [
                                        'baris' => $r+1,
                                        'id_transaksi' => $id_transaksi,
                                        'id_pjum' => null,
                                        'id_pb' => $value['id_pb'],
                                        'jenis_biaya' => 'PB',
                                        'kategori' => $kategori[$r],
                                        'status' => $status[$r],
                                        'tanggal' => $tanggal[$r],
                                        'note' => $note[$r],
                                        'ref' => $ref[$r],
                                        'jumlah_personil' => $jumlah_personil[$r],
                                        'negara_tujuan' => $negara_tujuan[$r],
                                        'negara_trading' => $negara_trading[$r],
                                        'created_by' => '05080',
                                    ];
                                    $this->m_kategori->insert($data_kategori_pb);
                                }
                            } else {
                                // echo 'KATEGORI ISI - ';
                                if(empty($cekdatapjum['jenis_biaya'])) {
                                    // echo 'PJUM KOSONG - ';
                                } else {
                                    // echo 'PJUM ISI - ';
                                    if($id_transaksi == $cekdatapjum['id_transaksi'] && $r+1 == $cekdatapjum['baris'] && 'PJUM' == $cekdatapjum['jenis_biaya']) {
                                        $resultKategori = $this->m_kategori->where('id_transaksi', $id_transaksi)->where('baris', $r+1)->where('jenis_biaya', 'PJUM')->select('id_kategori as id_kategori')->first();
                                        $kirim = $this->m_id->where('id_transaksi', $id_transaksi)->select('kirim_pb')->first();
                                        $ceknopb = $this->m_pb->ceknomorpbvalas($id_transaksi, $nopb[$k], $id_valas['id_valas']);
                                        foreach ($ceknopb as $key => $value) {
                                            if ($kirim['kirim_pb'] == 0) {
                                                $data_kategori_pb_edit = [
                                                    'id_kategori' => $resultKategori['id_kategori'],
                                                    'baris' => $r+1,
                                                    'id_pjum' => null,
                                                    'id_pb' => $value['id_pb'],
                                                    'jenis_biaya' => 'PB',
                                                    'kategori' => $kategori[$r],
                                                    'status' => $status[$r],
                                                    'tanggal' => $tanggal[$r],
                                                    'note' => $note[$r],
                                                    'ref' => $ref[$r],
                                                    'jumlah_personil' => $jumlah_personil[$r],
                                                    'negara_tujuan' => $negara_tujuan[$r],
                                                    'negara_trading' => $negara_trading[$r],
                                                ];
                                                $this->m_kategori->distinct($data_kategori_pb_edit['id_kategori']);
                                                $this->m_kategori->save($data_kategori_pb_edit);
                                            } else {
                                                $data_kategori_pb_edit = [
                                                    'id_kategori' => $resultKategori['id_kategori'],
                                                    'baris' => $r+1,
                                                    'id_pjum' => null,
                                                    'id_pb' => $value['id_pb'],
                                                    'jenis_biaya' => 'PB',
                                                    'kategori' => $kategori[$r],
                                                    'status' => $status[$r],
                                                    'tanggal' => $tanggal[$r],
                                                    'note' => $note[$r],
                                                    'ref' => $ref[$r],
                                                    'jumlah_personil' => $jumlah_personil[$r],
                                                    'negara_tujuan' => $negara_tujuan[$r],
                                                    'negara_trading' => $negara_trading[$r],
                                                    'edited_at' => $timestamp,
                                                    // 'edited_by' => '05080',
                                                ];
                                                $this->m_kategori->distinct($data_kategori_pb_edit['id_kategori']);
                                                $this->m_kategori->save($data_kategori_pb_edit);
                                            }
                                        }
                                    }
                                }

                                if(empty($cekdatapb['jenis_biaya'])) {
                                    // 'PB KOSONG) ';
                                } else {
                                    // 'PB ISI) ';
                                    if($id_transaksi == $cekdatapb['id_transaksi'] && $r+1 == $cekdatapb['baris'] && 'PB' == $cekdatapb['jenis_biaya']) {
                                        $resultKategori = $this->m_kategori->where('id_transaksi', $id_transaksi)->where('baris', $r+1)->where('jenis_biaya', 'PB')->select('id_kategori as id_kategori')->first();
                                        $kirim = $this->m_id->where('id_transaksi', $id_transaksi)->select('kirim_pb')->first();
                                        $ceknopb = $this->m_pb->ceknomorpbvalas($id_transaksi, $nopb[$k], $id_valas['id_valas']);
                                        foreach ($ceknopb as $key => $value) {
                                            if ($kirim['kirim_pb'] == 0) {
                                                $data_kategori_pb_edit = [
                                                    'id_kategori' => $resultKategori['id_kategori'],
                                                    'baris' => $r+1,
                                                    'id_pjum' => null,
                                                    'id_pb' => $value['id_pb'],
                                                    'jenis_biaya' => 'PB',
                                                    'kategori' => $kategori[$r],
                                                    'status' => $status[$r],
                                                    'tanggal' => $tanggal[$r],
                                                    'note' => $note[$r],
                                                    'ref' => $ref[$r],
                                                    'jumlah_personil' => $jumlah_personil[$r],
                                                    'negara_tujuan' => $negara_tujuan[$r],
                                                    'negara_trading' => $negara_trading[$r],
                                                ];
                                                $this->m_kategori->distinct($data_kategori_pb_edit['id_kategori']);
                                                $this->m_kategori->save($data_kategori_pb_edit);
                                            } else {
                                                $data_kategori_pb_edit = [
                                                    'id_kategori' => $resultKategori['id_kategori'],
                                                    'baris' => $r+1,
                                                    'id_pjum' => null,
                                                    'id_pb' => $value['id_pb'],
                                                    'jenis_biaya' => 'PB',
                                                    'kategori' => $kategori[$r],
                                                    'status' => $status[$r],
                                                    'tanggal' => $tanggal[$r],
                                                    'note' => $note[$r],
                                                    'ref' => $ref[$r],
                                                    'jumlah_personil' => $jumlah_personil[$r],
                                                    'negara_tujuan' => $negara_tujuan[$r],
                                                    'negara_trading' => $negara_trading[$r],
                                                    'edited_at' => $timestamp,
                                                    // 'edited_by' => '05080',
                                                ];
                                                $this->m_kategori->distinct($data_kategori_pb_edit['id_kategori']);
                                                $this->m_kategori->save($data_kategori_pb_edit);
                                            }
                                        }
                                    }
                                }
                            }

                            if(empty($cekvaluta1)) {
                                // echo 'BIAYA KOSONG - ';
                                $ceknopb = $this->m_pb->ceknomorpbvalas($id_transaksi, $nopb[$k], $id_valas['id_valas']);
                                $resultKategori = $this->m_kategori->where('id_transaksi', $id_transaksi)->where('baris', $r+1)->where('jenis_biaya', 'PB')->select('id_kategori as id_kategori')->first();
                                foreach ($ceknopb as $key => $value) {
                                    $data_biaya_pb = [
                                        'id_kategori' => $resultKategori['id_kategori'],
                                        'id_pum' => null,
                                        'id_pjum' => null,
                                        'id_pb' => $value['id_pb'],
                                        'baris' => $r+1,
                                        'kolom' => $k+1,
                                        'kategori' => $kategori[$r],
                                        'id_transaksi' => $id_transaksi,
                                        'id_valas' => $id_valas['id_valas'],
                                        'kode_valas' => $kode_valas,
                                        'simbol' => $simbol['simbol'],
                                        'jenis_biaya' => 'PB',
                                        'biaya' => $biaya,
                                        'tanggal' => $tanggal[$r],
                                        'created_by' => '05080',
                                    ];
                                    $this->m_biaya->insert($data_biaya_pb);
                                }
                            } else {
                                // echo 'BIAYA ISI - ';
                                if(empty($cekvalutapjum['jenis_biaya'])) {
                                    // echo 'PJUM KOSONG - ';
                                } else {
                                    // echo 'PJUM ISI - ';
                                    if ($submit_pjum['kirim_pjum'] == 1 && $cekvalutapjum['created_by'] != '05080' && $cekvalutapjum['biaya'] == $biaya) {
                                        
                                    } else {
                                        $bar = $r+1;
                                        session()->setFlashdata('warning', ['Tidak dapat melakukan edit biaya pada baris ke '.$bar.', karena merupakan data yang diupload oleh User Bagian']);
                                        return redirect()-> to('dashboard/'.$id_transaksi);
                                    }

                                    if ($submit_pjum['kirim_pjum'] == 1 && $cekvalutapjum['created_by'] != '05080' && $cekvalutapjum['id_valas'] == $id_valas['id_valas']) {
                                        
                                    } else {
                                        $alpha = $array[$k];
                                        session()->setFlashdata('warning', ['Tidak dapat melakukan edit valas pada kolom '.$alpha.', karena merupakan data yang diupload oleh User Bagian']);
                                        return redirect()-> to('dashboard/'.$id_transaksi);
                                    }

                                    if($id_transaksi == $cekvalutapjum['id_transaksi'] && $r+1 == $cekvalutapjum['baris'] && $k+1 == $cekvalutapjum['kolom'] && 'PJUM' == $cekvalutapjum['jenis_biaya']) {
                                        $resultBiaya = $this->m_biaya->where('id_transaksi', $id_transaksi)->where('baris', $r+1)->where('kolom', $k+1)->where('jenis_biaya', 'PJUM')->select('id_biaya as id_biaya')->first();
                                        $kirim = $this->m_id->where('id_transaksi', $id_transaksi)->select('kirim_pb')->first();
                                        $ceknopb = $this->m_pb->ceknomorpbvalas($id_transaksi, $nopb[$k], $id_valas['id_valas']);
                                        foreach ($ceknopb as $key => $value) {
                                            if ($kirim['kirim_pb'] == 0) {
                                                $data_biaya_pb_edit = [
                                                    'id_biaya' => $resultBiaya['id_biaya'],
                                                    'id_pum' => null,
                                                    'id_pjum' => null,
                                                    'id_pb' => $value['id_pb'],
                                                    'kategori' => $kategori[$r],
                                                    'id_valas' => $id_valas['id_valas'],
                                                    'kode_valas' => $kode_valas,
                                                    'simbol' => $simbol['simbol'],
                                                    'jenis_biaya' => 'PB',
                                                    'biaya' => $biaya,
                                                    'tanggal' => $tanggal[$r],
                                                ];
                                                $this->m_biaya->distinct($data_biaya_pb_edit['id_biaya']);
                                                $this->m_biaya->save($data_biaya_pb_edit);
                                            } else {
                                                $data_biaya_pb_edit = [
                                                    'id_biaya' => $resultBiaya['id_biaya'],
                                                    'id_pum' => null,
                                                    'id_pjum' => null,
                                                    'id_pb' => $value['id_pb'],
                                                    'kategori' => $kategori[$r],
                                                    'id_valas' => $id_valas['id_valas'],
                                                    'kode_valas' => $kode_valas,
                                                    'simbol' => $simbol['simbol'],
                                                    'jenis_biaya' => 'PB',
                                                    'biaya' => $biaya,
                                                    'tanggal' => $tanggal[$r],
                                                    'edited_at' => $timestamp,
                                                    // 'edited_by' => '05080',
                                                ];
                                                $this->m_biaya->distinct($data_biaya_pb_edit['id_biaya']);
                                                $this->m_biaya->save($data_biaya_pb_edit);
                                            }
                                        }
                                    }
                                }

                                if(empty($cekvalutapb['jenis_biaya'])) {
                                    // echo 'PB KOSONG) ';
                                } else {
                                    // echo 'PB ISI) ';
                                    if($id_transaksi == $cekvalutapb['id_transaksi'] && $r+1 == $cekvalutapb['baris'] && $k+1 == $cekvalutapb['kolom'] && 'PB' == $cekvalutapb['jenis_biaya']) {
                                        $resultBiaya = $this->m_biaya->where('id_transaksi', $id_transaksi)->where('baris', $r+1)->where('kolom', $k+1)->where('jenis_biaya', 'PB')->select('id_biaya as id_biaya')->first();
                                        $kirim = $this->m_id->where('id_transaksi', $id_transaksi)->select('kirim_pb')->first();
                                        $ceknopb = $this->m_pb->ceknomorpbvalas($id_transaksi, $nopb[$k], $id_valas['id_valas']);
                                        foreach ($ceknopb as $key => $value) {
                                            if ($kirim['kirim_pb'] == 0) {
                                                $data_biaya_pb_edit = [
                                                    'id_biaya' => $resultBiaya['id_biaya'],
                                                    'id_pum' => null,
                                                    'id_pjum' => null,
                                                    'id_pb' => $value['id_pb'],
                                                    'kategori' => $kategori[$r],
                                                    'id_valas' => $id_valas['id_valas'],
                                                    'kode_valas' => $kode_valas,
                                                    'simbol' => $simbol['simbol'],
                                                    'jenis_biaya' => 'PB',
                                                    'biaya' => $biaya,
                                                    'tanggal' => $tanggal[$r],
                                                ];
                                                $this->m_biaya->distinct($data_biaya_pb_edit['id_biaya']);
                                                $this->m_biaya->save($data_biaya_pb_edit);
                                            } else {
                                                $data_biaya_pb_edit = [
                                                    'id_biaya' => $resultBiaya['id_biaya'],
                                                    'id_pum' => null,
                                                    'id_pjum' => null,
                                                    'id_pb' => $value['id_pb'],
                                                    'kategori' => $kategori[$r],
                                                    'id_valas' => $id_valas['id_valas'],
                                                    'kode_valas' => $kode_valas,
                                                    'simbol' => $simbol['simbol'],
                                                    'jenis_biaya' => 'PB',
                                                    'biaya' => $biaya,
                                                    'tanggal' => $tanggal[$r],
                                                    'edited_at' => $timestamp,
                                                    // 'edited_by' => '05080',
                                                ];
                                                $this->m_biaya->distinct($data_biaya_pb_edit['id_biaya']);
                                                $this->m_biaya->save($data_biaya_pb_edit);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    session()->setFlashdata('success', 'Data Biaya berhasil di import');
                    break;
                } else {
                    // echo 'BIAYA KOSONG ';
                    $role= session()->get('akun_role');
                    $submit_pjum = $this->m_id->where('id_transaksi', $id_transaksi)->select('submit_pjum, kirim_pjum')->first();
                    $submit_pb = $this->m_id->where('id_transaksi', $id_transaksi)->select('submit_pb, kirim_pb')->first();
                    if($role == 'admin' || $role == 'user') {// && $submit_pjum['kirim_pjum'] == 0 && $submit_pb['kirim_pb'] == 0
                        $nik= session()->get('akun_nik');
                        $timestamp = date('Y-m-d H:i:s');
                        if (!empty($sheet[$r][2])) {
                            // echo 'KATEGORI ISI ';
                            $cekvaluta = $this->m_biaya->cekvaluta($id_transaksi, $r+1, $k+1, $jenis_biaya[$k]);
                            $cekvalutapjum = $this->m_biaya->cekvaluta($id_transaksi, $r+1, $k+1, 'PJUM');
                            $cekvalutapb = $this->m_biaya->cekvaluta($id_transaksi, $r+1, $k+1, 'PB');
                            $cekvaluta1 = $this->m_biaya->cekvaluta1($id_transaksi, $r+1, $k+1);

                            if(empty($cekvalutapjum['jenis_biaya'])) {
                                // echo 'PJUM KOSONG ';
                            } else {
                                if($id_transaksi == $cekvalutapjum['id_transaksi'] && $r+1 == $cekvalutapjum['baris'] && $k+1 == $cekvalutapjum['kolom'] && 'PJUM' == $cekvalutapjum['jenis_biaya']) {
                                    // echo 'PJUM ISI ';
                                    $resultBiaya = $this->m_biaya->where('id_transaksi', $id_transaksi)->where('baris', $r+1)->where('kolom', $k+1)->where('jenis_biaya', 'PJUM')->select('id_biaya as id_biaya')->first();
                                    $cekidkategori = $this->m_kategori->where('id_transaksi', $id_transaksi)->where('jenis_biaya', 'Support')->select('id_kategori as id_kategori')->findAll();
                                    $ceknopjum = $this->m_pjum->ceknomorpjumvalas($id_transaksi, $nopjum[$k], $id_valas['id_valas']);
                                    foreach ($ceknopjum as $key => $value) {
                                        $data_biaya_pjum_edit = [
                                            'id_biaya' => $resultBiaya['id_biaya'],
                                            'id_pum' => null,
                                            'id_pjum' => $value['id_pjum'],
                                            'id_pb' => null,
                                            'biaya' => null,
                                        ];
                                        $this->m_pjum->deletePostId($data_biaya_pjum_edit['id_pjum']);
                                        $this->m_kurs->deletekurspjum($data_biaya_pjum_edit['id_pjum']);

                                        $data = [
                                            'id_transaksi' => $id_transaksi,
                                            'submit_pjum' => 0,
                                            'kirim_pjum' => 0,
                                        ];
                                        $this->m_id->save($data);
                                    }

                                    foreach ($cekidkategori as $key => $value) {
                                        $data_id = [
                                            'id_kategori' => $value['id_kategori'],
                                        ];
                                        $this->m_kategori->deleteKategori($data_id['id_kategori']);
                                    }

                                    $this->m_pum->query('ALTER TABLE pum AUTO_INCREMENT 1');
                                    $this->m_pjum->query('ALTER TABLE pjum AUTO_INCREMENT 1');
                                    $this->m_kurs->query('ALTER TABLE kurs AUTO_INCREMENT 1');
                                    $this->m_kategori->query('ALTER TABLE kategori AUTO_INCREMENT 1');
                                    $this->m_biaya->query('ALTER TABLE biaya AUTO_INCREMENT 1');

                                    session()->setFlashdata('success', 'Data Biaya berhasil di hapus');
                                }
                            }

                            if(empty($cekvalutapb['jenis_biaya'])) {
                                // echo 'PB KOSONG ';
                            } else {
                                // echo 'PB ISI ';
                                if($id_transaksi == $cekvalutapb['id_transaksi'] && $r+1 == $cekvalutapb['baris'] && $k+1 == $cekvalutapb['kolom'] && 'PB' == $cekvalutapb['jenis_biaya']) {
                                    $resultBiaya = $this->m_biaya->where('id_transaksi', $id_transaksi)->where('baris', $r+1)->where('kolom', $k+1)->where('jenis_biaya', 'PB')->select('id_biaya as id_biaya')->first();
                                    $cekidkategori = $this->m_kategori->where('id_transaksi', $id_transaksi)->where('jenis_biaya', 'Support')->select('id_kategori as id_kategori')->findAll();
                                    $ceknopb = $this->m_pb->ceknomorpbvalas($id_transaksi, $nopb[$k], $id_valas['id_valas']);
                                    foreach ($ceknopb as $key => $value) {
                                        $val_pb = $this->m_pb->where('id_pb', $value['id_pb'])->select('created_by')->first();
                                        $data_biaya_pb_edit = [
                                            'id_biaya' => $resultBiaya['id_biaya'],
                                            'id_pum' => null,
                                            'id_pjum' => null,
                                            'id_pb' => $value['id_pb'],
                                            'biaya' => null,
                                        ];
                                        $this->m_pb->deletePostId($data_biaya_pb_edit['id_pb']);
                                        $this->m_kurs->deletekurspb($data_biaya_pb_edit['id_pb']);

                                        $data = [
                                            'id_transaksi' => $id_transaksi,
                                            'submit_pb' => 0,
                                            'kirim_pb' => 0,
                                        ];
                                        $this->m_id->save($data);
                                        // if($val_pb['created_by'] == '05080'){
                                        //     $bar = $r+1;
                                        //     // session()->setFlashdata('warning', ['Tidak dapat menghapus biaya pada baris ke '.$bar.', karena merupakan data yang diupload oleh Treasury']);
                                        //     // return redirect()-> to('dashboard/'.$id_transaksi);
                                        // } else {
                                            
                                        // }
                                    }

                                    foreach ($cekidkategori as $key => $value) {
                                        $val_pb = $this->m_pb->where('id_pb', $value['id_pb'])->select('created_by')->first();
                                        $data_id = [
                                            'id_kategori' => $value['id_kategori'],
                                        ];

                                        $this->m_kategori->deleteKategori($data_id['id_kategori']);
                                        // if($val_pb['created_by'] == '05080'){
                                        //     $bar = $r+1;
                                        //     // session()->setFlashdata('warning', ['Tidak dapat menghapus data PB pada baris ke '.$bar.', karena merupakan data yang diupload oleh Treasury']);
                                        //     // return redirect()-> to('dashboard/'.$id_transaksi);
                                        // } else {
                                            
                                        // }
                                    }

                                    $this->m_pb->query('ALTER TABLE pb AUTO_INCREMENT 1');
                                    $this->m_kurs->query('ALTER TABLE kurs AUTO_INCREMENT 1');
                                    $this->m_kategori->query('ALTER TABLE kategori AUTO_INCREMENT 1');
                                    $this->m_biaya->query('ALTER TABLE biaya AUTO_INCREMENT 1');

                                    session()->setFlashdata('success', 'Data Biaya berhasil di hapus');
                                }
                            }
                        }
                    } else if($submit_pjum['kirim_pjum'] == 1 && $role == 'treasury') {
                        $nik= session()->get('akun_nik');
                        $timestamp = date('Y-m-d H:i:s');
                        if (!empty($sheet[$r][2])) {
                            // echo 'KATEGORI ISI ';
                            $cekvaluta = $this->m_biaya->cekvaluta($id_transaksi, $r+1, $k+1, $jenis_biaya[$k]);
                            $cekvalutapjum = $this->m_biaya->cekvaluta($id_transaksi, $r+1, $k+1, 'PJUM');
                            $cekvalutapb = $this->m_biaya->cekvaluta($id_transaksi, $r+1, $k+1, 'PB');
                            $cekvaluta1 = $this->m_biaya->cekvaluta1($id_transaksi, $r+1, $k+1);

                            if(empty($cekvalutapjum['jenis_biaya'])) {
                                // echo 'PJUM KOSONG ';
                            } else {
                                if($id_transaksi == $cekvalutapjum['id_transaksi'] && $r+1 == $cekvalutapjum['baris'] && $k+1 == $cekvalutapjum['kolom'] && 'PJUM' == $cekvalutapjum['jenis_biaya']) {
                                    $ceknopjum = $this->m_pjum->ceknomorpjumvalas($id_transaksi, $nopjum[$k], $id_valas['id_valas']);
                                    foreach ($ceknopjum as $key => $value) {
                                        $val_pjum = $this->m_pjum->where('id_pjum', $value['id_pjum'])->select('created_by')->first();
                                        if($val_pjum['created_by'] != '05080'){
                                            $bar = $r+1;
                                            session()->setFlashdata('warning', ['Tidak dapat menghapus biaya pada baris ke '.$bar.', karena merupakan data yang diupload oleh User Bagian']);
                                            return redirect()-> to('dashboard/'.$id_transaksi);
                                        }
                                    }

                                    foreach ($cekidkategori as $key => $value) {
                                        $val_pjum = $this->m_pjum->where('id_pjum', $value['id_pjum'])->select('created_by')->first();
                                        if($val_pjum['created_by'] != '05080'){
                                            $bar = $r+1;
                                            session()->setFlashdata('warning', ['Tidak dapat menghapus data PJUM pada baris ke '.$bar.', karena merupakan data yang diupload oleh User Bagian']);
                                            return redirect()-> to('dashboard/'.$id_transaksi);
                                        }
                                    }
                                }
                            }

                            if(empty($cekvalutapb['jenis_biaya'])) {
                                // echo 'PB KOSONG ';
                            } else {
                                // echo 'PB ISI ';
                                if($id_transaksi == $cekvalutapb['id_transaksi'] && $r+1 == $cekvalutapb['baris'] && $k+1 == $cekvalutapb['kolom'] && 'PB' == $cekvalutapb['jenis_biaya']) {
                                    $resultBiaya = $this->m_biaya->where('id_transaksi', $id_transaksi)->where('baris', $r+1)->where('kolom', $k+1)->where('jenis_biaya', 'PB')->select('id_biaya as id_biaya')->first();
                                    $cekidkategori = $this->m_kategori->where('id_transaksi', $id_transaksi)->where('jenis_biaya', 'Support')->select('id_kategori as id_kategori')->findAll();
                                    $ceknopb = $this->m_pb->ceknomorpbvalas($id_transaksi, $nopb[$k], $id_valas['id_valas']);
                                    foreach ($ceknopb as $key => $value) {
                                        $val_pb = $this->m_pb->where('id_pb', $value['id_pb'])->select('created_by')->first();
                                        if($val_pb['created_by'] != '05080'){
                                            $bar = $r+1;
                                            session()->setFlashdata('warning', ['Tidak dapat menghapus biaya pada baris ke '.$bar.', karena merupakan data yang diupload oleh User Bagian']);
                                            return redirect()-> to('dashboard/'.$id_transaksi);
                                        } else {
                                            $data_biaya_pb_edit = [
                                                'id_biaya' => $resultBiaya['id_biaya'],
                                                'id_pum' => null,
                                                'id_pjum' => null,
                                                'id_pb' => $value['id_pb'],
                                                'biaya' => null,
                                            ];
                                            $this->m_pb->deletePostId($data_biaya_pb_edit['id_pb']);
                                            $this->m_kurs->deletekurspb($data_biaya_pb_edit['id_pb']);
                                        
                                            $data = [
                                                'id_transaksi' => $id_transaksi,
                                                'submit_pb' => 0,
                                                'kirim_pb' => 0,
                                            ];
                                            $this->m_id->save($data);
                                        }
                                    }

                                    foreach ($cekidkategori as $key => $value) {
                                        $val_pb = $this->m_pb->where('id_pb', $value['id_pb'])->select('created_by')->first();
                                        $data_id = [
                                            'id_kategori' => $value['id_kategori'],
                                        ];
                                        $this->m_kategori->deleteKategori($data_id['id_kategori']);
                                        // if($val_pb['created_by'] != '05080'){
                                        //     $bar = $r+1;
                                        //     // session()->setFlashdata('warning', ['Tidak dapat menghapus data PB pada baris ke '.$bar.', karena merupakan data yang diupload oleh Treasury']);
                                        //     // return redirect()-> to('dashboard/'.$id_transaksi);
                                        // } else {
                                            
                                        // }
                                    }
                                    
                                    $this->m_pb->query('ALTER TABLE pb AUTO_INCREMENT 1');
                                    $this->m_kurs->query('ALTER TABLE kurs AUTO_INCREMENT 1');
                                    $this->m_kategori->query('ALTER TABLE kategori AUTO_INCREMENT 1');
                                    $this->m_biaya->query('ALTER TABLE biaya AUTO_INCREMENT 1');

                                    session()->setFlashdata('success', 'Data Biaya berhasil di hapus');
                                }
                            }
                        }
                    }
                }
            }
        }
        return redirect()->to('dashboard/'.$id_transaksi);
    }

    public function exportbiaya($id_transaksi)
    {
        $role= session()->get('akun_role');

        $kat = $this->m_kategori->kategori($id_transaksi);
        if(empty($kat)) {
            session()->setFlashdata('warning', ['Tidak ada data, silahkan upload data biaya terlebih dahulu']);
            return redirect()-> to('dashboard/'.$id_transaksi);
        }

        $bia = $this->m_biaya->biaya($id_transaksi);
        if(empty($bia)) {
            session()->setFlashdata('warning', ['Tidak ada data, silahkan upload data biaya terlebih dahulu']);
            return redirect()-> to('dashboard/'.$id_transaksi);
        }

        $kategori = $this->m_kategori->kategori($id_transaksi);
        $biaya = $this->m_biaya->biaya($id_transaksi);
        $pum1 = $this->m_pum->pum($id_transaksi);
        $pjum = $this->m_pjum->pjum1($id_transaksi);
        $pb = $this->m_pb->pb1($id_transaksi);

        $bawah = $this->m_kategori->where('id_transaksi', $id_transaksi)->select('baris')->orderBy('id_kategori', 'desc')->first();
        $bawahpum = $this->m_pum->where('id_transaksi', $id_transaksi)->select('kolom')->orderBy('kolom', 'desc')->first();
        $bawahpjum = $this->m_pjum->where('id_transaksi', $id_transaksi)->select('kolom')->orderBy('kolom', 'desc')->first();
        $bawahpb = $this->m_pb->where('id_transaksi', $id_transaksi)->select('kolom')->orderBy('kolom', 'desc')->first();
        $baris = $this->m_kategori->where('id_transaksi', $id_transaksi)->select('baris')->orderBy('id_kategori', 'asc')->findAll();
        $nik = $this->m_id->where('id_transaksi', $id_transaksi)->select('nik')->first();

        $bawah1 = $this->m_pjum->where('id_transaksi', $id_transaksi)->select('kolom')->orderBy('kolom', 'asc')->first();
        $bawah2 = $this->m_pb->where('id_transaksi', $id_transaksi)->select('kolom')->orderBy('kolom', 'asc')->first();
        $atas1 = $this->m_pjum->where('id_transaksi', $id_transaksi)->select('kolom')->orderBy('kolom', 'desc')->first();
        $atas2 = $this->m_pb->where('id_transaksi', $id_transaksi)->select('kolom')->orderBy('kolom', 'desc')->first();

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName('Times New Roman');
        $spreadsheet->getDefaultStyle()->getFont()->setSize(12);
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle("Data Biaya PDLN");

        $personil = $this->m_personil->personil($id_transaksi);
        $negara = $this->m_negara_tujuan->negaratujuan($id_transaksi);

        $niknm_perso = '';
        $nik_perso = '';
        foreach ($personil as $pr => $perso) {
            $niknm_perso .= $perso['niknm'].', ';
            $nik_perso .= $perso['nik'].'_';
        }

        $tmp_negara = '';
        foreach ($negara as $ng => $neg) {
            $tmp_negara .= $neg['negara_tujuan'].', ';
        }

        $pum = $this->m_pum->where('id_transaksi', $id_transaksi)->groupBy(['pum', 'id_transaksi', 'id_valas'])->orderBy('id_pum', 'asc')->select('pum')->findAll();
        $uang_kembali = $this->m_pum->where('id_transaksi', $id_transaksi)->groupBy(['uang_kembali', 'id_transaksi', 'id_valas'])->orderBy('id_pum', 'asc')->select('uang_kembali')->findAll();
        $nopjum = $this->m_pjum->where('id_transaksi', $id_transaksi)->groupBy(['id_pjum'])->orderBy('kolom', 'asc')->select('nomor')->findAll();
        $nopb = $this->m_pb->where('id_transaksi', $id_transaksi)->groupBy(['id_pb'])->orderBy('kolom', 'asc')->select('nomor')->findAll();

        $arr1 = implode(' ', array_map(function ($entry) {
            return ($entry[key($entry)]);
        }, $pum));

        $exp1 = explode(' ', $arr1);

        $arr2 = implode(' ', array_map(function ($entry) {
            return ($entry[key($entry)]);
        }, $uang_kembali));

        $exp2 = explode(' ', $arr2);

        $arr3 = implode(' ', array_map(function ($entry) {
            return ($entry[key($entry)]);
        }, $nopjum));

        $exp3 = explode(' ', $arr3);

        $arr4 = implode(' ', array_map(function ($entry) {
            return ($entry[key($entry)]);
        }, $nopb));

        $exp4 = explode(' ', $arr4);
        
        $sheet->setCellValue('B1', 'PERJALANAN DINAS LUAR NEGERI '.substr($nik_perso, 0, -1).'_'.$id_transaksi);
        $sheet->setCellValue('I2', 'No PJUM =>');
        $sheet->setCellValue('I3', 'No PB =>');
        $sheet->setCellValue('I4', 'PUM =>');
        $sheet->setCellValue('I5', 'SISA UANG DIKEMBALIKAN =>');
        $sheet->setCellValue('B2', 'Negara Tujuan =>');
        $sheet->setCellValue('C2',  substr($tmp_negara, 0, -2));
        $sheet->setCellValue('C5', 'LIST KATEGORI ADA DI SHEET MASTER');
        $sheet->setCellValue('B5', 'FORMAT TANGGAL : YYYY-MM-DD');
        $sheet->setCellValue('B6', 'Tanggal');
        $sheet->setCellValue('C6', 'Kategori');
        $sheet->setCellValue('D6', 'Status');
        $sheet->setCellValue('E6', 'Ref');
        $sheet->setCellValue('F6', 'Note');
        $sheet->setCellValue('G6', 'Negara Tujuan');
        $sheet->setCellValue('H6', 'Negara Transit');
        $sheet->setCellValue('I6', 'Jumlah Personil');
        $sheet->setCellValue('J6', 'Valas');

        $array = array('G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ');
        $array1 = array('?','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK');
        $valas = $this->m_biaya->valuta($id_transaksi);
        $count = count((array)$valas);
        $alpha = $array[$count];

        if (empty($bawah1['kolom'])) {
            $bawah1['kolom'] = 0;
        }
        if (empty($bawah2['kolom'])) {
            $bawah2['kolom'] = 0;
        }
        if (empty($atas1['kolom'])) {
            $atas1['kolom'] = 0;
        }
        if (empty($atas2['kolom'])) {
            $atas2['kolom'] = 0;
        }
            
        if($role == 'admin' || $role == 'user'){
            $alpha1 = $array1[$bawah1['kolom']];
            $alpha2 = $array1[$bawah2['kolom']];
            $alpha3 = $array1[$atas1['kolom']];
            $alpha4 = $array1[$atas2['kolom']];

            if($count > $bawah1['kolom'] && $count > $bawah2['kolom']){
                $alpha = $array[$count];
            } else if($bawah1['kolom'] > $count && $bawah1['kolom'] > $bawah2['kolom']){
                $alpha = $array1[$atas1['kolom']];
            } else if($bawah2['kolom'] > $count && $bawah2['kolom'] > $bawah1['kolom']){
                $alpha = $array1[$atas2['kolom']];
            }
    
            $sheet->mergeCells('B1:'.$alpha.'1');
            $sheet->mergeCells('B6:B7');
            $sheet->mergeCells('C6:C7');
            $sheet->mergeCells('D6:D7');
            $sheet->mergeCells('E6:E7');
            $sheet->mergeCells('F6:F7');
            $sheet->mergeCells('G6:G7');
            $sheet->mergeCells('H6:H7');
            $sheet->mergeCells('I6:I7');
            $sheet->mergeCells('J6:'.$alpha.'6');
    
            foreach ($biaya as $key => $value) {
                $i = 7;
                $row = $value['baris'];
                $column = $value['kolom'];
                $sheet->setCellValueByColumnAndRow($column, $i, $value['kode_valas']);
                for ($j=8; $j <= $bawah['baris']; $j++) {
                    $bia = $value['biaya'];
                    $sheet->setCellValueByColumnAndRow($column, $row, $bia);
                }
            }
    
            $i = 8;
            foreach ($kategori as $key => $value) {
                $sheet->setCellValue('B'.$i, $value['tanggal']);
                $sheet->setCellValue('C'.$i, $value['kategori']);
                $sheet->setCellValue('D'.$i, $value['status']);
                $sheet->setCellValue('E'.$i, $value['ref']);
                $sheet->setCellValue('F'.$i, $value['note']);
                $sheet->setCellValue('G'.$i, $value['negara_tujuan']);
                $sheet->setCellValue('H'.$i, $value['negara_trading']);
                $sheet->setCellValue('I'.$i, $value['jumlah_personil']);
                $sheet->getStyle('B6:'.$alpha.$i)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $sheet->getStyle('I2:'.$alpha.$i)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $i++;
            }
    
            foreach ($pum1 as $key => $value) {
                $row = 4;
                $row1 = 5;
                $column = $value['kolom'];
                for ($j=$column; $j <= $bawahpum['kolom']; $j++) {
                    $pum = $value['pum'];
                    $uangkembali = $value['uang_kembali'];
                    $sheet->setCellValueByColumnAndRow($column, $row, $pum);
                    $sheet->setCellValueByColumnAndRow($column, $row1, $uangkembali);
                }
            }
    
            foreach ($pjum as $key => $value) {
                $row = 2;
                $column = $value['kolom'];
                for ($j=$column; $j <= $bawahpjum['kolom']; $j++) {
                    $nomor = $value['nomor'];
                    $sheet->setCellValueByColumnAndRow($column, $row, $nomor);
                }
            }
    
            foreach ($pb as $key => $value) {
                $row = 3;
                $column = $value['kolom'];
                for ($j=$column; $j <= $bawahpb['kolom']; $j++) {
                    $nomor = $value['nomor'];
                    $sheet->setCellValueByColumnAndRow($column, $row, $nomor);
                }
            }
    
            for ($k = 'B'; $k <= $alpha; $k++) {
                $spreadsheet->getActiveSheet()->getColumnDimension($k)->setWidth(20);
            }
    
            for ($k = 'J'; $k <= $alpha; $k++) {
                $spreadsheet->getActiveSheet()->getColumnDimension($k)->setAutoSize(true);
            }
    
            $sheet->getColumnDimension('B')->setAutoSize(true);
            $sheet->getColumnDimension('C')->setAutoSize(true);
            $sheet->getColumnDimension('F')->setAutoSize(true);
            $sheet->getColumnDimension('I')->setAutoSize(true);
            $sheet->getColumnDimension('A')->setVisible(false);
    
            $sheet->getStyle('B:I')->getAlignment()->setHorizontal('center');
            $sheet->getStyle('B:I')->getAlignment()->setVertical('center');
            $sheet->getStyle('J:'.$alpha)->getAlignment()->setHorizontal('center');
            $sheet->getStyle('B')->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
            $sheet->getStyle('J:'.$alpha)->getNumberFormat()->setFormatCode('#,##0.00');

            $spreadsheet->createSheet();
            $sheet1 = $spreadsheet->setActiveSheetIndex(1);

            // Rename worksheet
            $spreadsheet->getActiveSheet(1)->setTitle('Master');

            $sheet1->setCellValue('A1', 'Kategori');
            $sheet1->setCellValue('A2', 'Tiket Pesawat');
            $sheet1->setCellValue('A3', 'Bagasi Pesawat');
            $sheet1->setCellValue('A4', 'Porter Pesawat');
            $sheet1->setCellValue('A5', 'Hotel');
            $sheet1->setCellValue('A6', 'Makan dan Minum');
            $sheet1->setCellValue('A7', 'Transportasi');
            $sheet1->setCellValue('A8', 'Laundry');
            $sheet1->setCellValue('A9', 'Lain-lain');
            $sheet1->setCellValue('A10', 'Tukar Uang Keluar');
            $sheet1->setCellValue('A11', 'Tukar Uang Masuk');
            $sheet1->setCellValue('A12', 'Kembalian');

            $sheet1->setCellValue('B1', 'Note');
            $sheet1->setCellValue('B2', 'Tiket Pesawat');
            $sheet1->setCellValue('B3', 'Bagasi Pesawat');
            $sheet1->setCellValue('B4', 'Porter Pesawat');
            $sheet1->setCellValue('B5', 'Tiket Hotel');
            $sheet1->setCellValue('B6', 'Tip Hotel');
            $sheet1->setCellValue('B7', 'Makan dan Minum');
            $sheet1->setCellValue('B8', 'Tip Makan dan Minum');
            $sheet1->setCellValue('B9', 'Taxi');
            $sheet1->setCellValue('B10', 'Laundry');
            $sheet1->setCellValue('B11', 'Beli SIM Card');
            $sheet1->setCellValue('B12', 'Beli Sample');
            $sheet1->setCellValue('B13', 'Tukar Uang');
            $sheet1->setCellValue('B14', 'Kembalian');

            $sheet1->setCellValue('C1', 'Valas');
            $sheet1->setCellValue('C2', 'IDR');
            $sheet1->setCellValue('C3', 'USD');
            $sheet1->setCellValue('C4', 'SGD');
            $sheet1->setCellValue('C5', 'KHR');
            $sheet1->setCellValue('C6', 'VND');
            $sheet1->setCellValue('C7', 'MYR');
            $sheet1->setCellValue('C8', 'THB');
            $sheet1->setCellValue('C9', 'LAK');
            $sheet1->setCellValue('C10', 'MMK');
            $sheet1->setCellValue('C11', 'BND');
            $sheet1->setCellValue('C12', 'PHP');
            $sheet1->setCellValue('C13', 'EUR');
            $sheet1->setCellValue('C14', 'GBP');

            $sheet1->setCellValue('D1', 'Status');
            $sheet1->setCellValue('D2', 'Dibelikan GS');
            $sheet1->setCellValue('D3', 'Beli Sendiri');

            $sheet1->getStyle('A1:D14')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

            for ($k = 'A'; $k <= 'D'; $k++) {
                $spreadsheet->getActiveSheet(1)->getColumnDimension($k)->setAutoSize(true);
            }

            $sheet1->getStyle('A:D')->getAlignment()->setHorizontal('center');
            $sheet1->getStyle('A:D')->getAlignment()->setVertical('center');
            $sheet1->getStyle('A1:D1')->getFont()->setBold( true );

            $spreadsheet->setActiveSheetIndex(0);

            $nik = substr($nik_perso, 0, -1);
            $niknm = substr($niknm_perso, 0, -2);
    
            $writer = new Xls($spreadsheet);
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment; filename=Biaya Perjalanan Dinas Luar Negeri/'.$nik.'/'.$id_transaksi.'.xls');
            $writer->save("php://output");
            // $writer->save('Biaya '.substr($nik_perso, 0, -1).'_'.$id_transaksi.'.xls');
            // return redirect()->to('dashboard/'.$id_transaksi);
        } else if($role == 'treasury') {
            $alpha1 = $array1[$bawah1['kolom']];
            $alpha2 = $array1[$bawah2['kolom']];
            $alpha3 = $array1[$atas1['kolom']];
            $alpha4 = $array1[$atas2['kolom']];

            if($count > $bawah1['kolom'] && $count > $bawah2['kolom']){
                $alpha = $array[$count];
            } else if($bawah1['kolom'] > $count && $bawah1['kolom'] > $bawah2['kolom']){
                $alpha = $array1[$atas1['kolom']];
            } else if($bawah2['kolom'] > $count && $bawah2['kolom'] > $bawah1['kolom']){
                $alpha = $array1[$atas2['kolom']];
            }
    
            $sheet->mergeCells('B1:'.$alpha.'1');
            $sheet->mergeCells('B6:B7');
            $sheet->mergeCells('C6:C7');
            $sheet->mergeCells('D6:D7');
            $sheet->mergeCells('E6:E7');
            $sheet->mergeCells('F6:F7');
            $sheet->mergeCells('G6:G7');
            $sheet->mergeCells('H6:H7');
            $sheet->mergeCells('I6:I7');
            $sheet->mergeCells('J6:'.$alpha.'6');
    
            foreach ($biaya as $key => $value) {
                $i = 7;
                $row = $value['baris'];
                $column = $value['kolom'];
                $sheet->setCellValueByColumnAndRow($column, $i, $value['kode_valas']);
                for ($j=8; $j <= $bawah['baris']; $j++) {
                    $bia = $value['biaya'];
                    $sheet->setCellValueByColumnAndRow($column, $row, $bia);
                }
            }
    
            $i = 8;
            foreach ($kategori as $key => $value) {
                $sheet->setCellValue('B'.$i, $value['tanggal']);
                $sheet->setCellValue('C'.$i, $value['kategori']);
                $sheet->setCellValue('D'.$i, $value['status']);
                $sheet->setCellValue('E'.$i, $value['ref']);
                $sheet->setCellValue('F'.$i, $value['note']);
                $sheet->setCellValue('G'.$i, $value['negara_tujuan']);
                $sheet->setCellValue('H'.$i, $value['negara_trading']);
                $sheet->setCellValue('I'.$i, $value['jumlah_personil']);
                $sheet->getStyle('B6:'.$alpha.$i)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $sheet->getStyle('I2:'.$alpha.$i)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $i++;
            }
    
            foreach ($pum1 as $key => $value) {
                $row = 4;
                $row1 = 5;
                $column = $value['kolom'];
                for ($j=$column; $j <= $bawahpum['kolom']; $j++) {
                    $pum = $value['pum'];
                    $uangkembali = $value['uang_kembali'];
                    $sheet->setCellValueByColumnAndRow($column, $row, $pum);
                    $sheet->setCellValueByColumnAndRow($column, $row1, $uangkembali);
                }
            }
    
            foreach ($pjum as $key => $value) {
                $row = 2;
                $column = $value['kolom'];
                for ($j=$column; $j <= $bawahpjum['kolom']; $j++) {
                    $nomor = $value['nomor'];
                    $sheet->setCellValueByColumnAndRow($column, $row, $nomor);
                }
            }
    
            foreach ($pb as $key => $value) {
                $row = 3;
                $column = $value['kolom'];
                for ($j=$column; $j <= $bawahpb['kolom']; $j++) {
                    $nomor = $value['nomor'];
                    $sheet->setCellValueByColumnAndRow($column, $row, $nomor);
                }
            }
    
            for ($k = 'B'; $k <= $alpha; $k++) {
                $spreadsheet->getActiveSheet()->getColumnDimension($k)->setWidth(20);
            }
    
            for ($k = 'J'; $k <= $alpha; $k++) {
                $spreadsheet->getActiveSheet()->getColumnDimension($k)->setAutoSize(true);
            }
    
            $sheet->getColumnDimension('B')->setAutoSize(true);
            $sheet->getColumnDimension('C')->setAutoSize(true);
            $sheet->getColumnDimension('F')->setAutoSize(true);
            $sheet->getColumnDimension('I')->setAutoSize(true);
            $sheet->getColumnDimension('A')->setVisible(false);
    
            $sheet->getStyle('B:I')->getAlignment()->setHorizontal('center');
            $sheet->getStyle('B:I')->getAlignment()->setVertical('center');
            $sheet->getStyle('J:'.$alpha)->getAlignment()->setHorizontal('center');
            $sheet->getStyle('B')->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
            $sheet->getStyle('J:'.$alpha)->getNumberFormat()->setFormatCode('#,##0.00');

            $spreadsheet->createSheet();
            $sheet1 = $spreadsheet->setActiveSheetIndex(1);

            // Rename worksheet
            $spreadsheet->getActiveSheet(1)->setTitle('Master');

            $sheet1->setCellValue('A1', 'Kategori');
            $sheet1->setCellValue('A2', 'Tiket Pesawat');
            $sheet1->setCellValue('A3', 'Bagasi Pesawat');
            $sheet1->setCellValue('A4', 'Porter Pesawat');
            $sheet1->setCellValue('A5', 'Hotel');
            $sheet1->setCellValue('A6', 'Makan dan Minum');
            $sheet1->setCellValue('A7', 'Transportasi');
            $sheet1->setCellValue('A8', 'Laundry');
            $sheet1->setCellValue('A9', 'Lain-lain');
            $sheet1->setCellValue('A10', 'Tukar Uang Keluar');
            $sheet1->setCellValue('A11', 'Tukar Uang Masuk');
            $sheet1->setCellValue('A12', 'Kembalian');

            $sheet1->setCellValue('B1', 'Note');
            $sheet1->setCellValue('B2', 'Tiket Pesawat');
            $sheet1->setCellValue('B3', 'Bagasi Pesawat');
            $sheet1->setCellValue('B4', 'Porter Pesawat');
            $sheet1->setCellValue('B5', 'Tiket Hotel');
            $sheet1->setCellValue('B6', 'Tip Hotel');
            $sheet1->setCellValue('B7', 'Makan dan Minum');
            $sheet1->setCellValue('B8', 'Tip Makan dan Minum');
            $sheet1->setCellValue('B9', 'Taxi');
            $sheet1->setCellValue('B10', 'Laundry');
            $sheet1->setCellValue('B11', 'Beli SIM Card');
            $sheet1->setCellValue('B12', 'Beli Sample');
            $sheet1->setCellValue('B13', 'Tukar Uang');
            $sheet1->setCellValue('B14', 'Kembalian');

            $sheet1->setCellValue('C1', 'Valas');
            $sheet1->setCellValue('C2', 'IDR');
            $sheet1->setCellValue('C3', 'USD');
            $sheet1->setCellValue('C4', 'SGD');
            $sheet1->setCellValue('C5', 'KHR');
            $sheet1->setCellValue('C6', 'VND');
            $sheet1->setCellValue('C7', 'MYR');
            $sheet1->setCellValue('C8', 'THB');
            $sheet1->setCellValue('C9', 'LAK');
            $sheet1->setCellValue('C10', 'MMK');
            $sheet1->setCellValue('C11', 'BND');
            $sheet1->setCellValue('C12', 'PHP');
            $sheet1->setCellValue('C13', 'EUR');
            $sheet1->setCellValue('C14', 'GBP');

            $sheet1->setCellValue('D1', 'Status');
            $sheet1->setCellValue('D2', 'Dibelikan GS');
            $sheet1->setCellValue('D3', 'Beli Sendiri');

            $sheet1->getStyle('A1:D14')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

            for ($k = 'A'; $k <= 'D'; $k++) {
                $spreadsheet->getActiveSheet(1)->getColumnDimension($k)->setAutoSize(true);
            }

            $sheet1->getStyle('A:D')->getAlignment()->setHorizontal('center');
            $sheet1->getStyle('A:D')->getAlignment()->setVertical('center');
            $sheet1->getStyle('A1:D1')->getFont()->setBold( true );

            $spreadsheet->setActiveSheetIndex(0);

            $nik = substr($nik_perso, 0, -1);
            $niknm = substr($niknm_perso, 0, -2);
    
            $writer = new Xls($spreadsheet);
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment; filename=Biaya Perjalanan Dinas Luar Negeri/'.$nik.'/'.$id_transaksi.'.xls');
            $writer->save("php://output");
            // $writer->save('Biaya '.substr($nik_perso, 0, -1).'_'.$id_transaksi.'.xls');
            // return redirect()->to('dashboard/'.$id_transaksi);
        }
    }

    public function exporterp($id_transaksi)
    {
        $role= session()->get('akun_role');

        $kat = $this->m_kategori->kategori($id_transaksi);
        if(empty($kat)) {
            session()->setFlashdata('warning', ['Tidak ada data, silahkan upload data biaya terlebih dahulu']);
            return redirect()-> to('dashboard/'.$id_transaksi);
        }

        $bia = $this->m_biaya->biaya($id_transaksi);
        if(empty($bia)) {
            session()->setFlashdata('warning', ['Tidak ada data, silahkan upload data biaya terlebih dahulu']);
            return redirect()-> to('dashboard/'.$id_transaksi);
        }

        // $id_pjum = $this->m_kategori->where('id_transaksi', $id_transaksi)->select('id_pjum')->findAll();

        // foreach ($id_pjum as $key => $value) {
        //     if ($value['id_pjum'] != null) {
        //         $nopjum = $this->m_pjum->where('id_pjum', $value['id_pjum'])->select('id_pjum, tanggal')->findAll();
        //         foreach ($nopjum as $np => $nopj) {
        //             if($nopj['tanggal'] == null) {
        //                 session()->setFlashdata('warning', ['Tambahkan tanggal pembuatan no PJUM terlebih dahulu untuk melakukan submit data']);
        //                 return redirect()-> to('dashboard/'.$id_transaksi);
        //             }
        //         }
        //     }
        // }

        $kategori = $this->m_kategori->kategori($id_transaksi);
        $biaya = $this->m_biaya->biaya($id_transaksi);
        $pjum = $this->m_pjum->pjum1($id_transaksi);
        $pb = $this->m_pb->pb1($id_transaksi);

        $bawah = $this->m_kategori->where('id_transaksi', $id_transaksi)->select('baris')->orderBy('id_kategori', 'desc')->first();
        $bawahpjum = $this->m_pjum->where('id_transaksi', $id_transaksi)->select('kolom')->orderBy('kolom', 'desc')->first();
        $bawahpb = $this->m_pb->where('id_transaksi', $id_transaksi)->select('kolom')->orderBy('kolom', 'desc')->first();
        $baris = $this->m_kategori->where('id_transaksi', $id_transaksi)->select('baris')->orderBy('id_kategori', 'asc')->findAll();
        $nik = $this->m_id->where('id_transaksi', $id_transaksi)->select('nik, tanggal_berangkat, tanggal_pulang')->first();

        $bawah1 = $this->m_pjum->where('id_transaksi', $id_transaksi)->select('kolom')->orderBy('kolom', 'asc')->first();
        $bawah2 = $this->m_pb->where('id_transaksi', $id_transaksi)->select('kolom')->orderBy('kolom', 'asc')->first();
        $atas1 = $this->m_pjum->where('id_transaksi', $id_transaksi)->select('kolom')->orderBy('kolom', 'desc')->first();
        $atas2 = $this->m_pb->where('id_transaksi', $id_transaksi)->select('kolom')->orderBy('kolom', 'desc')->first();

        $spreadsheet = new Spreadsheet();
        Calculation::getInstance($spreadsheet)->disableCalculationCache();
        Calculation::getInstance()->setCalculationCacheEnabled(FALSE);
        $spreadsheet->getDefaultStyle()->getFont()->setName('Times New Roman');
        $spreadsheet->getDefaultStyle()->getFont()->setSize(12);
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle("Rekap Perjalanan Dinas LN");

        $personil = $this->m_personil->personil($id_transaksi);
        $negara = $this->m_negara_tujuan->negaratujuan($id_transaksi);

        $niknm_perso = '';
        $nik_perso = '';
        foreach ($personil as $pr => $perso) {
            $niknm_perso .= $perso['niknm'].', ';
            $nik_perso .= $perso['nik'].'_';
        }

        $tmp_negara = '';
        foreach ($negara as $ng => $neg) {
            $tmp_negara .= $neg['negara_tujuan'].', ';
        }

        $totalbiayatot = $this->m_biaya->where('id_transaksi', $id_transaksi)->wherenotIn('jenis_biaya', ['Support', 'PB'])->wherenotIn('kategori', ['Tukar Uang Masuk', 'Tukar Uang Keluar', 'Kembalian'])->groupBy(['id_valas', 'id_transaksi'])->orderBy('id_biaya', 'asc')->select('sum(biaya) as sum, id_valas, kode_valas')->findAll();

        $tukar_masuk = $this->m_biaya->where('id_transaksi', $id_transaksi)->wherenotIn('jenis_biaya', ['Support', 'PB'])->where('kategori', 'Tukar Uang Masuk')->orderBy('id_biaya', 'asc')->select('sum(biaya) as sum, id_valas, kode_valas')->findAll();

        $tukar_keluar = $this->m_biaya->where('id_transaksi', $id_transaksi)->wherenotIn('jenis_biaya', ['Support', 'PB'])->where('kategori', 'Tukar Uang Keluar')->orderBy('id_biaya', 'asc')->select('sum(biaya) as sum, id_valas, kode_valas')->findAll();

        $valastot = $this->m_biaya->where('id_transaksi', $id_transaksi)->wherenotIn('jenis_biaya', ['Support', 'PB'])->wherenotIn('kategori', ['Tukar Uang Masuk', 'Tukar Uang Keluar', 'Kembalian'])->groupBy(['kode_valas', 'id_transaksi'])->orderBy('id_biaya', 'asc')->select('kode_valas')->findAll();
        
        $totalbiayakurs = $this->m_biaya->where('id_transaksi', $id_transaksi)->wherenotIn('jenis_biaya', ['Support', 'PB'])->wherenotIn('kategori', ['Tukar Uang Masuk', 'Tukar Uang Keluar', 'Kembalian'])->groupBy(['id_valas', 'id_transaksi'])->orderBy('id_biaya', 'asc')->select('sum(biaya) as sum, id_valas, kode_valas')->findAll();

        $valaskurs = $this->m_biaya->where('id_transaksi', $id_transaksi)->wherenotIn('jenis_biaya', ['Support', 'PB'])->wherenotIn('kategori', ['Tukar Uang Masuk', 'Tukar Uang Keluar', 'Kembalian'])->groupBy(['kode_valas', 'id_transaksi'])->orderBy('id_biaya', 'asc')->select('kode_valas')->findAll();

        foreach($valastot as $iv => $val){
            $valas_ada = 0; // 0= tidak ada, 1= ada
            $temp_isi = 0;
            foreach($tukar_masuk as $tm => $tuma){
                if($val['kode_valas'] == $tuma['kode_valas']){
                    $count = count((array)$valastot);
                    $valas_ada = 1;
                    $temp_isi = $tuma['sum'];
                    break;
                }
            }

            $temptukarmasuk[$iv] = array(
                'sum' => $temp_isi,
            );
        }

        $tukar_masuk = $temptukarmasuk;

        foreach($valastot as $iv => $val){
            $valas_ada = 0; // 0= tidak ada, 1= ada
            $temp_isi = 0;
            foreach($tukar_keluar as $tk => $tuke){
                if($val['kode_valas'] == $tuke['kode_valas']){
                    $count = count((array)$valastot);
                    $valas_ada = 1;
                    $temp_isi = $tuke['sum'];
                    break;
                }
            }

            $temptukarkeluar[$iv] = array(
                'sum' => $temp_isi,
            );
        }

        $tukar_keluar = $temptukarkeluar;
        
        foreach($valastot as $iv => $val){
            $valas_ada = 0; // 0= tidak ada, 1= ada
            $temp_isi = 0;
            foreach($totalbiayatot as $ib => $bia){
                if($val['kode_valas'] == $bia['kode_valas']){
                    $count = count((array)$valastot);
                    $valas_ada = 1;
                    $temp_isi = $bia['sum'];
                    break;
                }
            }

            $temptotalbiayatot[$iv] = array(
                'sum' => $temp_isi,
            );
        }

        $totalbiayatot = $temptotalbiayatot;

        foreach($valaskurs as $iv => $val){
            $valas_ada = 0; // 0= tidak ada, 1= ada
            $temp_isi = 0;
            foreach($totalbiayakurs as $ib => $bia){
                foreach ($pjum as $key => $value) {
                    $kurs = $this->m_kurs->where('id_pjum', $value['id_pjum'])->select('kurs, id_kurs, id_valas, kode_valas')->findAll();
                    foreach ($kurs as $kr => $kur) {
                        if($val['kode_valas'] == $bia['kode_valas'] && $val['kode_valas'] == $kur['kode_valas']){
                            $count = count((array)$valastot);
                            $valas_ada = 1;
                            $temp_isi = $bia['sum'] * $kur['kurs'];
                            break;
                        }
                    }
                }
    
                $kurstotalbiaya[$iv] = array(
                    'sum' => (string)$temp_isi,
                );
            }
        }

        $totalbiayakurs = $kurstotalbiaya;

        foreach($valaskurs as $iv => $val){
            $valas_ada = 0; // 0= tidak ada, 1= ada
            $temp_isi = 1;
            foreach ($pjum as $key => $value) {
                $kurs = $this->m_kurs->where('id_pjum', $value['id_pjum'])->select('kurs, id_kurs, id_valas, kode_valas')->findAll();
                foreach ($kurs as $kr => $kur) {
                    if($val['kode_valas'] == $kur['kode_valas']){
                        $valas_ada = 1;
                        $temp_isi = $kur['kurs'];
                        break;
                    }
                }
            }

            $kursbiaya[$iv] = array(
                'sum' => (string)$temp_isi,
            );
        }

        $biayakurs = $kursbiaya;
        
        // foreach($valaskurs as $iv => $val){
        //     $valas_ada = 0; // 0= tidak ada, 1= ada
        //     $temp_isi = 0;
        //     foreach($totalbiayakurs as $ib => $bia){
        //         foreach ($pjum as $key => $value) {
        //             $kurs = $this->m_kurs->where('id_pjum', $value['id_pjum'])->select('kurs, id_kurs, id_valas, kode_valas')->findAll();
        //             foreach ($kurs as $kr => $kur) {
        //                 if($val['kode_valas'] == $bia['kode_valas'] && $val['kode_valas'] == $kur['kode_valas']){
        //                     $count = count((array)$valastot);
        //                     $valas_ada = 1;
        //                     $temp_isi = $bia['sum'] * $kur['kurs'];
        //                     break;
        //                 }
        //             }
        //         }
    
        //         $kurstotalbiaya[$iv] = array(
        //             'sum' => (string)$temp_isi,
        //         );
        //     }
        // }

        // $totalbiayakurs = $kurstotalbiaya;

        $pum = $this->m_pum->where('id_transaksi', $id_transaksi)->groupBy(['pum', 'id_transaksi', 'id_valas'])->orderBy('id_pum', 'asc')->select('pum')->findAll();
        $uang_kembali = $this->m_pum->where('id_transaksi', $id_transaksi)->groupBy(['uang_kembali', 'id_transaksi', 'id_valas'])->orderBy('id_pum', 'asc')->select('uang_kembali')->findAll();
        $nopjum = $this->m_pjum->where('id_transaksi', $id_transaksi)->groupBy(['id_pjum'])->orderBy('kolom', 'asc')->select('nomor')->findAll();
        $nopb = $this->m_pb->where('id_transaksi', $id_transaksi)->groupBy(['id_pb'])->orderBy('kolom', 'asc')->select('nomor')->findAll();

        $arr1 = implode(' ', array_map(function ($entry) {
            return ($entry[key($entry)]);
        }, $pum));

        $exp1 = explode(' ', $arr1);

        $arr2 = implode(' ', array_map(function ($entry) {
            return ($entry[key($entry)]);
        }, $uang_kembali));

        $exp2 = explode(' ', $arr2);

        $arr3 = implode(' ', array_map(function ($entry) {
            return ($entry[key($entry)]);
        }, $nopjum));

        $exp3 = explode(' ', $arr3);

        $arr4 = implode(' ', array_map(function ($entry) {
            return ($entry[key($entry)]);
        }, $nopb));

        $exp4 = explode(' ', $arr4);

        $arr5 = implode(' ', array_map(function ($entry) {
            return ($entry[key($entry)]);
        }, $valastot));

        $exp5 = explode(' ', $arr5);

        $arr6 = implode(' ', array_map(function ($entry) {
            return ($entry[key($entry)]);
        }, $totalbiayatot));

        $exp6 = explode(' ', $arr6);

        $arr7 = implode(' ', array_map(function ($entry) {
            return ($entry[key($entry)]);
        }, $biayakurs));

        $exp7 = explode(' ', $arr7);

        $arr8 = implode(' ', array_map(function ($entry) {
            return ($entry[key($entry)]);
        }, $tukar_masuk));

        $exp8 = explode(' ', $arr8);

        $arr9 = implode(' ', array_map(function ($entry) {
            return ($entry[key($entry)]);
        }, $tukar_keluar));

        $exp9 = explode(' ', $arr9);
        
        $sheet->setCellValue('B1', 'PERJALANAN DINAS LUAR NEGERI '.substr($nik_perso, 0, -1).'_'.$id_transaksi);
        $sheet->setCellValue('B3', 'Personil =>');
        $sheet->setCellValue('B4', 'Tanggal Perjalanan =>');
        $sheet->setCellValue('C3', substr($niknm_perso, 0, -2));
        $sheet->setCellValue('C4', tanggal_indo1($nik['tanggal_berangkat']).' s/d '.tanggal_indo1($nik['tanggal_pulang']));
        $sheet->setCellValue('B8', 'Total Pengeluaran');
        $sheet->setCellValue('B9', 'Nilai PUM');
        $sheet->setCellValue('B10', 'Sisa Uang Seharusnya');
        $sheet->setCellValue('B11', 'Sisa Uang Dikembalikan');
        $sheet->setCellValue('B12', 'Kekurangan/Kelebihan');
        $sheet->setCellValue('B13', 'Tukar Uang Masuk');
        $sheet->setCellValue('B14', 'Tukar Uang Keluar');
        // $sheet->setCellValue('B13', 'Kurs Saat PJUM');
        // $sheet->setCellValue('B14', 'Kekurangan/Kelebihan Dalam IDR');
        $sheet->setCellValue('C6', 'Valas');

        $array = array('B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ');
        $array1 = array('?','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK');
        $valas = $this->m_biaya->valuta($id_transaksi);
        $alpha = $array[$count];

        $sheet->mergeCells('B1:'.$alpha.'1');
        $sheet->mergeCells('B6:B7');
        $sheet->mergeCells('C6:'.$alpha.'6');

        $sheet->fromArray($exp5, NULL, 'C7');
        $sheet->fromArray($exp6, NULL, 'C8');
        $sheet->fromArray($exp1, NULL, 'C9');
        $sheet->fromArray($exp8, NULL, 'C13');//Tukar Uang Masuk
        $sheet->fromArray($exp9, NULL, 'C14');//Tukar Uang Keluar

        for ($k = 'C'; $k <= $alpha; $k++) {
            $sheet->setCellValue($k.'10', '='.$k.'9-'.$k.'8-'.$k.'14+'.$k.'13');
        }

        $sheet->fromArray($exp2, NULL, 'C11');

        for ($k = 'C'; $k <= $alpha; $k++) {
            $sheet->setCellValue($k.'12', '='.$k.'11-'.$k.'10');
        }

        // $sheet->fromArray($exp7, NULL, 'C13');

        // for ($k = 'C'; $k <= $alpha; $k++) {
        //     $sheet->setCellValue($k.'14', '='.$k.'12*'.$k.'13');
        // }
        
        for ($k = 'C'; $k <= $alpha; $k++) {
            $spreadsheet->getActiveSheet()->getColumnDimension($k)->setWidth(20);
        }

        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('A')->setVisible(false);
        // $sheet->getRowDimension(13)->setVisible(false);
        // $sheet->getRowDimension(14)->setVisible(false);

        // $sheet->getTabColor()->setRGB('FFFF00');
        // $sheet->getStyle('B14:'.$alpha.'14')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);
        $sheet->getStyle('B6:'.$alpha.'14')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getStyle('B6:'.$alpha.'14')->getFont()->setBold( true );
        $sheet->getStyle('B8:'.$alpha.'8')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFF00');
        $sheet->getStyle('B10:'.$alpha.'10')->getFont()->getColor()->setARGB('993300');
        $sheet->getStyle('B11:'.$alpha.'11')->getFont()->getColor()->setARGB('0066CC');
        // $sheet->getStyle('B13:'.$alpha.'13')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('99CC00');
        // $sheet->getStyle('B14:'.$alpha.'14')->getFont()->getColor()->setARGB('FF0000');
        $sheet->getStyle('C3')->getAlignment()->setWrapText(true);
        $sheet->getStyle('C4')->getAlignment()->setWrapText(true);
        $sheet->getStyle('B:'.$alpha)->getAlignment()->setHorizontal('center');
        $sheet->getStyle('C8:'.$alpha.'14')->getAlignment()->setHorizontal('right');
        $sheet->getStyle('C:'.$alpha)->getNumberFormat()->setFormatCode('#,##0.00');

        $nik = substr($nik_perso, 0, -1);
        $niknm = substr($niknm_perso, 0, -2);

        $writer = new Xls($spreadsheet);

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename=PJUM Perjalanan Dinas Luar Negeri/'.$nik.'/'.$id_transaksi.'.xls');
        $writer->save("php://output");

        // $writer->save('PJUM Perjalanan Dinas Luar Negeri '.$nik.'_'.$id_transaksi.'.xls');
        // return redirect()->to('dashboard/'.$id_transaksi);
    }

    public function exportlaporan($id_transaksi)
    {
        $arraypjum = array('C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ');
        $arraypb = array('F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK');
        $valaspjum = $this->m_biaya->valas($id_transaksi, 'pjum');
        $valaspb = $this->m_biaya->valas($id_transaksi, 'pb');
        $valassup = $this->m_biaya->valas($id_transaksi, 'support');
        $valasall = $this->m_biaya->where('id_transaksi', $id_transaksi)->wherenotIn('jenis_biaya', ['Support'])->groupBy(['kode_valas', 'id_transaksi'])->orderBy('id_biaya', 'asc')->select('kode_valas')->findAll();
        $valassel = $this->m_biaya->where('id_transaksi', $id_transaksi)->groupBy(['kode_valas', 'id_transaksi'])->orderBy('id_biaya', 'asc')->select('kode_valas')->findAll();
        $valassupport = $this->m_biaya->support($id_transaksi, 'support');
        $countpjum = count((array)$valaspjum);
        $countpb = count((array)$valaspb);
        $countall = count((array)$valasall);
        $countsel = count((array)$valassel);
        $countsupport = count((array)$valassupport);
        $countsup = count((array)$valassup);
        if ($countpjum == null) {
            $countpjum = $countall;
        } 
        if ($countpb == null) {
            $countpb = $countall;
        }
        $akhirpjum = $countpjum + 3;
        $akhirpb = $akhirpjum + 4;
        $alphapjum = $arraypjum[$countpjum];
        $alphaall = $arraypjum[$countall];
        $alphasel = $arraypjum[$countsel];
        $alphasup = $arraypjum[$countsup + 1];
        $alphapjum2 = $arraypjum[$countpjum + 2];
        $alphapb = $arraypb[$countpb + 2];
        $alphatotal = $arraypjum[$countpjum + 2 + $countpb + 1];
        
        $kategoripesawatpjum = $this->m_kategori->kategoripesawatpjum($id_transaksi);
        $kategoripesawatpb = $this->m_kategori->kategoripesawatpb($id_transaksi);

        $kategoripjum = $this->m_kategori->getDataIdtransaksi($id_transaksi, 'pjum');
        $kategoripb = $this->m_kategori->getDataIdtransaksi($id_transaksi, 'pb');
        $kategorisupport = $this->m_kategori->getDataIdtransaksi($id_transaksi, 'support');

        $biayapesawatpjum = $this->m_biaya->biayapesawatpjum($id_transaksi);
        $biayapesawatpb = $this->m_biaya->biayapesawatpb($id_transaksi);

        $jumlahbarispesawatpjum = $this->m_kategori->where('id_transaksi', $id_transaksi)->wherenotIn('jenis_biaya', ['Support', 'pb'])->whereIn('kategori', ['Pesawat'])->groupBy(['baris', 'id_transaksi'])->orderBy('id_kategori', 'asc')->select('baris')->findAll();
        $barisataspesawatpjum = $this->m_kategori->where('id_transaksi', $id_transaksi)->wherenotIn('jenis_biaya', ['Support', 'pb'])->whereIn('kategori', ['Pesawat'])->groupBy(['baris', 'id_transaksi'])->orderBy('id_kategori', 'asc')->select('baris')->first();
        $jumlahbarispesawatpb = $this->m_kategori->where('id_transaksi', $id_transaksi)->wherenotIn('jenis_biaya', ['Support', 'pjum'])->whereIn('kategori', ['Pesawat'])->groupBy(['baris', 'id_transaksi'])->orderBy('id_kategori', 'asc')->select('baris')->findAll();
        $barisataspesawatpb = $this->m_kategori->where('id_transaksi', $id_transaksi)->wherenotIn('jenis_biaya', ['Support', 'pjum'])->whereIn('kategori', ['Pesawat'])->groupBy(['baris', 'id_transaksi'])->orderBy('id_kategori', 'asc')->select('baris')->first();
        
        $valaspesawat = $this->m_biaya->where('id_transaksi', $id_transaksi)->wherenotIn('jenis_biaya', ['Support'])->groupBy(['kode_valas', 'id_transaksi'])->orderBy('id_biaya', 'asc')->select('kode_valas')->findAll();
        $valastot = $this->m_biaya->where('id_transaksi', $id_transaksi)->groupBy(['kode_valas', 'id_transaksi'])->orderBy('id_biaya', 'asc')->select('kode_valas')->findAll();
        $countpes = count((array)$valaspesawat);

        $listkategori = $this->m_kategori->where('id_transaksi', $id_transaksi)->wherenotIn('jenis_biaya', ['Support'])->wherenotIn('kategori', ['Tukar Uang Masuk', 'Tukar Uang Keluar', 'Kembalian'])->groupBy(['kategori', 'id_transaksi'])->orderBy('kategori', 'asc')->select('kategori')->findAll();
        $countkat = count((array)$listkategori);
        $alphakat = $arraypjum[$countkat];

        $totalbiayapesawat = $this->m_biaya->where('id_transaksi', $id_transaksi)->wherenotIn('jenis_biaya', ['Support'])->whereIn('kategori', ['Tiket Pesawat'])->groupBy(['id_valas', 'id_transaksi'])->orderBy('id_biaya', 'asc')->select('sum(biaya) as sum, kode_valas')->findAll();
        $totalbiayabagasi = $this->m_biaya->where('id_transaksi', $id_transaksi)->wherenotIn('jenis_biaya', ['Support'])->whereIn('kategori', ['Bagasi Pesawat'])->groupBy(['id_valas', 'id_transaksi'])->orderBy('id_biaya', 'asc')->select('sum(biaya) as sum, kode_valas')->findAll();
        $totalbiayaporter = $this->m_biaya->where('id_transaksi', $id_transaksi)->wherenotIn('jenis_biaya', ['Support'])->whereIn('kategori', ['Porter Pesawat'])->groupBy(['id_valas', 'id_transaksi'])->orderBy('id_biaya', 'asc')->select('sum(biaya) as sum, kode_valas')->findAll();
        $totalbiayahotel = $this->m_biaya->where('id_transaksi', $id_transaksi)->wherenotIn('jenis_biaya', ['Support'])->whereIn('kategori', ['Hotel'])->groupBy(['id_valas', 'id_transaksi'])->orderBy('id_biaya', 'asc')->select('sum(biaya) as sum, kode_valas')->findAll();
        $totalbiayamakan = $this->m_biaya->where('id_transaksi', $id_transaksi)->wherenotIn('jenis_biaya', ['Support'])->whereIn('kategori', ['Makan dan Minum'])->groupBy(['id_valas', 'id_transaksi'])->orderBy('id_biaya', 'asc')->select('sum(biaya) as sum, kode_valas')->findAll();
        $totalbiayatrans = $this->m_biaya->where('id_transaksi', $id_transaksi)->wherenotIn('jenis_biaya', ['Support'])->whereIn('kategori', ['Transportasi'])->groupBy(['id_valas', 'id_transaksi'])->orderBy('id_biaya', 'asc')->select('sum(biaya) as sum, kode_valas')->findAll();
        $totalbiayalaundry = $this->m_biaya->where('id_transaksi', $id_transaksi)->wherenotIn('jenis_biaya', ['Support'])->whereIn('kategori', ['Laundry'])->groupBy(['id_valas', 'id_transaksi'])->orderBy('id_biaya', 'asc')->select('sum(biaya) as sum, kode_valas')->findAll();
        $totalbiayalain = $this->m_biaya->where('id_transaksi', $id_transaksi)->wherenotIn('jenis_biaya', ['Support'])->whereIn('kategori', ['Lain-lain'])->groupBy(['id_valas', 'id_transaksi'])->orderBy('id_biaya', 'asc')->select('sum(biaya) as sum, kode_valas')->findAll();
        $totalbiayatot = $this->m_biaya->where('id_transaksi', $id_transaksi)->wherenotIn('jenis_biaya', ['Support'])->wherenotIn('kategori', ['Tukar Uang Masuk', 'Tukar Uang Keluar', 'Kembalian'])->groupBy(['id_valas', 'id_transaksi'])->orderBy('id_biaya', 'asc')->select('sum(biaya) as sum, kode_valas')->findAll();
        $totalbiayasel = $this->m_biaya->where('id_transaksi', $id_transaksi)->wherenotIn('kategori', ['Tukar Uang Masuk', 'Tukar Uang Keluar', 'Kembalian'])->groupBy(['id_valas', 'id_transaksi'])->orderBy('id_biaya', 'asc')->select('sum(biaya) as sum, kode_valas')->findAll();

        foreach($valaspesawat as $iv => $val){
            $valas_ada = null; // 0= tidak ada, 1= ada
            $temp_isi = null;
            foreach($totalbiayapesawat as $ib => $bia){
                if($val['kode_valas'] == $bia['kode_valas']){
                    $valas_ada = 1;
                    $temp_isi = $bia['sum'];
                    break;
                }
            }

            $temptotalbiayapesawat[$iv] = array(
                'sum' => $temp_isi,
            );
        }

        $totalbiayapesawat = $temptotalbiayapesawat;

        foreach($valaspesawat as $iv => $val){
            $valas_ada = null; // 0= tidak ada, 1= ada
            $temp_isi = null;
            foreach($totalbiayabagasi as $ib => $bia){
                if($val['kode_valas'] == $bia['kode_valas']){
                    $valas_ada = 1;
                    $temp_isi = $bia['sum'];
                    break;
                }
            }

            $temptotalbiayabagasi[$iv] = array(
                'sum' => $temp_isi,
            );
        }

        $totalbiayabagasi = $temptotalbiayabagasi;

        foreach($valaspesawat as $iv => $val){
            $valas_ada = null; // 0= tidak ada, 1= ada
            $temp_isi = null;
            foreach($totalbiayaporter as $ib => $bia){
                if($val['kode_valas'] == $bia['kode_valas']){
                    $valas_ada = 1;
                    $temp_isi = $bia['sum'];
                    break;
                }
            }

            $temptotalbiayaporter[$iv] = array(
                'sum' => $temp_isi,
            );
        }

        $totalbiayaporter = $temptotalbiayaporter;

        foreach($valaspesawat as $iv => $val){
            $valas_ada = null; // 0= tidak ada, 1= ada
            $temp_isi = null;
            foreach($totalbiayahotel as $ib => $bia){
                if($val['kode_valas'] == $bia['kode_valas']){
                    $valas_ada = 1;
                    $temp_isi = $bia['sum'];
                    break;
                }
            }

            $temptotalbiayahotel[$iv] = array(
                'sum' => $temp_isi,
            );
        }

        $totalbiayahotel = $temptotalbiayahotel;

        foreach($valaspesawat as $iv => $val){
            $valas_ada = null; // 0= tidak ada, 1= ada
            $temp_isi = null;
            foreach($totalbiayamakan as $ib => $bia){
                if($val['kode_valas'] == $bia['kode_valas']){
                    $valas_ada = 1;
                    $temp_isi = $bia['sum'];
                    break;
                }
            }

            $temptotalbiayamakan[$iv] = array(
                'sum' => $temp_isi,
            );
        }

        $totalbiayamakan = $temptotalbiayamakan;

        foreach($valaspesawat as $iv => $val){
            $valas_ada = null; // 0= tidak ada, 1= ada
            $temp_isi = null;
            foreach($totalbiayatrans as $ib => $bia){
                if($val['kode_valas'] == $bia['kode_valas']){
                    $valas_ada = 1;
                    $temp_isi = $bia['sum'];
                    break;
                }
            }

            $temptotalbiayatrans[$iv] = array(
                'sum' => $temp_isi,
            );
        }

        $totalbiayatrans = $temptotalbiayatrans;

        foreach($valaspesawat as $iv => $val){
            $valas_ada = null; // 0= tidak ada, 1= ada
            $temp_isi = null;
            foreach($totalbiayalaundry as $ib => $bia){
                if($val['kode_valas'] == $bia['kode_valas']){
                    $valas_ada = 1;
                    $temp_isi = $bia['sum'];
                    break;
                }
            }

            $temptotalbiayalaundry[$iv] = array(
                'sum' => $temp_isi,
            );
        }

        $totalbiayalaundry = $temptotalbiayalaundry;

        foreach($valaspesawat as $iv => $val){
            $valas_ada = null; // 0= tidak ada, 1= ada
            $temp_isi = null;
            foreach($totalbiayalain as $ib => $bia){
                if($val['kode_valas'] == $bia['kode_valas']){
                    $valas_ada = 1;
                    $temp_isi = $bia['sum'];
                    break;
                }
            }

            $temptotalbiayalain[$iv] = array(
                'sum' => $temp_isi,
            );
        }

        $totalbiayalain = $temptotalbiayalain;

        foreach($valastot as $iv => $val){
            $valas_ada = null; // 0= tidak ada, 1= ada
            $temp_isi = null;
            foreach($totalbiayatot as $ib => $bia){
                if($val['kode_valas'] == $bia['kode_valas']){
                    $valas_ada = 1;
                    $temp_isi = $bia['sum'];
                    break;
                }
            }

            $temptotalbiayatot[$iv] = array(
                'sum' => $temp_isi,
            );
        }

        $totalbiayatot = $temptotalbiayatot;

        foreach($valastot as $iv => $val){
            $valas_ada = null; // 0= tidak ada, 1= ada
            $temp_isi = null;
            foreach($totalbiayasel as $ib => $bia){
                if($val['kode_valas'] == $bia['kode_valas']){
                    $valas_ada = 1;
                    $temp_isi = $bia['sum'];
                    break;
                }
            }

            $temptotalbiayasel[$iv] = array(
                'sum' => $temp_isi,
            );
        }

        $totalbiayasel = $temptotalbiayasel;

        $totalbiayasupport = $this->m_biaya->where('id_transaksi', $id_transaksi)->wherenotIn('jenis_biaya', ['pjum', 'pb'])->groupBy(['id_valas', 'id_transaksi'])->orderBy('id_biaya', 'asc')->select('sum(biaya)')->findAll();

        $arr2 = implode(' ', array_map(function ($entry) {
            return ($entry[key($entry)]);
        }, $valaspesawat));

        $exp2 = explode(' ', $arr2);

        $arr3 = implode(' ', array_map(function ($entry) {
            return ($entry[key($entry)]);
        }, $totalbiayapesawat));

        $exp3 = explode(' ', $arr3);

        $arr3a = implode(' ', array_map(function ($entry) {
            return ($entry[key($entry)]);
        }, $totalbiayabagasi));

        $exp3a = explode(' ', $arr3a);

        $arr3b = implode(' ', array_map(function ($entry) {
            return ($entry[key($entry)]);
        }, $totalbiayaporter));

        $exp3b = explode(' ', $arr3b);

        $arr4 = implode(' ', array_map(function ($entry) {
            return ($entry[key($entry)]);
        }, $totalbiayahotel));

        $exp4 = explode(' ', $arr4);

        $arr5 = implode(' ', array_map(function ($entry) {
            return ($entry[key($entry)]);
        }, $totalbiayamakan));

        $exp5 = explode(' ', $arr5);

        $arr6 = implode(' ', array_map(function ($entry) {
            return ($entry[key($entry)]);
        }, $totalbiayatrans));

        $exp6 = explode(' ', $arr6);

        $arr7 = implode(' ', array_map(function ($entry) {
            return ($entry[key($entry)]);
        }, $totalbiayalaundry));

        $exp7 = explode(' ', $arr7);

        $arr8 = implode(' ', array_map(function ($entry) {
            return ($entry[key($entry)]);
        }, $totalbiayalain));

        $exp8 = explode(' ', $arr8);
        
        $arr9 = implode(' ', array_map(function ($entry) {
            return ($entry[key($entry)]);
        }, $totalbiayatot));

        $exp9 = explode(' ', $arr9);
        
        $arr10 = implode(' ', array_map(function ($entry) {
            return ($entry[key($entry)]);
        }, $totalbiayasupport));

        $exp10 = explode(' ', $arr10);

        $arr11 = implode(' ', array_map(function ($entry) {
            return ($entry[key($entry)]);
        }, $valastot));

        $exp11 = explode(' ', $arr11);
        
        $arr12 = implode(' ', array_map(function ($entry) {
            return ($entry[key($entry)]);
        }, $totalbiayasel));

        $exp12 = explode(' ', $arr12);
        
        $arr13 = implode(' ', array_map(function ($entry) {
            return ($entry[key($entry)]);
        }, $totalbiayatot));

        $exp13 = explode(' ', $arr13);

        $jumlahpjumpesawat = count((array)$jumlahbarispesawatpjum);
        $jumlahpbpesawat = count((array)$jumlahbarispesawatpb);
        if ($jumlahpjumpesawat < $jumlahpbpesawat) {
            $jumlah = $jumlahpbpesawat;
        } else {
            $jumlah = $jumlahpjumpesawat;
        }

        // $barispjumpesawat = $jumlahpjumpesawat + 13;
        // $barispbpesawat = $jumlahpbpesawat + 13;
        // if ($barispjumpesawat < $barispbpesawat) {
        //     $baris = $barispbpesawat;
        // } else {
        //     $baris = $barispjumpesawat;
        // }

        $baris = 16;

        $biayapb = $this->m_biaya->getDataBiaya($id_transaksi, 'pb');
        $biayasupport = $this->m_biaya->getDataBiaya($id_transaksi, 'support');

        $bawahpb = $this->m_kategori->where('id_transaksi', $id_transaksi)->where('jenis_biaya', 'pb')->select('baris')->orderBy('id_kategori', 'desc')->first();
        $bawahsupport = $this->m_kategori->where('id_transaksi', $id_transaksi)->where('jenis_biaya', 'support')->select('baris')->orderBy('id_kategori', 'desc')->first();
        
        $pum = $this->m_pum->alldataIdt($id_transaksi);
        $nik = $this->m_id->where('id_transaksi', $id_transaksi)->select('nik, tanggal_berangkat, tanggal_pulang')->first();
        $kota = $this->m_id->where('id_transaksi', $id_transaksi)->select('kota')->first();
        $karyawan = $this->m_am21->where('nik', $nik['nik'])->select('niknm, strorg')->first();
        $bagian = $this->m_bm06->where('strorg', $karyawan['strorg'])->select('strorgnm')->first();

        $personil = $this->m_personil->personil($id_transaksi);
        $negara = $this->m_negara_tujuan->negaratujuan($id_transaksi);
        $nomorpjum = $this->m_pjum->pjum($id_transaksi);
        $nomorpb = $this->m_pb->pb($id_transaksi);

        $niknm_perso = '';
        $nik_perso = '';
        foreach ($personil as $pr => $perso) {
            $niknm_perso .= $perso['niknm'].', ';
            $nik_perso .= $perso['nik'].'_';
        }

        $tmp_negara = '';
        foreach ($negara as $ng => $neg) {
            $tmp_negara .= $neg['negara_tujuan'].', ';
        }

        $tmp_nopjum = '';
        foreach ($nomorpjum as $np => $nopjum) {
            $nompjum = $nopjum['nomor'];
            if ($nompjum == null) {
                $nompjum = "no pjum masih kosong";
            }
            $tmp_nopjum .= $nompjum.', ';
        }

        $tmp_nopb = '';
        foreach ($nomorpb as $np => $nopb) {
            $nompb = $nopb['nomor'];
            if ($nompb == null) {
                $nompb = "no pb masih kosong";
            }
            $tmp_nopb .= $nompb.', ';
        }

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName('Times New Roman');
        $spreadsheet->getDefaultStyle()->getFont()->setSize(12);
        $sheet = $spreadsheet->getActiveSheet();

        // Total Biaya Pengeluaran
        $sheet->setTitle("Biaya Perjalanan Dinas LN");
        
        $sheet->setCellValue('B1', 'PERJALANAN DINAS LUAR NEGERI_'.substr($nik_perso, 0, -1).'_'.$id_transaksi);
        $sheet->setCellValue('B3', 'Data Karyawan');
        $sheet->setCellValue('B4', 'Nama Karyawan =>');
        $sheet->setCellValue('B5', 'Bagian =>');
        $sheet->setCellValue('B6', 'No PJUM =>');
        $sheet->setCellValue('B7', 'No PB =>');
        $sheet->setCellValue('B9', 'Waktu Tugas');
        $sheet->setCellValue('B10', 'Negara Tujuan =>');
        $sheet->setCellValue('B11', 'Tanggal Keberangkatan (YYYY-MM-DD) =>');
        $sheet->setCellValue('B12', 'Tanggal Pulang (YYYY-MM-DD) =>');
        $sheet->setCellValue('B13', 'Berangkat dari kota =>');
        $sheet->setCellValue('C4', 	substr($niknm_perso, 0, -2));
        $sheet->setCellValue('C5', $bagian['strorgnm']);
        $sheet->setCellValue('C6', substr($tmp_nopjum, 0, -2));
        $sheet->setCellValue('C7', substr($tmp_nopb, 0, -2));
        $sheet->setCellValue('C10', substr($tmp_negara, 0, -2));
        $sheet->setCellValue('C11', $nik['tanggal_berangkat']);
        $sheet->setCellValue('C12', $nik['tanggal_pulang']);
        $sheet->setCellValue('C13', $kota['kota']);

        $sheet->mergeCells('B1:'.$alphaall.'1');

        $sheet->getStyle('B1')->getFont()->setBold( true );
        $sheet->getStyle('B3')->getFont()->setBold( true );
        $sheet->getStyle('B9')->getFont()->setBold( true );
        $sheet->getStyle('D:'.$alphasel)->getAlignment()->setHorizontal('center');
        $sheet->getStyle('D:'.$alphasel)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('B:'.$alphaall)->getAlignment()->setHorizontal('center');
        $sheet->getStyle('B:'.$alphaall)->getAlignment()->setVertical('center');
        
        // $sheet->getStyle('C4')->getAlignment()->setWrapText(true);
        // $sheet->getStyle('C10')->getAlignment()->setWrapText(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('A')->setVisible(false);
        $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(40);
        $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(40);
        $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(15);

        for ($k = 'D'; $k <= $alphasel; $k++) {
            $spreadsheet->getActiveSheet()->getColumnDimension($k)->setWidth(15);
        }
        
        // $sheet->setCellValue('B15', 'Biaya Pengeluaran Selama Perjalanan Dinas Luar Negeri');
        // $sheet->setCellValue('B17', 'Kategori (PJUM + PB)');
        // $sheet->setCellValue('D17', 'Total Biaya (PJUM + PB)');

        // $sheet->fromArray($exp2, NULL, 'D'.$baris+2);

        // $sheet->mergeCells('B17:C'.(int)$baris + 2);
        // $sheet->mergeCells('B15:'.$alphaall.'16');
        // $sheet->mergeCells('D17:'.$alphaall.'17');

        // $sheet->getStyle('B15:'.$alphaall.'16')->getFont()->setBold( true );

        // $i = $countkat + 18;
        // $sheet->getStyle('B15:'.$alphaall.$i)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        // $i++;

        // $row = 19;
        // foreach ($listkategori as $lk => $liskat) {
        //     $totalbiaya = $this->m_biaya->where('id_transaksi', $id_transaksi)->wherenotIn('jenis_biaya', ['Support'])->whereIn('kategori', [$liskat['kategori']])->groupBy(['id_valas', 'id_transaksi'])->orderBy('id_biaya', 'asc')->select('sum(biaya) as sum, kode_valas')->findAll();
        //     foreach($valaspesawat as $iv => $val){
        //         $valas_ada = 0; // 0= tidak ada, 1= ada
        //         $temp_isi = 0;
        //         foreach($totalbiaya as $ib => $bia){
        //             if($val['kode_valas'] == $bia['kode_valas']){
        //                 $valas_ada = 1;
        //                 $temp_isi = $bia['sum'];
        //                 break;
        //             }
        //         }
    
        //         $temptotalbiaya[$iv] = array(
        //             'sum' => $temp_isi,
        //         );
        //     }
        //     $totalbiaya = $temptotalbiaya;

        //     $sheet->mergeCells('B'.$row.':C'.$row);

        //     $sheet->setCellValue('B'.$row, $liskat['kategori']);

        //     $column = 4;
        //     foreach ($totalbiaya as $key => $value) {
        //         $sheet->setCellValueByColumnAndRow($column, $row, $value['sum']);
        //         $column++;
        //     }
        //     $row++;
        // }

        // $baristotal = 19 + $countkat; //24
        // $sheet->setCellValue('B'.$baristotal, 'Total Seluruh Biaya Pengeluaran (PJUM + PB)');//24

        // $sheet->fromArray($exp9, NULL, 'D'.$baristotal);//24

        // $baristot = $baristotal - 1;//24
        // $baristot1 = $baristotal;//25
        // $sheet->mergeCells('B'.$baristot1.':C'.$baristot1 + 1);

        // for ($i='D', $k='D'; $i<=$alphaall; $i++, $k++) {
        //     $sheet->mergeCells($i.$baristot1.':'.$k.$baristot1 + 1);
        // }

        // $sheet->getStyle('B'.$baristot1.':'.$alphaall.$baristot1 + 1)->getFont()->setBold( true );

        // $i = $baristotal + 1;//41
        // $sheet->getStyle('B'.$baristot1.':'.$alphaall.$i)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        // $i++;

        // // Biaya Support
        // $barissup = $baristotal + 8; //
        // $indexjudul = $baristotal + 5; //
        // $indexsupport = $countsupport + $barissup; //39
        // $indextotal = $indexsupport + 2;
        // $indexkat = $baristotal + 6;
        
        // $sheet->setCellValue('B'.$indexjudul, 'Biaya Support Perjalanan Dinas Luar Negeri');
        // $sheet->setCellValue('B'.$indexjudul + 1, 'Tanggal');
        // $sheet->setCellValue('C'.$indexjudul + 1, 'Kategori');
        // $sheet->setCellValue('D'.$indexjudul + 1, 'Jumlah Personil');
        // $sheet->setCellValue('E'.$indexjudul + 1, 'Biaya');
        // $sheet->setCellValue('B'.$indexsupport, 'Total Biaya Support');

        // $sheet->fromArray($exp10, NULL, 'E'.$indexsupport);

        // $sheet->mergeCells('B'.$indexjudul.':'.$alphasup.$indexjudul);
        // $sheet->mergeCells('B'.$indexkat.':B'.$indexkat + 1);
        // $sheet->mergeCells('C'.$indexkat.':C'.$indexkat + 1);
        // $sheet->mergeCells('D'.$indexkat.':D'.$indexkat + 1);
        // $sheet->mergeCells('B'.$indexsupport.':D'.$indexsupport);

        // $sheet->getStyle('B'.$indexjudul)->getFont()->setBold( true );
        // $sheet->getStyle('B'.$indexsupport.':'.$alphasup.$indexsupport)->getFont()->setBold( true );

        // $spreadsheet->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);

        // for ($i=$indexjudul; $i <= $indexsupport; $i++) { 
        //     $sheet->getStyle('B'.$indexjudul.':'.$alphasup.$indexsupport)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        //     $sheet->getStyle('D'.$indexjudul.':D'.$i)->getNumberFormat()->setFormatCode('#');
        //     $i++;
        // }

        // foreach ($biayasupport as $key => $value) {
        //     $i = $indexjudul + 2;
        //     $column = $value['kolom'];
        //     $row = $baristotal + (int)$value['baris'];
        //     $sheet->setCellValueByColumnAndRow($column, $i, $value['kode_valas']);
        //     $i++;
        //     for ($j=$indexjudul + 3; $j <= $indexsupport; $j++) {
        //         $bia = $value['biaya'];
        //         if ($bia == 0) {
        //             $bia = null;
        //         }
        //         $sheet->setCellValueByColumnAndRow(5, $row, $bia);
        //     }
        // }

        // $row = $indexjudul + 3;
        // foreach ($kategorisupport as $key => $value) {
        //     $sheet->setCellValue('B'.$row, $value['tanggal']);
        //     $sheet->setCellValue('C'.$row, $value['kategori']);
        //     $sheet->setCellValue('D'.$row, $value['jumlah_personil']);
        //     $row++;
        // }

        // $sheet->setCellValue('B'.$indextotal, 'Total Biaya Perjalanan Dinas Luar Negeri');//57
        // $sheet->setCellValue('B'.$indextotal + 1, 'Total Biaya (PJUM + PB + Support)');//58

        // $sheet->fromArray($exp11, NULL, 'D'.$indexsupport + 3);//58
        // $sheet->fromArray($exp12, NULL, 'D'.$indexsupport + 4);//59

        // $bottom = $indexsupport + 4;

        // $sheet->getStyle('B'.$bottom.':'.$alphaall.$bottom)->getFont()->setBold( true );

        // $barissel = $indextotal;//57
        // $barissel1 = $indextotal + 1;//58
        // $sheet->mergeCells('B'.$barissel.':'.$alphasel.$barissel);
        // $sheet->mergeCells('B'.$barissel1.':C'.$indextotal + 2);//59

        // $sheet->getStyle('B'.$barissel.':B'.$barissel1)->getFont()->setBold( true );
        // // $sheet->getStyle('B'.$barissel)->getAlignment()->setHorizontal('left');

        // $i = $indextotal + 2;//59
        // $sheet->getStyle('B'.$barissel.':'.$alphasel.$i)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        // $i++;
        //Akhir baris

        $baris = 15;
        $sheet->setCellValue('B'.(int)$baris + 1, '1. Tiket Pesawat');
        $sheet->setCellValue('B'.(int)$baris + 2, 'Total Biaya Kategori Tiket Pesawat (PJUM + PB)');

        $sheet->fromArray($exp2, NULL, 'D'.(int)$baris + 2);//20
        $sheet->fromArray($exp3, NULL, 'D'.(int)$baris + 3);//21

        $barispesawat = (int)$baris;//18
        $barishotel = (int)$baris + 1;//19
        $barishotel1 = (int)$baris + 2;//20
        $barishotel2 = (int)$baris + 3;//20
        $sheet->mergeCells('B'.$barispesawat.':'.$alphaall.$barispesawat);
        $sheet->mergeCells('B'.$barishotel.':'.$alphaall.$barishotel);
        $sheet->mergeCells('B'.$barishotel1.':C'.(int)$baris + 3);//21

        $sheet->getStyle('B'.$barishotel.':B'.$barishotel1)->getFont()->setBold( true );
        $sheet->getStyle('B'.$barishotel)->getAlignment()->setHorizontal('left');
        $sheet->getStyle('D'.$barishotel2.':'.$alphaall.$barishotel2)->getAlignment()->setHorizontal('right');

        $i = (int)$baris + 3;
        $sheet->getStyle('B'.$barispesawat.':'.$alphaall.$i)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $i++;

        $baris = 19;
        $sheet->setCellValue('B'.(int)$baris + 1, '2. Bagasi Pesawat');
        $sheet->setCellValue('B'.(int)$baris + 2, 'Total Biaya Kategori Bagasi Pesawat (PJUM + PB)');

        $sheet->fromArray($exp2, NULL, 'D'.(int)$baris + 2);//20
        $sheet->fromArray($exp3a, NULL, 'D'.(int)$baris + 3);//21

        $barispesawat = (int)$baris;//18
        $barishotel = (int)$baris + 1;//19
        $barishotel1 = (int)$baris + 2;//20
        $barishotel2 = (int)$baris + 3;//20
        $sheet->mergeCells('B'.$barispesawat.':'.$alphaall.$barispesawat);
        $sheet->mergeCells('B'.$barishotel.':'.$alphaall.$barishotel);
        $sheet->mergeCells('B'.$barishotel1.':C'.(int)$baris + 3);//21

        $sheet->getStyle('B'.$barishotel.':B'.$barishotel1)->getFont()->setBold( true );
        $sheet->getStyle('B'.$barishotel)->getAlignment()->setHorizontal('left');
        $sheet->getStyle('D'.$barishotel2.':'.$alphaall.$barishotel2)->getAlignment()->setHorizontal('right');

        $i = (int)$baris + 3;
        $sheet->getStyle('B'.$barispesawat.':'.$alphaall.$i)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $i++;

        $baris = 23;
        $sheet->setCellValue('B'.(int)$baris + 1, '3. Porter Pesawat');
        $sheet->setCellValue('B'.(int)$baris + 2, 'Total Biaya Kategori Porter Pesawat (PJUM + PB)');

        $sheet->fromArray($exp2, NULL, 'D'.(int)$baris + 2);//20
        $sheet->fromArray($exp3b, NULL, 'D'.(int)$baris + 3);//21

        $barispesawat = (int)$baris;//18
        $barishotel = (int)$baris + 1;//19
        $barishotel1 = (int)$baris + 2;//20
        $barishotel2 = (int)$baris + 3;//20
        $sheet->mergeCells('B'.$barispesawat.':'.$alphaall.$barispesawat);
        $sheet->mergeCells('B'.$barishotel.':'.$alphaall.$barishotel);
        $sheet->mergeCells('B'.$barishotel1.':C'.(int)$baris + 3);//21

        $sheet->getStyle('B'.$barishotel.':B'.$barishotel1)->getFont()->setBold( true );
        $sheet->getStyle('B'.$barishotel)->getAlignment()->setHorizontal('left');
        $sheet->getStyle('D'.$barishotel2.':'.$alphaall.$barishotel2)->getAlignment()->setHorizontal('right');

        $i = (int)$baris + 3;
        $sheet->getStyle('B'.$barispesawat.':'.$alphaall.$i)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $i++;

        $baris = 27;
        $sheet->setCellValue('B'.(int)$baris + 1, '4. Hotel');
        $sheet->setCellValue('B'.(int)$baris + 2, 'Total Biaya Kategori Hotel (PJUM + PB)');

        $sheet->fromArray($exp2, NULL, 'D'.(int)$baris + 2);//20
        $sheet->fromArray($exp4, NULL, 'D'.(int)$baris + 3);//21

        $barispesawat = (int)$baris;//18
        $barishotel = (int)$baris + 1;//19
        $barishotel1 = (int)$baris + 2;//20
        $barishotel2 = (int)$baris + 3;//20
        $sheet->mergeCells('B'.$barispesawat.':'.$alphaall.$barispesawat);
        $sheet->mergeCells('B'.$barishotel.':'.$alphaall.$barishotel);
        $sheet->mergeCells('B'.$barishotel1.':C'.(int)$baris + 3);//21

        $sheet->getStyle('B'.$barishotel.':B'.$barishotel1)->getFont()->setBold( true );
        $sheet->getStyle('B'.$barishotel)->getAlignment()->setHorizontal('left');
        $sheet->getStyle('D'.$barishotel2.':'.$alphaall.$barishotel2)->getAlignment()->setHorizontal('right');

        $i = (int)$baris + 3;
        $sheet->getStyle('B'.$barispesawat.':'.$alphaall.$i)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $i++;

        $baris = 31;
        $sheet->setCellValue('B'.(int)$baris + 1, '5. Makan dan Minum');
        $sheet->setCellValue('B'.(int)$baris + 2, 'Total Biaya Kategori Makan dan Minum (PJUM + PB)');

        $sheet->fromArray($exp2, NULL, 'D'.(int)$baris + 2);//20
        $sheet->fromArray($exp5, NULL, 'D'.(int)$baris + 3);//21

        $barispesawat = (int)$baris;//18
        $barishotel = (int)$baris + 1;//19
        $barishotel1 = (int)$baris + 2;//20
        $barishotel2 = (int)$baris + 3;//20
        $sheet->mergeCells('B'.$barispesawat.':'.$alphaall.$barispesawat);
        $sheet->mergeCells('B'.$barishotel.':'.$alphaall.$barishotel);
        $sheet->mergeCells('B'.$barishotel1.':C'.(int)$baris + 3);//21

        $sheet->getStyle('B'.$barishotel.':B'.$barishotel1)->getFont()->setBold( true );
        $sheet->getStyle('B'.$barishotel)->getAlignment()->setHorizontal('left');
        $sheet->getStyle('D'.$barishotel2.':'.$alphaall.$barishotel2)->getAlignment()->setHorizontal('right');

        $i = (int)$baris + 3;
        $sheet->getStyle('B'.$barispesawat.':'.$alphaall.$i)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $i++;

        $baris = 35;
        $sheet->setCellValue('B'.(int)$baris + 1, '6. Transportasi');
        $sheet->setCellValue('B'.(int)$baris + 2, 'Total Biaya Kategori Transportasi (PJUM + PB)');

        $sheet->fromArray($exp2, NULL, 'D'.(int)$baris + 2);//20
        $sheet->fromArray($exp6, NULL, 'D'.(int)$baris + 3);//21

        $barispesawat = (int)$baris;//18
        $barishotel = (int)$baris + 1;//19
        $barishotel1 = (int)$baris + 2;//20
        $barishotel2 = (int)$baris + 3;//20
        $sheet->mergeCells('B'.$barispesawat.':'.$alphaall.$barispesawat);
        $sheet->mergeCells('B'.$barishotel.':'.$alphaall.$barishotel);
        $sheet->mergeCells('B'.$barishotel1.':C'.(int)$baris + 3);//21

        $sheet->getStyle('B'.$barishotel.':B'.$barishotel1)->getFont()->setBold( true );
        $sheet->getStyle('B'.$barishotel)->getAlignment()->setHorizontal('left');
        $sheet->getStyle('D'.$barishotel2.':'.$alphaall.$barishotel2)->getAlignment()->setHorizontal('right');

        $i = (int)$baris + 3;
        $sheet->getStyle('B'.$barispesawat.':'.$alphaall.$i)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $i++;

        $baris = 39;
        $sheet->setCellValue('B'.(int)$baris + 1, '7. Laundry');
        $sheet->setCellValue('B'.(int)$baris + 2, 'Total Biaya Kategori Laundry (PJUM + PB)');

        $sheet->fromArray($exp2, NULL, 'D'.(int)$baris + 2);//20
        $sheet->fromArray($exp7, NULL, 'D'.(int)$baris + 3);//21

        $barispesawat = (int)$baris;//18
        $barishotel = (int)$baris + 1;//19
        $barishotel1 = (int)$baris + 2;//20
        $barishotel2 = (int)$baris + 3;//20
        $sheet->mergeCells('B'.$barispesawat.':'.$alphaall.$barispesawat);
        $sheet->mergeCells('B'.$barishotel.':'.$alphaall.$barishotel);
        $sheet->mergeCells('B'.$barishotel1.':C'.(int)$baris + 3);//21

        $sheet->getStyle('B'.$barishotel.':B'.$barishotel1)->getFont()->setBold( true );
        $sheet->getStyle('B'.$barishotel)->getAlignment()->setHorizontal('left');
        $sheet->getStyle('D'.$barishotel2.':'.$alphaall.$barishotel2)->getAlignment()->setHorizontal('right');

        $i = (int)$baris + 3;
        $sheet->getStyle('B'.$barispesawat.':'.$alphaall.$i)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $i++;

        $baris = 43;
        $sheet->setCellValue('B'.(int)$baris + 1, '8. Lain-lain');
        $sheet->setCellValue('B'.(int)$baris + 2, 'Total Biaya Kategori Lain-lain (PJUM + PB)');

        $sheet->fromArray($exp2, NULL, 'D'.(int)$baris + 2);//20
        $sheet->fromArray($exp8, NULL, 'D'.(int)$baris + 3);//21

        $barispesawat = (int)$baris;//18
        $barishotel = (int)$baris + 1;//19
        $barishotel1 = (int)$baris + 2;//20
        $barishotel2 = (int)$baris + 3;//20
        $sheet->mergeCells('B'.$barispesawat.':'.$alphaall.$barispesawat);
        $sheet->mergeCells('B'.$barishotel.':'.$alphaall.$barishotel);
        $sheet->mergeCells('B'.$barishotel1.':C'.(int)$baris + 3);//21

        $sheet->getStyle('B'.$barishotel.':B'.$barishotel1)->getFont()->setBold( true );
        $sheet->getStyle('B'.$barishotel)->getAlignment()->setHorizontal('left');
        $sheet->getStyle('D'.$barishotel2.':'.$alphaall.$barishotel2)->getAlignment()->setHorizontal('right');

        $i = (int)$baris + 3;
        $sheet->getStyle('B'.$barispesawat.':'.$alphaall.$i)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $i++;

        $baris = 47;
        $sheet->setCellValue('B'.(int)$baris + 1, 'Total Biaya Perjalanan Dinas Luar Negeri');
        $sheet->setCellValue('B'.(int)$baris + 2, 'Total Biaya (PJUM + PB)');

        $sheet->fromArray($exp2, NULL, 'D'.(int)$baris + 2);//20
        $sheet->fromArray($exp13, NULL, 'D'.(int)$baris + 3);//21

        $barispesawat = (int)$baris;//18
        $barishotel = (int)$baris + 1;//19
        $barishotel1 = (int)$baris + 2;//20
        $barishotel2 = (int)$baris + 3;//20
        $sheet->mergeCells('B'.$barispesawat.':'.$alphaall.$barispesawat);
        $sheet->mergeCells('B'.$barishotel.':'.$alphaall.$barishotel);
        $sheet->mergeCells('B'.$barishotel1.':C'.(int)$baris + 3);//21

        $sheet->getStyle('B'.$barishotel.':B'.$barishotel1)->getFont()->setBold( true );
        $sheet->getStyle('D'.$barishotel2.':'.$alphaall.$barishotel2)->getFont()->setBold( true );
        $sheet->getStyle('B'.$barishotel)->getAlignment()->setHorizontal('left');
        $sheet->getStyle('D'.$barishotel2.':'.$alphaall.$barishotel2)->getAlignment()->setHorizontal('right');

        $i = (int)$baris + 3;
        $sheet->getStyle('B'.$barispesawat.':'.$alphaall.$i)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $i++;

        // Biaya Support

        if (empty($biayasupport)) {
            
        } else {
            $baris = 53;//49
            // $barissup = $baristotal + 8; //
            $indexsupport = $countsupport + 57; //39
            // $indextotal = $indexsupport + 2;
            $indexkat = $baris + 2;
            
            $sheet->setCellValue('B'.$baris, 'Biaya Support Perjalanan Dinas Luar Negeri');
            $sheet->setCellValue('B'.$indexkat, 'Tanggal');
            $sheet->setCellValue('C'.$indexkat, 'Kategori');
            $sheet->setCellValue('D'.$indexkat, 'Jumlah Personil');
            $sheet->setCellValue('E'.$indexkat, 'Biaya');
            $sheet->setCellValue('B'.$indexsupport, 'Total Biaya Support');

            $sheet->fromArray($exp10, NULL, 'E'.$indexsupport);

            $sheet->mergeCells('B'.$baris.':'.$alphasup.$baris + 1);
            $sheet->mergeCells('B'.$indexkat.':B'.$indexkat + 1);
            $sheet->mergeCells('C'.$indexkat.':C'.$indexkat + 1);
            $sheet->mergeCells('D'.$indexkat.':D'.$indexkat + 1);
            $sheet->mergeCells('B'.$indexsupport.':D'.$indexsupport);

            $sheet->getStyle('B'.$baris)->getFont()->setBold(true);
            $sheet->getStyle('B'.$indexsupport.':'.$alphasup.$indexsupport)->getFont()->setBold(true);

            $spreadsheet->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);

            for ($i=$baris; $i <= $indexsupport; $i++) { 
                $sheet->getStyle('B'.$baris.':'.$alphasup.$indexsupport)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $sheet->getStyle('D'.$baris.':D'.$i)->getNumberFormat()->setFormatCode('#');
                $i++;
            }

            foreach ($biayasupport as $key => $value) {
                $i = $baris + 3;
                $column = $value['kolom'];
                $row = 49 + (int)$value['baris'];
                $sheet->setCellValueByColumnAndRow($column, $i, $value['kode_valas']);
                $i++;
                for ($j=$baris + 4; $j <= $indexsupport; $j++) {
                    $bia = $value['biaya'];
                    if ($bia == 0) {
                        $bia = null;
                    }
                    $sheet->setCellValueByColumnAndRow(5, $row, $bia);
                    $sheet->getStyle('E'.$row.':E'.$indexsupport)->getAlignment()->setHorizontal('right');
                }
            }

            $row = $baris + 4;
            foreach ($kategorisupport as $key => $value) {
                $sheet->setCellValue('B'.$row, $value['tanggal']);
                $sheet->setCellValue('C'.$row, $value['kategori']);
                $sheet->setCellValue('D'.$row, $value['jumlah_personil']);
                $row++;
            }

            $baris = $indexsupport + 2;
            $sheet->setCellValue('B'.(int)$baris + 1, 'Total Biaya Perjalanan Dinas Luar Negeri');
            $sheet->setCellValue('B'.(int)$baris + 2, 'Total Biaya (PJUM + PB + Support)');

            $sheet->fromArray($exp11, NULL, 'D'.(int)$baris + 2);//20
            $sheet->fromArray($exp12, NULL, 'D'.(int)$baris + 3);//21

            $barispesawat = (int)$baris;//18
            $barishotel = (int)$baris + 1;//19
            $barishotel1 = (int)$baris + 2;//20
            $barishotel2 = (int)$baris + 3;//20
            $sheet->mergeCells('B'.$barispesawat.':'.$alphaall.$barispesawat);
            $sheet->mergeCells('B'.$barishotel.':'.$alphaall.$barishotel);
            $sheet->mergeCells('B'.$barishotel1.':C'.(int)$baris + 3);//21

            $sheet->getStyle('B'.$barishotel.':B'.$barishotel1)->getFont()->setBold( true );
            $sheet->getStyle('D'.$barishotel2.':'.$alphaall.$barishotel2)->getFont()->setBold( true );
            $sheet->getStyle('B'.$barishotel)->getAlignment()->setHorizontal('left');
            $sheet->getStyle('D'.$barishotel2.':'.$alphaall.$barishotel2)->getAlignment()->setHorizontal('right');

            $i = (int)$baris + 3;
            $sheet->getStyle('B'.$barispesawat.':'.$alphaall.$i)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $i++;
        }

        // $sheet->setCellValue('B'.$indextotal, 'Total Biaya Perjalanan Dinas Luar Negeri');//57
        // $sheet->setCellValue('B'.$indextotal + 1, 'Total Biaya (PJUM + PB + Support)');//58

        // $sheet->fromArray($exp11, NULL, 'D'.$indexsupport + 3);//58
        // $sheet->fromArray($exp12, NULL, 'D'.$indexsupport + 4);//59

        // $bottom = $indexsupport + 4;

        // $sheet->getStyle('B'.$bottom.':'.$alphaall.$bottom)->getFont()->setBold( true );

        // $barissel = $indextotal;//57
        // $barissel1 = $indextotal + 1;//58
        // $sheet->mergeCells('B'.$barissel.':'.$alphasel.$barissel);
        // $sheet->mergeCells('B'.$barissel1.':C'.$indextotal + 2);//59

        // $sheet->getStyle('B'.$barissel.':B'.$barissel1)->getFont()->setBold( true );
        // // $sheet->getStyle('B'.$barissel)->getAlignment()->setHorizontal('left');

        // $i = $indextotal + 2;//59
        // $sheet->getStyle('B'.$barissel.':'.$alphasel.$i)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        // $i++;

        $spreadsheet->createSheet();
        $sheet1 = $spreadsheet->setActiveSheetIndex(1);

        // Rename worksheet
        $spreadsheet->getActiveSheet(1)->setTitle('Biaya dalam Rupiah');

        $sheet1->setCellValue('B1', 'PERJALANAN DINAS LUAR NEGERI_'.substr($nik_perso, 0, -1).'_'.$id_transaksi);
        $sheet1->setCellValue('B3', 'Data Karyawan');
        $sheet1->setCellValue('B4', 'Nama Karyawan =>');
        $sheet1->setCellValue('B5', 'Bagian =>');
        $sheet1->setCellValue('B6', 'No PJUM =>');
        $sheet1->setCellValue('B7', 'No PB =>');
        $sheet1->setCellValue('B9', 'Waktu Tugas');
        $sheet1->setCellValue('B10', 'Negara Tujuan =>');
        $sheet1->setCellValue('B11', 'Tanggal Keberangkatan (YYYY-MM-DD) =>');
        $sheet1->setCellValue('B12', 'Tanggal Pulang (YYYY-MM-DD) =>');
        $sheet1->setCellValue('B13', 'Berangkat dari kota =>');
        $sheet1->setCellValue('C4', substr($niknm_perso, 0, -2));
        $sheet1->setCellValue('C5', $bagian['strorgnm']);
        $sheet1->setCellValue('C6', substr($tmp_nopjum, 0, -2));
        $sheet1->setCellValue('C7', substr($tmp_nopb, 0, -2));
        $sheet1->setCellValue('C10', substr($tmp_negara, 0, -2));
        $sheet1->setCellValue('C11', $nik['tanggal_berangkat']);
        $sheet1->setCellValue('C12', $nik['tanggal_pulang']);
        $sheet1->setCellValue('C13', $kota['kota']);

        $sheet1->mergeCells('B1:E1');

        $sheet1->getStyle('B1')->getFont()->setBold( true );
        $sheet1->getStyle('B3')->getFont()->setBold( true );
        $sheet1->getStyle('B9')->getFont()->setBold( true );
        $sheet1->getStyle('D:'.$alphasel)->getAlignment()->setHorizontal('center');
        $sheet1->getStyle('D:'.$alphasel)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet1->getStyle('B:'.$alphaall)->getAlignment()->setHorizontal('center');
        $sheet1->getStyle('B:'.$alphaall)->getAlignment()->setVertical('center');
        
        // $sheet1->getStyle('C4')->getAlignment()->setWrapText(true);
        // $sheet1->getStyle('C10')->getAlignment()->setWrapText(true);
        $sheet1->getColumnDimension('C')->setAutoSize(true);
        $sheet1->getColumnDimension('A')->setVisible(false);
        $spreadsheet->getActiveSheet(1)->getColumnDimension('B')->setWidth(40);
        $spreadsheet->getActiveSheet(1)->getColumnDimension('C')->setWidth(40);
        $spreadsheet->getActiveSheet(1)->getColumnDimension('F')->setWidth(15);

        for ($k = 'D'; $k <= $alphasel; $k++) {
            $spreadsheet->getActiveSheet(1)->getColumnDimension($k)->setWidth(15);
        }

        $totalbiayapesawat = $this->m_biaya->where('id_transaksi', $id_transaksi)->wherenotIn('jenis_biaya', ['Support'])->whereIn('kategori', ['Tiket Pesawat'])->orderBy('id_biaya', 'asc')->select('biaya, id_valas, id_pjum, id_pb')->findAll();
        $totalbiayabagasi = $this->m_biaya->where('id_transaksi', $id_transaksi)->wherenotIn('jenis_biaya', ['Support'])->whereIn('kategori', ['Bagasi Pesawat'])->orderBy('id_biaya', 'asc')->select('biaya, id_valas, id_pjum, id_pb')->findAll();
        $totalbiayaporter = $this->m_biaya->where('id_transaksi', $id_transaksi)->wherenotIn('jenis_biaya', ['Support'])->whereIn('kategori', ['Porter Pesawat'])->orderBy('id_biaya', 'asc')->select('biaya, id_valas, id_pjum, id_pb')->findAll();
        $totalbiayahotel = $this->m_biaya->where('id_transaksi', $id_transaksi)->wherenotIn('jenis_biaya', ['Support'])->whereIn('kategori', ['Hotel'])->orderBy('id_biaya', 'asc')->select('biaya, id_valas, id_pjum, id_pb')->findAll();
        $totalbiayamakan = $this->m_biaya->where('id_transaksi', $id_transaksi)->wherenotIn('jenis_biaya', ['Support'])->whereIn('kategori', ['Makan dan Minum'])->orderBy('id_biaya', 'asc')->select('biaya, id_valas, id_pjum, id_pb')->findAll();
        $totalbiayatrans = $this->m_biaya->where('id_transaksi', $id_transaksi)->wherenotIn('jenis_biaya', ['Support'])->whereIn('kategori', ['Transportasi'])->orderBy('id_biaya', 'asc')->select('biaya, id_valas, id_pjum, id_pb')->findAll();
        $totalbiayalaundry = $this->m_biaya->where('id_transaksi', $id_transaksi)->wherenotIn('jenis_biaya', ['Support'])->whereIn('kategori', ['Laundry'])->orderBy('id_biaya', 'asc')->select('biaya, id_valas, id_pjum, id_pb')->findAll();
        $totalbiayalain = $this->m_biaya->where('id_transaksi', $id_transaksi)->wherenotIn('jenis_biaya', ['Support'])->whereIn('kategori', ['Lain-lain'])->orderBy('id_biaya', 'asc')->select('biaya, id_valas, id_pjum, id_pb')->findAll();
        $totalbiayatot = $this->m_biaya->where('id_transaksi', $id_transaksi)->wherenotIn('jenis_biaya', ['Support'])->wherenotIn('kategori', ['Tukar Uang Masuk', 'Tukar Uang Keluar', 'Kembalian'])->orderBy('id_biaya', 'asc')->select('biaya, id_valas, id_pjum, id_pb')->findAll();
        $totalbiayasel = $this->m_biaya->where('id_transaksi', $id_transaksi)->wherenotIn('kategori', ['Tukar Uang Masuk', 'Tukar Uang Keluar', 'Kembalian'])->orderBy('id_biaya', 'asc')->select('biaya, id_valas, id_pjum, id_pb')->findAll();

        $baris = 15;
        $sheet1->setCellValue('B'.(int)$baris + 1, '1. Tiket Pesawat');
        $sheet1->setCellValue('B'.(int)$baris + 2, 'Total Biaya Kategori Tiket Pesawat (PJUM + PB)');
        $sheet1->setCellValue('D'.(int)$baris + 2, 'IDR');//20
        
        $i = 18;        
        $sum = 0;
        foreach ($totalbiayapesawat as $key => $value) {
            $id_valas = $value['id_valas'];
            $id_pjum = $value['id_pjum'];
            $id_pb = $value['id_pb'];
            $biaya = $value['biaya'];
            if ($id_pjum != null) {
                $kurs = $this->m_kurs->where('id_pjum', $id_pjum)->select('id_valas, kode_valas, tanggal, kurs')->findAll();
                if (empty($kurs)) {
                    $kurs = 1;
                    $biaya = $biaya * $kurs;
                } else if (!empty($kurs)) {
                    foreach ($kurs as $k => $kur) {
                        if ($id_valas != 76 && $kur['id_valas']) {
                            $kurs = $kur['kurs'];
                            $biaya = $biaya * $kurs;
                        }
                    
                        if ($id_valas == 76) {
                            $kurs = 1;
                            $biaya = $biaya * $kurs;
                        }
                    }
                }

            } 
            
            if ($id_pb != null) {
                $kurs = $this->m_kurs->where('id_pb', $id_pb)->select('id_valas, kode_valas, tanggal, kurs')->findAll();
                if (empty($kurs)) {
                    $kurs = 1;
                    $biaya = $biaya * $kurs;
                } else if (!empty($kurs)) {
                    foreach ($kurs as $k => $kur) {
                        if ($id_valas != 76 && $kur['id_valas']) {
                            $kurs = $kur['kurs'];
                            $biaya = $biaya * $kurs;
                        }
                    
                        if ($id_valas == 76) {
                            $kurs = 1;
                            $biaya = $biaya * $kurs;
                        }
                    }
                }
            }

            $sum+= $biaya;
            $sheet1->setCellValue('D'.$i, $sum);
        }

        $barispesawat = (int)$baris;//18
        $barishotel = (int)$baris + 1;//19
        $barishotel1 = (int)$baris + 2;//20
        $barishotel2 = (int)$baris + 3;//20
        $sheet1->mergeCells('B'.$barispesawat.':D'.$barispesawat);
        $sheet1->mergeCells('B'.$barishotel.':D'.$barishotel);
        $sheet1->mergeCells('B'.$barishotel1.':C'.$barishotel2);//21

        $sheet1->getStyle('B'.$barishotel.':B'.$barishotel1)->getFont()->setBold( true );
        $sheet1->getStyle('B'.$barishotel)->getAlignment()->setHorizontal('left');
        $sheet1->getStyle('D'.$barishotel2.':D'.$barishotel2)->getAlignment()->setHorizontal('right');

        $i = (int)$baris + 3;
        $sheet1->getStyle('B'.$barispesawat.':D'.$barishotel2)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $i++;

        $baris = 19;
        $sheet1->setCellValue('B'.(int)$baris + 1, '2. Bagasi Pesawat');
        $sheet1->setCellValue('B'.(int)$baris + 2, 'Total Biaya Kategori Bagasi Pesawat (PJUM + PB)');
        $sheet1->setCellValue('D'.(int)$baris + 2, 'IDR');//20
        
        $i = 22;
        $sum = 0;
        foreach ($totalbiayabagasi as $key => $value) {
            $id_valas = $value['id_valas'];
            $id_pjum = $value['id_pjum'];
            $id_pb = $value['id_pb'];
            $biaya = $value['biaya'];
            if ($id_pjum != null) {
                $kurs = $this->m_kurs->where('id_pjum', $id_pjum)->select('id_valas, kode_valas, tanggal, kurs')->findAll();
                if (empty($kurs)) {
                    $kurs = 1;
                    $biaya = $biaya * $kurs;
                } else if (!empty($kurs)) {
                    foreach ($kurs as $k => $kur) {
                        if ($id_valas != 76 && $kur['id_valas']) {
                            $kurs = $kur['kurs'];
                            $biaya = $biaya * $kurs;
                        }
                    
                        if ($id_valas == 76) {
                            $kurs = 1;
                            $biaya = $biaya * $kurs;
                        }
                    }
                }

            } 
            
            if ($id_pb != null) {
                $kurs = $this->m_kurs->where('id_pb', $id_pb)->select('id_valas, kode_valas, tanggal, kurs')->findAll();
                if (empty($kurs)) {
                    $kurs = 1;
                    $biaya = $biaya * $kurs;
                } else if (!empty($kurs)) {
                    foreach ($kurs as $k => $kur) {
                        if ($id_valas != 76 && $kur['id_valas']) {
                            $kurs = $kur['kurs'];
                            $biaya = $biaya * $kurs;
                        }
                    
                        if ($id_valas == 76) {
                            $kurs = 1;
                            $biaya = $biaya * $kurs;
                        }
                    }
                }
            }

            $sum+= $biaya;
            $sheet1->setCellValue('D'.$i, $sum);
        }

        $barispesawat = (int)$baris;//18
        $barishotel = (int)$baris + 1;//19
        $barishotel1 = (int)$baris + 2;//20
        $barishotel2 = (int)$baris + 3;//20
        $sheet1->mergeCells('B'.$barispesawat.':D'.$barispesawat);
        $sheet1->mergeCells('B'.$barishotel.':D'.$barishotel);
        $sheet1->mergeCells('B'.$barishotel1.':C'.$barishotel2);//21

        $sheet1->getStyle('B'.$barishotel.':B'.$barishotel1)->getFont()->setBold( true );
        $sheet1->getStyle('B'.$barishotel)->getAlignment()->setHorizontal('left');
        $sheet1->getStyle('D'.$barishotel2.':D'.$barishotel2)->getAlignment()->setHorizontal('right');

        $i = (int)$baris + 3;
        $sheet1->getStyle('B'.$barispesawat.':D'.$barishotel2)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $i++;

        $baris = 23;
        $sheet1->setCellValue('B'.(int)$baris + 1, '3. Porter Pesawat');
        $sheet1->setCellValue('B'.(int)$baris + 2, 'Total Biaya Kategori Porter Pesawat (PJUM + PB)');
        $sheet1->setCellValue('D'.(int)$baris + 2, 'IDR');//20
        
        $i = 26;
        $sum = 0;
        foreach ($totalbiayaporter as $key => $value) {
            $id_valas = $value['id_valas'];
            $id_pjum = $value['id_pjum'];
            $id_pb = $value['id_pb'];
            $biaya = $value['biaya'];
            if ($id_pjum != null) {
                $kurs = $this->m_kurs->where('id_pjum', $id_pjum)->select('id_valas, kode_valas, tanggal, kurs')->findAll();
                if (empty($kurs)) {
                    $kurs = 1;
                    $biaya = $biaya * $kurs;
                } else if (!empty($kurs)) {
                    foreach ($kurs as $k => $kur) {
                        if ($id_valas != 76 && $kur['id_valas']) {
                            $kurs = $kur['kurs'];
                            $biaya = $biaya * $kurs;
                        }
                    
                        if ($id_valas == 76) {
                            $kurs = 1;
                            $biaya = $biaya * $kurs;
                        }
                    }
                }

            } 
            
            if ($id_pb != null) {
                $kurs = $this->m_kurs->where('id_pb', $id_pb)->select('id_valas, kode_valas, tanggal, kurs')->findAll();
                if (empty($kurs)) {
                    $kurs = 1;
                    $biaya = $biaya * $kurs;
                } else if (!empty($kurs)) {
                    foreach ($kurs as $k => $kur) {
                        if ($id_valas != 76 && $kur['id_valas']) {
                            $kurs = $kur['kurs'];
                            $biaya = $biaya * $kurs;
                        }
                    
                        if ($id_valas == 76) {
                            $kurs = 1;
                            $biaya = $biaya * $kurs;
                        }
                    }
                }
            }

            $sum+= $biaya;
            $sheet1->setCellValue('D'.$i, $sum);
        }

        $barispesawat = (int)$baris;//18
        $barishotel = (int)$baris + 1;//19
        $barishotel1 = (int)$baris + 2;//20
        $barishotel2 = (int)$baris + 3;//20
        $sheet1->mergeCells('B'.$barispesawat.':D'.$barispesawat);
        $sheet1->mergeCells('B'.$barishotel.':D'.$barishotel);
        $sheet1->mergeCells('B'.$barishotel1.':C'.$barishotel2);//21

        $sheet1->getStyle('B'.$barishotel.':B'.$barishotel1)->getFont()->setBold( true );
        $sheet1->getStyle('B'.$barishotel)->getAlignment()->setHorizontal('left');
        $sheet1->getStyle('D'.$barishotel2.':D'.$barishotel2)->getAlignment()->setHorizontal('right');

        $i = (int)$baris + 3;
        $sheet1->getStyle('B'.$barispesawat.':D'.$barishotel2)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $i++;

        $baris = 27;
        $sheet1->setCellValue('B'.(int)$baris + 1, '4. Hotel');
        $sheet1->setCellValue('B'.(int)$baris + 2, 'Total Biaya Kategori Hotel (PJUM + PB)');
        $sheet1->setCellValue('D'.(int)$baris + 2, 'IDR');//20
        
        $i = 30;
        $sum = 0;
        foreach ($totalbiayahotel as $key => $value) {
            $id_valas = $value['id_valas'];
            $id_pjum = $value['id_pjum'];
            $id_pb = $value['id_pb'];
            $biaya = $value['biaya'];
            if ($id_pjum != null) {
                $kurs = $this->m_kurs->where('id_pjum', $id_pjum)->select('id_valas, kode_valas, tanggal, kurs')->findAll();
                if (empty($kurs)) {
                    $kurs = 1;
                    $biaya = $biaya * $kurs;
                } else if (!empty($kurs)) {
                    foreach ($kurs as $k => $kur) {
                        if ($id_valas != 76 && $kur['id_valas']) {
                            $kurs = $kur['kurs'];
                            $biaya = $biaya * $kurs;
                        }
                    
                        if ($id_valas == 76) {
                            $kurs = 1;
                            $biaya = $biaya * $kurs;
                        }
                    }
                }

            } 
            
            if ($id_pb != null) {
                $kurs = $this->m_kurs->where('id_pb', $id_pb)->select('id_valas, kode_valas, tanggal, kurs')->findAll();
                if (empty($kurs)) {
                    $kurs = 1;
                    $biaya = $biaya * $kurs;
                } else if (!empty($kurs)) {
                    foreach ($kurs as $k => $kur) {
                        if ($id_valas != 76 && $kur['id_valas']) {
                            $kurs = $kur['kurs'];
                            $biaya = $biaya * $kurs;
                        }
                    
                        if ($id_valas == 76) {
                            $kurs = 1;
                            $biaya = $biaya * $kurs;
                        }
                    }
                }
            }

            $sum+= $biaya;
            $sheet1->setCellValue('D'.$i, $sum);
        }

        $barispesawat = (int)$baris;//18
        $barishotel = (int)$baris + 1;//19
        $barishotel1 = (int)$baris + 2;//20
        $barishotel2 = (int)$baris + 3;//20
        $sheet1->mergeCells('B'.$barispesawat.':D'.$barispesawat);
        $sheet1->mergeCells('B'.$barishotel.':D'.$barishotel);
        $sheet1->mergeCells('B'.$barishotel1.':C'.$barishotel2);//21

        $sheet1->getStyle('B'.$barishotel.':B'.$barishotel1)->getFont()->setBold( true );
        $sheet1->getStyle('B'.$barishotel)->getAlignment()->setHorizontal('left');
        $sheet1->getStyle('D'.$barishotel2.':D'.$barishotel2)->getAlignment()->setHorizontal('right');

        $i = (int)$baris + 3;
        $sheet1->getStyle('B'.$barispesawat.':D'.$barishotel2)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $i++;

        $baris = 31;
        $sheet1->setCellValue('B'.(int)$baris + 1, '5. Makan dan Minum');
        $sheet1->setCellValue('B'.(int)$baris + 2, 'Total Biaya Kategori Makan dan Minum (PJUM + PB)');
        $sheet1->setCellValue('D'.(int)$baris + 2, 'IDR');//20
        
        $i = 34;
        $sum = 0;
        foreach ($totalbiayamakan as $key => $value) {
            $id_valas = $value['id_valas'];
            $id_pjum = $value['id_pjum'];
            $id_pb = $value['id_pb'];
            $biaya = $value['biaya'];
            if ($id_pjum != null) {
                $kurs = $this->m_kurs->where('id_pjum', $id_pjum)->select('id_valas, kode_valas, tanggal, kurs')->findAll();
                if (empty($kurs)) {
                    $kurs = 1;
                    $biaya = $biaya * $kurs;
                } else if (!empty($kurs)) {
                    foreach ($kurs as $k => $kur) {
                        if ($id_valas != 76 && $kur['id_valas']) {
                            $kurs = $kur['kurs'];
                            $biaya = $biaya * $kurs;
                        }
                    
                        if ($id_valas == 76) {
                            $kurs = 1;
                            $biaya = $biaya * $kurs;
                        }
                    }
                }

            } 
            
            if ($id_pb != null) {
                $kurs = $this->m_kurs->where('id_pb', $id_pb)->select('id_valas, kode_valas, tanggal, kurs')->findAll();
                if (empty($kurs)) {
                    $kurs = 1;
                    $biaya = $biaya * $kurs;
                } else if (!empty($kurs)) {
                    foreach ($kurs as $k => $kur) {
                        if ($id_valas != 76 && $kur['id_valas']) {
                            $kurs = $kur['kurs'];
                            $biaya = $biaya * $kurs;
                        }
                    
                        if ($id_valas == 76) {
                            $kurs = 1;
                            $biaya = $biaya * $kurs;
                        }
                    }
                }
            }

            $sum+= $biaya;
            $sheet1->setCellValue('D'.$i, $sum);
        }

        $barispesawat = (int)$baris;//18
        $barishotel = (int)$baris + 1;//19
        $barishotel1 = (int)$baris + 2;//20
        $barishotel2 = (int)$baris + 3;//20
        $sheet1->mergeCells('B'.$barispesawat.':D'.$barispesawat);
        $sheet1->mergeCells('B'.$barishotel.':D'.$barishotel);
        $sheet1->mergeCells('B'.$barishotel1.':C'.$barishotel2);//21

        $sheet1->getStyle('B'.$barishotel.':B'.$barishotel1)->getFont()->setBold( true );
        $sheet1->getStyle('B'.$barishotel)->getAlignment()->setHorizontal('left');
        $sheet1->getStyle('D'.$barishotel2.':D'.$barishotel2)->getAlignment()->setHorizontal('right');

        $i = (int)$baris + 3;
        $sheet1->getStyle('B'.$barispesawat.':D'.$barishotel2)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $i++;

        $baris = 35;
        $sheet1->setCellValue('B'.(int)$baris + 1, '6. Transportasi');
        $sheet1->setCellValue('B'.(int)$baris + 2, 'Total Biaya Kategori Transportasi (PJUM + PB)');
        $sheet1->setCellValue('D'.(int)$baris + 2, 'IDR');//20
        
        $i = 38;
        $sum = 0;
        foreach ($totalbiayatrans as $key => $value) {
            $id_valas = $value['id_valas'];
            $id_pjum = $value['id_pjum'];
            $id_pb = $value['id_pb'];
            $biaya = $value['biaya'];
            if ($id_pjum != null) {
                $kurs = $this->m_kurs->where('id_pjum', $id_pjum)->select('id_valas, kode_valas, tanggal, kurs')->findAll();
                if (empty($kurs)) {
                    $kurs = 1;
                    $biaya = $biaya * $kurs;
                } else if (!empty($kurs)) {
                    foreach ($kurs as $k => $kur) {
                        if ($id_valas != 76 && $kur['id_valas']) {
                            $kurs = $kur['kurs'];
                            $biaya = $biaya * $kurs;
                        }
                    
                        if ($id_valas == 76) {
                            $kurs = 1;
                            $biaya = $biaya * $kurs;
                        }
                    }
                }

            } 
            
            if ($id_pb != null) {
                $kurs = $this->m_kurs->where('id_pb', $id_pb)->select('id_valas, kode_valas, tanggal, kurs')->findAll();
                if (empty($kurs)) {
                    $kurs = 1;
                    $biaya = $biaya * $kurs;
                } else if (!empty($kurs)) {
                    foreach ($kurs as $k => $kur) {
                        if ($id_valas != 76 && $kur['id_valas']) {
                            $kurs = $kur['kurs'];
                            $biaya = $biaya * $kurs;
                        }
                    
                        if ($id_valas == 76) {
                            $kurs = 1;
                            $biaya = $biaya * $kurs;
                        }
                    }
                }
            }

            $sum+= $biaya;
            $sheet1->setCellValue('D'.$i, $sum);
        }

        $barispesawat = (int)$baris;//18
        $barishotel = (int)$baris + 1;//19
        $barishotel1 = (int)$baris + 2;//20
        $barishotel2 = (int)$baris + 3;//20
        $sheet1->mergeCells('B'.$barispesawat.':D'.$barispesawat);
        $sheet1->mergeCells('B'.$barishotel.':D'.$barishotel);
        $sheet1->mergeCells('B'.$barishotel1.':C'.$barishotel2);//21

        $sheet1->getStyle('B'.$barishotel.':B'.$barishotel1)->getFont()->setBold( true );
        $sheet1->getStyle('B'.$barishotel)->getAlignment()->setHorizontal('left');
        $sheet1->getStyle('D'.$barishotel2.':D'.$barishotel2)->getAlignment()->setHorizontal('right');

        $i = (int)$baris + 3;
        $sheet1->getStyle('B'.$barispesawat.':D'.$barishotel2)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $i++;

        $baris = 39;
        $sheet1->setCellValue('B'.(int)$baris + 1, '7. Laundry');
        $sheet1->setCellValue('B'.(int)$baris + 2, 'Total Biaya Kategori Laundry (PJUM + PB)');
        $sheet1->setCellValue('D'.(int)$baris + 2, 'IDR');//20
        
        $i = 42;
        $sum = 0;
        foreach ($totalbiayalaundry as $key => $value) {
            $id_valas = $value['id_valas'];
            $id_pjum = $value['id_pjum'];
            $id_pb = $value['id_pb'];
            $biaya = $value['biaya'];
            if ($id_pjum != null) {
                $kurs = $this->m_kurs->where('id_pjum', $id_pjum)->select('id_valas, kode_valas, tanggal, kurs')->findAll();
                if (empty($kurs)) {
                    $kurs = 1;
                    $biaya = $biaya * $kurs;
                } else if (!empty($kurs)) {
                    foreach ($kurs as $k => $kur) {
                        if ($id_valas != 76 && $kur['id_valas']) {
                            $kurs = $kur['kurs'];
                            $biaya = $biaya * $kurs;
                        }
                    
                        if ($id_valas == 76) {
                            $kurs = 1;
                            $biaya = $biaya * $kurs;
                        }
                    }
                }

            } 
            
            if ($id_pb != null) {
                $kurs = $this->m_kurs->where('id_pb', $id_pb)->select('id_valas, kode_valas, tanggal, kurs')->findAll();
                if (empty($kurs)) {
                    $kurs = 1;
                    $biaya = $biaya * $kurs;
                } else if (!empty($kurs)) {
                    foreach ($kurs as $k => $kur) {
                        if ($id_valas != 76 && $kur['id_valas']) {
                            $kurs = $kur['kurs'];
                            $biaya = $biaya * $kurs;
                        }
                    
                        if ($id_valas == 76) {
                            $kurs = 1;
                            $biaya = $biaya * $kurs;
                        }
                    }
                }
            }

            $sum+= $biaya;
            $sheet1->setCellValue('D'.$i, $sum);
        }

        $barispesawat = (int)$baris;//18
        $barishotel = (int)$baris + 1;//19
        $barishotel1 = (int)$baris + 2;//20
        $barishotel2 = (int)$baris + 3;//20
        $sheet1->mergeCells('B'.$barispesawat.':D'.$barispesawat);
        $sheet1->mergeCells('B'.$barishotel.':D'.$barishotel);
        $sheet1->mergeCells('B'.$barishotel1.':C'.$barishotel2);//21

        $sheet1->getStyle('B'.$barishotel.':B'.$barishotel1)->getFont()->setBold( true );
        $sheet1->getStyle('B'.$barishotel)->getAlignment()->setHorizontal('left');
        $sheet1->getStyle('D'.$barishotel2.':D'.$barishotel2)->getAlignment()->setHorizontal('right');

        $i = (int)$baris + 3;
        $sheet1->getStyle('B'.$barispesawat.':D'.$barishotel2)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $i++;

        $baris = 43;
        $sheet1->setCellValue('B'.(int)$baris + 1, '8. Lain-lain');
        $sheet1->setCellValue('B'.(int)$baris + 2, 'Total Biaya Kategori Lain-lain (PJUM + PB)');
        $sheet1->setCellValue('D'.(int)$baris + 2, 'IDR');//20
        
        $i = 46;
        $sum = 0;
        foreach ($totalbiayalain as $key => $value) {
            $id_valas = $value['id_valas'];
            $id_pjum = $value['id_pjum'];
            $id_pb = $value['id_pb'];
            $biaya = $value['biaya'];
            if ($id_pjum != null) {
                $kurs = $this->m_kurs->where('id_pjum', $id_pjum)->select('id_valas, kode_valas, tanggal, kurs')->findAll();
                if (empty($kurs)) {
                    $kurs = 1;
                    $biaya = $biaya * $kurs;
                } else if (!empty($kurs)) {
                    foreach ($kurs as $k => $kur) {
                        if ($id_valas != 76 && $kur['id_valas']) {
                            $kurs = $kur['kurs'];
                            $biaya = $biaya * $kurs;
                        }
                    
                        if ($id_valas == 76) {
                            $kurs = 1;
                            $biaya = $biaya * $kurs;
                        }
                    }
                }

            } 
            
            if ($id_pb != null) {
                $kurs = $this->m_kurs->where('id_pb', $id_pb)->select('id_valas, kode_valas, tanggal, kurs')->findAll();
                if (empty($kurs)) {
                    $kurs = 1;
                    $biaya = $biaya * $kurs;
                } else if (!empty($kurs)) {
                    foreach ($kurs as $k => $kur) {
                        if ($id_valas != 76 && $kur['id_valas']) {
                            $kurs = $kur['kurs'];
                            $biaya = $biaya * $kurs;
                        }
                    
                        if ($id_valas == 76) {
                            $kurs = 1;
                            $biaya = $biaya * $kurs;
                        }
                    }
                }
            }

            $sum+= $biaya;
            $sheet1->setCellValue('D'.$i, $sum);
        }

        $barispesawat = (int)$baris;//18
        $barishotel = (int)$baris + 1;//19
        $barishotel1 = (int)$baris + 2;//20
        $barishotel2 = (int)$baris + 3;//20
        $sheet1->mergeCells('B'.$barispesawat.':D'.$barispesawat);
        $sheet1->mergeCells('B'.$barishotel.':D'.$barishotel);
        $sheet1->mergeCells('B'.$barishotel1.':C'.$barishotel2);//21

        $sheet1->getStyle('B'.$barishotel.':B'.$barishotel1)->getFont()->setBold( true );
        $sheet1->getStyle('B'.$barishotel)->getAlignment()->setHorizontal('left');
        $sheet1->getStyle('D'.$barishotel2.':D'.$barishotel2)->getAlignment()->setHorizontal('right');

        $i = (int)$baris + 3;
        $sheet1->getStyle('B'.$barispesawat.':D'.$barishotel2)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $i++;

        $baris = 47;
        $sheet1->setCellValue('B'.(int)$baris + 1, 'Total Biaya Perjalanan Dinas Luar Negeri');
        $sheet1->setCellValue('B'.(int)$baris + 2, 'Total Biaya (PJUM + PB)');
        $sheet1->setCellValue('D'.(int)$baris + 2, 'IDR');//20
        
        $i = 50;
        $sum = 0;
        foreach ($totalbiayatot as $key => $value) {
            $id_valas = $value['id_valas'];
            $id_pjum = $value['id_pjum'];
            $id_pb = $value['id_pb'];
            $biaya = $value['biaya'];
            if ($id_pjum != null) {
                $kurs = $this->m_kurs->where('id_pjum', $id_pjum)->select('id_valas, kode_valas, tanggal, kurs')->findAll();
                if (empty($kurs)) {
                    $kurs = 1;
                    $biaya = $biaya * $kurs;
                } else if (!empty($kurs)) {
                    foreach ($kurs as $k => $kur) {
                        if ($id_valas != 76 && $kur['id_valas']) {
                            $kurs = $kur['kurs'];
                            $biaya = $biaya * $kurs;
                        }
                    
                        if ($id_valas == 76) {
                            $kurs = 1;
                            $biaya = $biaya * $kurs;
                        }
                    }
                }

            } 
            
            if ($id_pb != null) {
                $kurs = $this->m_kurs->where('id_pb', $id_pb)->select('id_valas, kode_valas, tanggal, kurs')->findAll();
                if (empty($kurs)) {
                    $kurs = 1;
                    $biaya = $biaya * $kurs;
                } else if (!empty($kurs)) {
                    foreach ($kurs as $k => $kur) {
                        if ($id_valas != 76 && $kur['id_valas']) {
                            $kurs = $kur['kurs'];
                            $biaya = $biaya * $kurs;
                        }
                    
                        if ($id_valas == 76) {
                            $kurs = 1;
                            $biaya = $biaya * $kurs;
                        }
                    }
                }
            }

            $sum+= $biaya;
            $sheet1->setCellValue('D'.$i, $sum);
        }

        $barispesawat = (int)$baris;//18
        $barishotel = (int)$baris + 1;//19
        $barishotel1 = (int)$baris + 2;//20
        $barishotel2 = (int)$baris + 3;//20
        $sheet1->mergeCells('B'.$barispesawat.':D'.$barispesawat);
        $sheet1->mergeCells('B'.$barishotel.':D'.$barishotel);
        $sheet1->mergeCells('B'.$barishotel1.':C'.$barishotel2);//21

        $sheet1->getStyle('B'.$barishotel.':B'.$barishotel1)->getFont()->setBold( true );
        $sheet1->getStyle('D50')->getFont()->setBold( true );
        $sheet1->getStyle('B'.$barishotel)->getAlignment()->setHorizontal('left');
        $sheet1->getStyle('D'.$barishotel2.':D'.$barishotel2)->getAlignment()->setHorizontal('right');

        $i = (int)$baris + 3;
        $sheet1->getStyle('B'.$barispesawat.':D'.$barishotel2)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $i++;

        // Biaya Support

        if (empty($biayasupport)) {
            
        } else {
            $baris = 53;//49
            // $barissup = $baristotal + 8; //
            $indexsupport = $countsupport + 57; //39
            // $indextotal = $indexsupport + 2;
            $indexkat = $baris + 2;
            
            $sheet1->setCellValue('B'.$baris, 'Biaya Support Perjalanan Dinas Luar Negeri');
            $sheet1->setCellValue('B'.$indexkat, 'Tanggal');
            $sheet1->setCellValue('C'.$indexkat, 'Kategori');
            $sheet1->setCellValue('D'.$indexkat, 'Jumlah Personil');
            $sheet1->setCellValue('E'.$indexkat, 'Biaya');
            $sheet1->setCellValue('B'.$indexsupport, 'Total Biaya Support');

            $sheet1->fromArray($exp10, NULL, 'E'.$indexsupport);

            $sheet1->mergeCells('B'.$baris.':'.$alphasup.$baris + 1);
            $sheet1->mergeCells('B'.$indexkat.':B'.$indexkat + 1);
            $sheet1->mergeCells('C'.$indexkat.':C'.$indexkat + 1);
            $sheet1->mergeCells('D'.$indexkat.':D'.$indexkat + 1);
            $sheet1->mergeCells('B'.$indexsupport.':D'.$indexsupport);

            $sheet1->getStyle('B'.$baris)->getFont()->setBold(true);
            $sheet1->getStyle('B'.$indexsupport.':'.$alphasup.$indexsupport)->getFont()->setBold(true);

            $spreadsheet->getActiveSheet(1)->getColumnDimension('D')->setAutoSize(true);

            for ($i=$baris; $i <= $indexsupport; $i++) { 
                $sheet1->getStyle('B'.$baris.':'.$alphasup.$indexsupport)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $sheet1->getStyle('D'.$baris.':D'.$i)->getNumberFormat()->setFormatCode('#');
                $i++;
            }

            foreach ($biayasupport as $key => $value) {
                $i = $baris + 3;
                $column = $value['kolom'];
                $row = 49 + (int)$value['baris'];
                $sheet1->setCellValueByColumnAndRow($column, $i, $value['kode_valas']);
                $i++;
                for ($j=$baris + 4; $j <= $indexsupport; $j++) {
                    $bia = $value['biaya'];
                    if ($bia == 0) {
                        $bia = null;
                    }
                    $sheet1->setCellValueByColumnAndRow(5, $row, $bia);
                    $sheet1->getStyle('E'.$row.':E'.$indexsupport)->getAlignment()->setHorizontal('right');
                }
            }

            $row = $baris + 4;
            foreach ($kategorisupport as $key => $value) {
                $sheet1->setCellValue('B'.$row, $value['tanggal']);
                $sheet1->setCellValue('C'.$row, $value['kategori']);
                $sheet1->setCellValue('D'.$row, $value['jumlah_personil']);
                $row++;
            }

            $baris = $indexsupport + 2;
            $sheet1->setCellValue('B'.(int)$baris + 1, 'Total Biaya Perjalanan Dinas Luar Negeri');
            $sheet1->setCellValue('B'.(int)$baris + 2, 'Total Biaya (PJUM + PB + Support)');
            $sheet1->setCellValue('D'.(int)$baris + 2, 'IDR');//20
        
            $i = $indexsupport + 5;
            $sum = 0;
            foreach ($totalbiayasel as $key => $value) {
                $id_valas = $value['id_valas'];
                $id_pjum = $value['id_pjum'];
                $id_pb = $value['id_pb'];
                $biaya = $value['biaya'];
                if ($id_pjum != null) {
                    $kurs = $this->m_kurs->where('id_pjum', $id_pjum)->select('id_valas, kode_valas, tanggal, kurs')->findAll();
                    if (empty($kurs)) {
                        $kurs = 1;
                        $biaya = $biaya * $kurs;
                    } else if (!empty($kurs)) {
                        foreach ($kurs as $k => $kur) {
                            if ($id_valas != 76 && $kur['id_valas']) {
                                $kurs = $kur['kurs'];
                                $biaya = $biaya * $kurs;
                            }
                        
                            if ($id_valas == 76) {
                                $kurs = 1;
                                $biaya = $biaya * $kurs;
                            }
                        }
                    }
    
                } 
                
                if ($id_pb != null) {
                    $kurs = $this->m_kurs->where('id_pb', $id_pb)->select('id_valas, kode_valas, tanggal, kurs')->findAll();
                    if (empty($kurs)) {
                        $kurs = 1;
                        $biaya = $biaya * $kurs;
                    } else if (!empty($kurs)) {
                        foreach ($kurs as $k => $kur) {
                            if ($id_valas != 76 && $kur['id_valas']) {
                                $kurs = $kur['kurs'];
                                $biaya = $biaya * $kurs;
                            }
                        
                            if ($id_valas == 76) {
                                $kurs = 1;
                                $biaya = $biaya * $kurs;
                            }
                        }
                    }
                }
    
                $sum+= $biaya;
                $sheet1->setCellValue('D'.$i, $sum);
            }
    
            $barispesawat = (int)$baris;//18
            $barishotel = (int)$baris + 1;//19
            $barishotel1 = (int)$baris + 2;//20
            $barishotel2 = (int)$baris + 3;//20
            $sheet1->mergeCells('B'.$barispesawat.':D'.$barispesawat);
            $sheet1->mergeCells('B'.$barishotel.':D'.$barishotel);
            $sheet1->mergeCells('B'.$barishotel1.':C'.$barishotel2);//21
    
            $sheet1->getStyle('B'.$barishotel.':B'.$barishotel1)->getFont()->setBold( true );
            $sheet1->getStyle('D'.$indexsupport + 5)->getFont()->setBold( true );
            $sheet1->getStyle('B'.$barishotel)->getAlignment()->setHorizontal('left');
            $sheet1->getStyle('D'.$barishotel2.':D'.$barishotel2)->getAlignment()->setHorizontal('right');
    
            $i = (int)$baris + 3;
            $sheet1->getStyle('B'.$barispesawat.':D'.$barishotel2)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $i++;
        }

        // $spreadsheet->setActiveSheetIndex(0);

        $writer = new Xls($spreadsheet);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename=Report Biaya Perjalanan Dinas Luar Negeri/'.substr($nik_perso, 0, -1).'/'.$id_transaksi.'.xls');
        $writer->save("php://output");
        // $writer->save('Report Biaya '.substr($nik_perso, 0, -1).'.xls');
        // return redirect()->to('dashboard/'.$id_transaksi);
    }
}