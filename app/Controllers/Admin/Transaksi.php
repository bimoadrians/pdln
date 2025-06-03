<?php
namespace App\Controllers\Admin;

date_default_timezone_set("Asia/Jakarta");

use App\Controllers\BaseController;
use PhpOffice\PhpSpreadsheet\Calculation\Calculation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use App\Models\TransaksiModel;
use App\Models\Am21Model;
use App\Models\Am21bModel;
use App\Models\Bm06Model;
use App\Models\NegaraModel;
use App\Models\KotaModel;
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

class Transaksi extends BaseController
{
    public function __construct()
    {
        $this->validation = \Config\Services::validation();
        $session = \Config\Services::session();

        $this->m_admin = new Am21bModel();
        $this->m_am21 = new Am21Model();
        $this->m_bm06 = new Bm06Model();
        $this->m_id = new TransaksiModel();
        $this->m_negara = new NegaraModel();
        $this->m_kota = new KotaModel();
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

        $pager = \Config\Services::pager();
        helper('global_fungsi_helper');
        helper('url');
    }

    public function transaksi()
    {
        $strorg = session()->get('strorg');
        $nik = session()->get('akun_nik');
        $niknm = session()->get('niknm');
        $role = session()->get('akun_role');


        if ($role == 'admin') {
            $submit = $this->m_id->where('SUBSTRING(strorg, 1, 4)', substr($strorg, 0, 4))->orderBy('submit_pjum', 'desc')->orderBy('submit_pb', 'desc')->select('submit_pjum, submit_pb')->first();
        } else if ($role == 'user') {
            // $submit = $this->m_id->where('nik', $nik)->where('SUBSTRING(strorg, 1, 4)', substr($strorg, 0, 4))->findAll();
            $submit = $this->m_id->where('strorg', $strorg)->orderBy('submit_pjum', 'desc')->orderBy('submit_pb', 'desc')->select('submit_pjum, submit_pb')->first();
        } else if($role == 'treasury' || $role == 'gs'){
            $submit = null;
        }

        if($this->request->getVar('aksi') == 'hapus' && $this->request->getVar('id_transaksi')) {
            $dataPost = $this->m_id->getPostId($this->request->getVar('id_transaksi'), substr($strorg, 0, 4));
            if($dataPost['id_transaksi']) {//memastikan bahwa ada data
                $aksi = $this->m_id->deletePostId($this->request->getVar('id_transaksi'));
                if($aksi == true) {
                    $this->m_id->query('ALTER TABLE transaksi AUTO_INCREMENT 1');
                    $this->m_personil->query('ALTER TABLE personil AUTO_INCREMENT 1');
                    $this->m_negara_tujuan->query('ALTER TABLE negaratujuan AUTO_INCREMENT 1');
                    $this->m_pum->query('ALTER TABLE pum AUTO_INCREMENT 1');
                    $this->m_pjum->query('ALTER TABLE pjum AUTO_INCREMENT 1');
                    $this->m_pb->query('ALTER TABLE pb AUTO_INCREMENT 1');
                    $this->m_kurs->query('ALTER TABLE kurs AUTO_INCREMENT 1');
                    $this->m_kategori->query('ALTER TABLE kategori AUTO_INCREMENT 1');
                    $this->m_biaya->query('ALTER TABLE biaya AUTO_INCREMENT 1');
                    $this->m_log_email->query('ALTER TABLE log_email AUTO_INCREMENT 1');
                    session()->setFlashdata('success', "ID Transaksi berhasil dihapus");
                } else {
                    session()->setFlashdata('warning', ['ID Transaksi gagal dihapus']);
                }
            }
            return redirect()->to("transaksi");
        }

        if($this->request->getMethod() == 'post') {
            $tanggal_awal = $this->request->getVar('tanggal_awal');
            $tanggal_akhir = $this->request->getVar('tanggal_akhir');
            $strorgnm = $this->request->getVar('strorgnm');
            $negara = $this->request->getVar('negara');
            $kategori = $this->request->getVar('kategori');

            if (empty($strorgnm)) {
                $strorgnm = null;
            } else {
                $stro = $this->m_bm06->whereIn('strorgnm', $strorgnm)->select('strorg')->findAll();

                $str = implode(' ', array_map(function ($entry) {
                    return ($entry[key($entry)]);
                }, $stro));
    
                $strorg = explode(' ', $str);
            }

            //Memilih tanggal dan bagian untuk menentukan id transaksi
            if (empty($strorgnm)) { //semua bagian, semua negara, semua kategori
                $id = $this->m_id->tanggalsemua($tanggal_awal, $tanggal_akhir, substr($strorg, 0, 4));
            } else if (!empty($strorgnm)) { //milih bagian, semua negara, semua kategori
                $id = $this->m_id->whereIn('strorg', $strorg)->Where('tanggal_berangkat >=', $tanggal_awal)->Where('tanggal_pulang <=', $tanggal_akhir)->Where('submit_pjum', 4)->Where('submit_pb', 4)->findAll();
            }

            $arr_id = implode(' ', array_map(function ($entry) {
                return ($entry[key($entry)]);
            }, $id));

            if(empty($arr_id)) {
                session()->setFlashdata('warning', ['Data tidak ditemukan']);
                return redirect()->to('transaksi');
            }

            $id_tran = explode(' ', $arr_id);
            $id_trans = array_unique($id_tran);

            $id_transaksi = array_values($id_trans);

            if (empty($negara) && empty($kategori)) { //semua negara, semua kategori
                $kategori1 = $this->m_kategori->whereIn('id_transaksi', $id_transaksi)->wherenotIn('jenis_biaya', ['Support'])->wherenotIn('kategori', ['Tukar Uang Masuk', 'Tukar Uang Keluar', 'Kembalian'])->orderBy('tanggal', 'asc')->findAll();
                $biaya = $this->m_biaya->whereIn('id_transaksi', $id_transaksi)->wherenotIn('jenis_biaya', ['Support'])->wherenotIn('kategori', ['Tukar Uang Masuk', 'Tukar Uang Keluar', 'Kembalian'])->orderBy('tanggal', 'asc')->findAll();
                $valas = $this->m_biaya->whereIn('id_transaksi', $id_transaksi)->wherenotIn('jenis_biaya', ['Support'])->wherenotIn('kategori', ['Tukar Uang Masuk', 'Tukar Uang Keluar', 'Kembalian'])->orderBy('tanggal', 'asc')->select('id_valas')->findAll();
                $valas1 = $this->m_biaya->whereIn('id_transaksi', $id_transaksi)->wherenotIn('jenis_biaya', ['Support'])->wherenotIn('kategori', ['Tukar Uang Masuk', 'Tukar Uang Keluar', 'Kembalian'])->orderBy('tanggal', 'asc')->select('kode_valas')->findAll();
                $valas2 = $this->m_biaya->whereIn('id_transaksi', $id_transaksi)->wherenotIn('jenis_biaya', ['Support'])->wherenotIn('kategori', ['Tukar Uang Masuk', 'Tukar Uang Keluar', 'Kembalian'])->orderBy('tanggal', 'asc')->select('kolom')->findAll();
                $totalbiayatot = $this->m_biaya->whereIn('id_transaksi', $id_transaksi)->wherenotIn('jenis_biaya', ['Support'])->wherenotIn('kategori', ['Tukar Uang Masuk', 'Tukar Uang Keluar', 'Kembalian'])->groupBy(['id_valas'])->orderBy('tanggal', 'asc')->select('sum(biaya) as sum, id_valas')->findAll();
                $kurs = $this->m_kurs->whereIn('id_transaksi', $id_transaksi)->select('id_valas, kode_valas, tanggal, kurs')->findAll();
            } else if (!empty($negara) && empty($kategori)) { //milih negara, semua kategori
                $kategori1 = $this->m_kategori->whereIn('id_transaksi', $id_transaksi)->whereIn('negara_tujuan', $negara)->orwhereIn('id_transaksi', $id_transaksi)->whereIn('negara_trading', $negara)->wherenotIn('jenis_biaya', ['Support'])->wherenotIn('kategori', ['Tukar Uang Masuk', 'Tukar Uang Keluar', 'Kembalian'])->orderBy('tanggal', 'asc')->findAll();
                $id_kat = $this->m_kategori->whereIn('id_transaksi', $id_transaksi)->whereIn('negara_tujuan', $negara)->orwhereIn('id_transaksi', $id_transaksi)->whereIn('negara_trading', $negara)->wherenotIn('jenis_biaya', ['Support'])->wherenotIn('kategori', ['Tukar Uang Masuk', 'Tukar Uang Keluar', 'Kembalian'])->orderBy('tanggal', 'asc')->select('id_kategori')->findAll();
                $id_pjum = $this->m_kategori->whereIn('id_transaksi', $id_transaksi)->whereIn('negara_tujuan', $negara)->orwhereIn('id_transaksi', $id_transaksi)->whereIn('negara_trading', $negara)->wherenotIn('jenis_biaya', ['Support'])->wherenotIn('kategori', ['Tukar Uang Masuk', 'Tukar Uang Keluar', 'Kembalian'])->orderBy('tanggal', 'asc')->select('id_pjum')->findAll();
                $id_pb = $this->m_kategori->whereIn('id_transaksi', $id_transaksi)->whereIn('negara_tujuan', $negara)->orwhereIn('id_transaksi', $id_transaksi)->whereIn('negara_trading', $negara)->wherenotIn('jenis_biaya', ['Support'])->wherenotIn('kategori', ['Tukar Uang Masuk', 'Tukar Uang Keluar', 'Kembalian'])->orderBy('tanggal', 'asc')->select('id_pb')->findAll();

                $arr_kat = implode(' ', array_map(function ($entry) {
                    return ($entry[key($entry)]);
                }, $id_kat));

                $arr_pjum = implode(' ', array_map(function ($entry) {
                    return ($entry[key($entry)]);
                }, $id_pjum));

                $arr_pb = implode(' ', array_map(function ($entry) {
                    return ($entry[key($entry)]);
                }, $id_pb));

                if(empty($arr_kat)) {
                    session()->setFlashdata('warning', ['Data tidak ditemukan']);
                    return redirect()->to('transaksi');
                }

                $id_kategori = explode(' ', $arr_kat);
                $id_pjum = explode(' ', $arr_pjum);
                $id_pb = explode(' ', $arr_pb);

                $biaya = $this->m_biaya->whereIn('id_kategori', $id_kategori)->wherenotIn('jenis_biaya', ['Support'])->wherenotIn('kategori', ['Tukar Uang Masuk', 'Tukar Uang Keluar', 'Kembalian'])->orderBy('tanggal', 'asc')->findAll();
                $valas = $this->m_biaya->whereIn('id_kategori', $id_kategori)->wherenotIn('jenis_biaya', ['Support'])->wherenotIn('kategori', ['Tukar Uang Masuk', 'Tukar Uang Keluar', 'Kembalian'])->orderBy('tanggal', 'asc')->select('id_valas')->findAll();
                $valas1 = $this->m_biaya->whereIn('id_kategori', $id_kategori)->wherenotIn('jenis_biaya', ['Support'])->wherenotIn('kategori', ['Tukar Uang Masuk', 'Tukar Uang Keluar', 'Kembalian'])->orderBy('tanggal', 'asc')->select('kode_valas')->findAll();
                $valas2 = $this->m_biaya->whereIn('id_kategori', $id_kategori)->wherenotIn('jenis_biaya', ['Support'])->wherenotIn('kategori', ['Tukar Uang Masuk', 'Tukar Uang Keluar', 'Kembalian'])->orderBy('tanggal', 'asc')->select('kolom')->findAll();
                $totalbiayatot = $this->m_biaya->whereIn('id_kategori', $id_kategori)->wherenotIn('jenis_biaya', ['Support'])->wherenotIn('kategori', ['Tukar Uang Masuk', 'Tukar Uang Keluar', 'Kembalian'])->groupBy(['id_valas'])->orderBy('tanggal', 'asc')->select('sum(biaya) as sum, id_valas')->findAll();
                $kurs = $this->m_kurs->whereIn('id_pjum', $id_pjum)->orwhereIn('id_pb', $id_pb)->select('id_valas, kode_valas, tanggal, kurs')->findAll();
            } else if (empty($negara) && !empty($kategori)) { //semua negara, milih kategori
                $kategori1 = $this->m_kategori->whereIn('id_transaksi', $id_transaksi)->whereIn('kategori', $kategori)->wherenotIn('jenis_biaya', ['Support'])->wherenotIn('kategori', ['Tukar Uang Masuk', 'Tukar Uang Keluar', 'Kembalian'])->orderBy('tanggal', 'asc')->findAll();
                $id_kat = $this->m_kategori->whereIn('id_transaksi', $id_transaksi)->whereIn('kategori', $kategori)->wherenotIn('jenis_biaya', ['Support'])->wherenotIn('kategori', ['Tukar Uang Masuk', 'Tukar Uang Keluar', 'Kembalian'])->orderBy('tanggal', 'asc')->select('id_kategori')->findAll();
                $id_pjum = $this->m_kategori->whereIn('id_transaksi', $id_transaksi)->whereIn('kategori', $kategori)->wherenotIn('jenis_biaya', ['Support'])->wherenotIn('kategori', ['Tukar Uang Masuk', 'Tukar Uang Keluar', 'Kembalian'])->orderBy('tanggal', 'asc')->select('id_pjum')->findAll();
                $id_pb = $this->m_kategori->whereIn('id_transaksi', $id_transaksi)->whereIn('kategori', $kategori)->wherenotIn('jenis_biaya', ['Support'])->wherenotIn('kategori', ['Tukar Uang Masuk', 'Tukar Uang Keluar', 'Kembalian'])->orderBy('tanggal', 'asc')->select('id_pb')->findAll();

                $arr_kat = implode(' ', array_map(function ($entry) {
                    return ($entry[key($entry)]);
                }, $id_kat));

                $arr_pjum = implode(' ', array_map(function ($entry) {
                    return ($entry[key($entry)]);
                }, $id_pjum));

                $arr_pb = implode(' ', array_map(function ($entry) {
                    return ($entry[key($entry)]);
                }, $id_pb));

                if(empty($arr_kat)) {
                    session()->setFlashdata('warning', ['Data tidak ditemukan']);
                    return redirect()->to('transaksi');
                }

                $id_kategori = explode(' ', $arr_kat);
                $id_pjum = explode(' ', $arr_pjum);
                $id_pb = explode(' ', $arr_pb);

                $biaya = $this->m_biaya->whereIn('id_kategori', $id_kategori)->wherenotIn('jenis_biaya', ['Support'])->wherenotIn('kategori', ['Tukar Uang Masuk', 'Tukar Uang Keluar', 'Kembalian'])->orderBy('tanggal', 'asc')->findAll();
                $valas = $this->m_biaya->whereIn('id_kategori', $id_kategori)->wherenotIn('jenis_biaya', ['Support'])->wherenotIn('kategori', ['Tukar Uang Masuk', 'Tukar Uang Keluar', 'Kembalian'])->orderBy('tanggal', 'asc')->select('id_valas')->findAll();
                $valas1 = $this->m_biaya->whereIn('id_kategori', $id_kategori)->wherenotIn('jenis_biaya', ['Support'])->wherenotIn('kategori', ['Tukar Uang Masuk', 'Tukar Uang Keluar', 'Kembalian'])->orderBy('tanggal', 'asc')->select('kode_valas')->findAll();
                $valas2 = $this->m_biaya->whereIn('id_kategori', $id_kategori)->wherenotIn('jenis_biaya', ['Support'])->wherenotIn('kategori', ['Tukar Uang Masuk', 'Tukar Uang Keluar', 'Kembalian'])->orderBy('tanggal', 'asc')->select('kolom')->findAll();
                $totalbiayatot = $this->m_biaya->whereIn('id_kategori', $id_kategori)->wherenotIn('jenis_biaya', ['Support'])->wherenotIn('kategori', ['Tukar Uang Masuk', 'Tukar Uang Keluar', 'Kembalian'])->groupBy(['id_valas'])->orderBy('tanggal', 'asc')->select('sum(biaya) as sum, id_valas')->findAll();
                $kurs = $this->m_kurs->whereIn('id_pjum', $id_pjum)->orwhereIn('id_pb', $id_pb)->select('id_valas, kode_valas, tanggal, kurs')->findAll();
            } else if (!empty($negara) && !empty($kategori)) { //milih negara, milih kategori
                $kategori1 = $this->m_kategori->whereIn('id_transaksi', $id_transaksi)->whereIn('negara_tujuan', $negara)->whereIn('kategori', $kategori)->orwhereIn('id_transaksi', $id_transaksi)->whereIn('negara_trading', $negara)->whereIn('kategori', $kategori)->wherenotIn('jenis_biaya', ['Support'])->wherenotIn('kategori', ['Tukar Uang Masuk', 'Tukar Uang Keluar', 'Kembalian'])->orderBy('tanggal', 'asc')->findAll();
                $id_kat = $this->m_kategori->whereIn('id_transaksi', $id_transaksi)->whereIn('negara_tujuan', $negara)->whereIn('kategori', $kategori)->orwhereIn('id_transaksi', $id_transaksi)->whereIn('negara_trading', $negara)->whereIn('kategori', $kategori)->wherenotIn('jenis_biaya', ['Support'])->wherenotIn('kategori', ['Tukar Uang Masuk', 'Tukar Uang Keluar', 'Kembalian'])->orderBy('tanggal', 'asc')->select('id_kategori')->findAll();
                $id_pjum = $this->m_kategori->whereIn('id_transaksi', $id_transaksi)->whereIn('negara_tujuan', $negara)->whereIn('kategori', $kategori)->orwhereIn('id_transaksi', $id_transaksi)->whereIn('negara_trading', $negara)->whereIn('kategori', $kategori)->wherenotIn('jenis_biaya', ['Support'])->wherenotIn('kategori', ['Tukar Uang Masuk', 'Tukar Uang Keluar', 'Kembalian'])->orderBy('tanggal', 'asc')->select('id_pjum')->findAll();
                $id_pb = $this->m_kategori->whereIn('id_transaksi', $id_transaksi)->whereIn('negara_tujuan', $negara)->whereIn('kategori', $kategori)->orwhereIn('id_transaksi', $id_transaksi)->whereIn('negara_trading', $negara)->whereIn('kategori', $kategori)->wherenotIn('jenis_biaya', ['Support'])->wherenotIn('kategori', ['Tukar Uang Masuk', 'Tukar Uang Keluar', 'Kembalian'])->orderBy('tanggal', 'asc')->select('id_pb')->findAll();

                $arr_kat = implode(' ', array_map(function ($entry) {
                    return ($entry[key($entry)]);
                }, $id_kat));

                $arr_pjum = implode(' ', array_map(function ($entry) {
                    return ($entry[key($entry)]);
                }, $id_pjum));

                $arr_pb = implode(' ', array_map(function ($entry) {
                    return ($entry[key($entry)]);
                }, $id_pb));

                if(empty($arr_kat)) {
                    session()->setFlashdata('warning', ['Data tidak ditemukan']);
                    return redirect()->to('transaksi');
                }

                $id_kategori = explode(' ', $arr_kat);
                $id_pjum = explode(' ', $arr_pjum);
                $id_pb = explode(' ', $arr_pb);

                $biaya = $this->m_biaya->whereIn('id_kategori', $id_kategori)->wherenotIn('jenis_biaya', ['Support'])->wherenotIn('kategori', ['Tukar Uang Masuk', 'Tukar Uang Keluar', 'Kembalian'])->orderBy('tanggal', 'asc')->findAll();
                $valas = $this->m_biaya->whereIn('id_kategori', $id_kategori)->wherenotIn('jenis_biaya', ['Support'])->wherenotIn('kategori', ['Tukar Uang Masuk', 'Tukar Uang Keluar', 'Kembalian'])->orderBy('tanggal', 'asc')->select('id_valas')->findAll();
                $valas1 = $this->m_biaya->whereIn('id_kategori', $id_kategori)->wherenotIn('jenis_biaya', ['Support'])->wherenotIn('kategori', ['Tukar Uang Masuk', 'Tukar Uang Keluar', 'Kembalian'])->orderBy('tanggal', 'asc')->select('kode_valas')->findAll();
                $valas2 = $this->m_biaya->whereIn('id_kategori', $id_kategori)->wherenotIn('jenis_biaya', ['Support'])->wherenotIn('kategori', ['Tukar Uang Masuk', 'Tukar Uang Keluar', 'Kembalian'])->orderBy('tanggal', 'asc')->select('kolom')->findAll();
                $totalbiayatot = $this->m_biaya->whereIn('id_kategori', $id_kategori)->wherenotIn('jenis_biaya', ['Support'])->wherenotIn('kategori', ['Tukar Uang Masuk', 'Tukar Uang Keluar', 'Kembalian'])->groupBy(['id_valas'])->orderBy('tanggal', 'asc')->select('sum(biaya) as sum, id_valas')->findAll();
                $kurs = $this->m_kurs->whereIn('id_pjum', $id_pjum)->orwhereIn('id_pb', $id_pb)->select('id_valas, kode_valas, tanggal, kurs')->findAll();
            }

            $array = array('I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR');
            $array1 = array('8','9','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','25','26','27','28','29','30','31','32','33','34','35','36','37','38','39','40','41','42','43','44','45');
            $arraypjum = array('C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR');

            $kategorisupport = $this->m_kategori->whereIn('id_transaksi', $id_transaksi)->wherenotIn('jenis_biaya', ['pjum', 'pb'])->orderBy('tanggal', 'asc')->findAll();
            $biayasupport = $this->m_biaya->whereIn('id_transaksi', $id_transaksi)->wherenotIn('jenis_biaya', ['pjum', 'pb'])->orderBy('tanggal', 'asc')->findAll();
            $valassupport = $this->m_biaya->whereIn('id_transaksi', $id_transaksi)->wherenotIn('jenis_biaya', ['pjum', 'pb'])->groupBy(['id_biaya', 'id_transaksi', 'jenis_biaya'])->orderBy('tanggal', 'asc')->select('id_biaya')->findAll();
            $valassup = $this->m_biaya->whereIn('id_transaksi', $id_transaksi)->wherenotIn('jenis_biaya', ['pjum', 'pb'])->groupBy(['id_valas', 'id_transaksi', 'jenis_biaya'])->orderBy('tanggal', 'asc')->select('id_valas')->findAll();

            $arr1 = implode(' ', array_map(function ($entry) {
                return ($entry[key($entry)]);
            }, $valas));

            $exp1 = explode(' ', $arr1);

            $arr2 = implode(' ', array_map(function ($entry) {
                return ($entry[key($entry)]);
            }, $valas1));

            $exp2 = explode(' ', $arr2);

            $arr3 = implode(' ', array_map(function ($entry) {
                return ($entry[key($entry)]);
            }, $valas2));

            $exp3 = explode(' ', $arr3);

            $valas_unique = array_unique($exp1);
            $kode_uniqu = array_unique($exp2);
            $kolom_uniqu = array_unique($exp3);

            $id_valas_unique = array_values($valas_unique);
            $kode_unique = array_values($kode_uniqu);
            $kolom_unique = array_values($kolom_uniqu);

            $count = count((array)$id_valas_unique);
            $count1 = array_keys($id_valas_unique);
            $count2 = count((array)$negara);
            $count3 = count((array)$kategori);
            $count4 = count((array)$kategori1);
            $count5 = count((array)$strorgnm);
            $count6= array_keys($id_valas_unique, 76);
            $count7= count((array)$count6);
            $countsup = count((array)$valassup);
            $countsupport = count((array)$valassupport);
            $baris_total = (int)$count4 + 8;
            $baris_support = (int)$count4 + 10;
            $alpha = $array[$count];
            $alphasup = $arraypjum[$countsup + 1];
            $num = $array1[$count];

            $spreadsheet = new Spreadsheet();
            Calculation::getInstance($spreadsheet)->disableCalculationCache();
            Calculation::getInstance()->setCalculationCacheEnabled(FALSE);
            $spreadsheet->getDefaultStyle()->getFont()->setName('Times New Roman');
            $spreadsheet->getDefaultStyle()->getFont()->setSize(12);
            $sheet = $spreadsheet->getActiveSheet();

            $sheet->setTitle("Laporan Biaya Semua Valuta");

            $sheet->setCellValue('B1', 'PERJALANAN DINAS LUAR NEGERI PERIODE '.strtoupper(tanggal_indo1($tanggal_awal)).' HINGGA '.strtoupper(tanggal_indo1($tanggal_akhir)));
            $sheet->setCellValue('B2', 'Bagian =>');
            $sheet->setCellValue('B3', 'Negara =>');
            $sheet->setCellValue('B4', 'Kategori =>');
            $sheet->setCellValue('B6', 'Tanggal');
            $sheet->setCellValue('C6', 'Kategori');
            $sheet->setCellValue('D6', 'Status');
            $sheet->setCellValue('E6', 'Ref');
            $sheet->setCellValue('F6', 'Note');
            $sheet->setCellValue('G6', 'Negara Tujuan');
            $sheet->setCellValue('H6', 'Negara Transit');
            $sheet->setCellValue('I6', 'Jumlah Personil');
            $sheet->setCellValue('J6', 'Valas');
            $sheet->setCellValue('B'.$baris_total, 'TOTAL BIAYA');

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
            $sheet->mergeCells('B'.$baris_total.':I'.$baris_total);

            $sheet->getStyle('B'.$baris_total.':'.$alpha.$baris_total)->getFont()->setBold( true );
            $sheet->getStyle('J7:'.$alpha.'7')->getFont()->setBold( true );
            $sheet->getStyle('B:'.$alpha)->getAlignment()->setHorizontal('center');
            $sheet->getStyle('B:'.$alpha)->getAlignment()->setVertical('center');
            $sheet->getStyle('J8:'.$alpha.$baris_total)->getAlignment()->setHorizontal('right');

            for ($k = 'B'; $k <= $alpha; $k++) {
                $spreadsheet->getActiveSheet()->getColumnDimension($k)->setWidth(20);
            }

            $sheet->getColumnDimension('A')->setVisible(false);
            $sheet->getColumnDimension('C')->setAutoSize(true);
            $sheet->getColumnDimension('F')->setAutoSize(true);

            $sheet->fromArray($kode_unique, null, 'J7');

            $i = 8;
            foreach ($kategori1 as $key => $value) {
                $sheet->setCellValue('B'.$i, $value['tanggal']);
                $sheet->setCellValue('C'.$i, $value['kategori']);
                $sheet->setCellValue('D'.$i, $value['status']);
                $sheet->setCellValue('E'.$i, $value['ref']);
                $sheet->setCellValue('F'.$i, $value['note']);
                $sheet->setCellValue('G'.$i, $value['negara_tujuan']);
                $sheet->setCellValue('H'.$i, $value['negara_trading']);
                $sheet->setCellValue('I'.$i, $value['jumlah_personil']);
                $sheet->getStyle('B6:'.$alpha.$i+1)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $i++;
            }

            $i = 8;
            foreach ($biaya as $key => $value) {
                for ($j = 0; $j < $count; $j++) {
                    $array = array('J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR');
                    $alpha = $array[$count1[$j]];
                    if ($value['id_valas'] == $id_valas_unique[$j]) {
                        $sheet->setCellValue($alpha.$i, $value['biaya']);
                        $i++;
                    }
                }
            }

            $baris = $baris_support;
            $bar_tot = $baris_total - 2;
            $indexval = $baris + 1;
            $indexkat = $baris + 2;
            $inde = $baris + 4;
            $indexsupport = $countsupport + $inde;

            foreach ($totalbiayatot as $key => $value) {
                for ($j = 0; $j < $count; $j++) {
                    $array = array('J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR');
                    $alpha = $array[$count1[$j]];
                    if ($id_valas_unique[$j] == $value['id_valas']) {
                        $sheet->setCellValue($alpha.$baris_total, $value['sum']);
                        $sheet->setCellValue($alpha.$indexkat, $value['sum']);
                    }
                }
            }

            // Biaya Support

            if (empty($biayasupport)) {
                
            } else {
                $sheet->setCellValue('B'.$baris, 'Biaya Support Perjalanan Dinas Luar Negeri');
                $sheet->setCellValue('G'.$baris, 'TOTAL BIAYA (PJUM + PB + SUPPORT)');
                $sheet->setCellValue('B'.$indexkat, 'Tanggal');
                $sheet->setCellValue('C'.$indexkat, 'Kategori');
                $sheet->setCellValue('D'.$indexkat, 'Jumlah Personil');
                $sheet->setCellValue('E'.$indexkat, 'Biaya');
                $sheet->setCellValue('E'.$indexkat+1, 'IDR');
                $sheet->setCellValue('B'.$indexsupport, 'TOTAL BIAYA SUPPORT');
                
                $sheet->fromArray($kode_unique, null, 'J'.$baris+1);

                $sheet->mergeCells('B'.$baris.':E'.$baris + 1);
                $sheet->mergeCells('B'.$indexkat.':B'.$indexkat + 1);
                $sheet->mergeCells('C'.$indexkat.':C'.$indexkat + 1);
                $sheet->mergeCells('D'.$indexkat.':D'.$indexkat + 1);
                $sheet->mergeCells('B'.$indexsupport.':D'.$indexsupport);

                $sheet->getStyle('B'.$baris)->getFont()->setBold(true);
                $sheet->getStyle('G'.$baris)->getFont()->setBold(true);
                $sheet->getStyle('E'.$indexkat+1)->getFont()->setBold(true);
                $sheet->getStyle('E'.$indexkat+1)->getFont()->setBold(true);
                $sheet->getStyle('B'.$indexsupport.':E'.$indexsupport)->getFont()->setBold(true);
                $sheet->getStyle('E'.$inde.':E'.$indexsupport)->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle('E'.$inde.':E'.$indexsupport)->getAlignment()->setHorizontal('right');

                for ($i=$baris; $i <= $indexsupport; $i++) { 
                    $sheet->getStyle('B'.$baris.':E'.$indexsupport)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                    $sheet->getStyle('G'.$baris.':'.$alpha.$baris + 2)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                    $sheet->getStyle('D'.$baris.':D'.$i)->getNumberFormat()->setFormatCode('#');
                    $i++;
                }
    
                $row = $baris + 4;
                foreach ($kategorisupport as $key => $value) {
                    $sheet->setCellValue('B'.$row, $value['tanggal']);
                    $sheet->setCellValue('C'.$row, $value['kategori']);
                    $sheet->setCellValue('D'.$row, $value['jumlah_personil']);
                    $row++;
                }

                $row = $baris + 4;
                foreach ($biayasupport as $key => $value) {
                    $sheet->setCellValue('E'.$row, $value['biaya']);
                    $row++;
                }

                if (!empty($count7)) {
                    for ($j = 0; $j < $count7; $j++) {
                        $array_tot = array('J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR');
                        $alpha_tot = $array_tot[$count6[$j]];

                        $sheet->mergeCells('G'.$baris.':I'.$baris + 2);
                        $sheet->mergeCells('J'.$baris.':'.$alpha.$baris);

                        $sheet->getStyle('J'.$indexval.':'.$alpha.$indexkat)->getFont()->setBold(true);
                        $sheet->getStyle('J'.$indexkat.':'.$alpha.$indexkat)->getAlignment()->setHorizontal('right');
                        $sheet->getStyle('J:'.$alpha)->getNumberFormat()->setFormatCode('#,##0.00');

                        $sheet->setCellValue('J'.$baris, 'Valas');
                        $sheet->setCellValue($alpha_tot.$indexkat, '=('.$alpha_tot.$baris_total.') + (E'.($indexsupport).')');
                    }
                } else {
                    $sheet->mergeCells('G'.$baris.':H'.$baris + 2);
                    $sheet->mergeCells('I'.$baris.':'.$alpha.$baris);

                    $sheet->getStyle('I'.$indexval.':'.$alpha.$indexkat)->getFont()->setBold(true);
                    $sheet->getStyle('I'.$indexkat.':'.$alpha.$indexkat)->getAlignment()->setHorizontal('right');
                    $sheet->getStyle('I:'.$alpha)->getNumberFormat()->setFormatCode('#,##0.00');

                    $sheet->setCellValue('I'.$baris, 'Valas');
                    $sheet->setCellValue('I'.$baris+1, 'IDR');
                    $sheet->setCellValue('I'.$indexkat, '=E'.($indexsupport));
                }

                $sheet->setCellValue('E'.$indexsupport, '=SUM(E'.$inde.':E'.($indexsupport - 1).')');
            }

            $spreadsheet->createSheet();
            $sheet1 = $spreadsheet->setActiveSheetIndex(1);

            // Rename worksheet
            $spreadsheet->getActiveSheet(1)->setTitle('Laporan Biaya dalam Rupiah');

            $sheet1->setCellValue('B1', 'PERJALANAN DINAS LUAR NEGERI PERIODE '.strtoupper(tanggal_indo1($tanggal_awal)).' HINGGA '.strtoupper(tanggal_indo1($tanggal_akhir)));
            $sheet1->setCellValue('B2', 'Bagian =>');
            $sheet1->setCellValue('B3', 'Negara =>');
            $sheet1->setCellValue('B4', 'Kategori =>');
            $sheet1->setCellValue('B6', 'Tanggal');
            $sheet1->setCellValue('C6', 'Kategori');
            $sheet1->setCellValue('D6', 'Status');
            $sheet1->setCellValue('E6', 'Ref');
            $sheet1->setCellValue('F6', 'Note');
            $sheet1->setCellValue('G6', 'Negara Tujuan');
            $sheet1->setCellValue('H6', 'Negara Transit');
            $sheet1->setCellValue('I6', 'Jumlah Personil');
            $sheet1->setCellValue('J6', 'Valas');
            $sheet1->setCellValue('J7', 'IDR');
            $sheet1->setCellValue('B'.$baris_total, 'TOTAL BIAYA');

            $sheet1->mergeCells('B1:J1');
            $sheet1->mergeCells('B6:B7');
            $sheet1->mergeCells('C6:C7');
            $sheet1->mergeCells('D6:D7');
            $sheet1->mergeCells('E6:E7');
            $sheet1->mergeCells('F6:F7');
            $sheet1->mergeCells('G6:G7');
            $sheet1->mergeCells('H6:H7');
            $sheet1->mergeCells('I6:I7');
            $sheet1->mergeCells('B'.$baris_total.':I'.$baris_total);

            $sheet1->getStyle('B'.$baris_total.':J'.$baris_total)->getFont()->setBold( true );
            $sheet1->getStyle('B:J')->getAlignment()->setHorizontal('center');
            $sheet1->getStyle('B:J')->getAlignment()->setVertical('center');
            $sheet1->getStyle('J8:J'.$baris_total)->getAlignment()->setHorizontal('right');
            $sheet1->getStyle('J')->getNumberFormat()->setFormatCode('#,##0.00');

            for ($k = 'B'; $k <= 'J'; $k++) {
                $spreadsheet->getActiveSheet(1)->getColumnDimension($k)->setWidth(20);
            }

            $sheet1->getColumnDimension('A')->setVisible(false);
            $sheet1->getColumnDimension('C')->setAutoSize(true);
            $sheet1->getColumnDimension('F')->setAutoSize(true);

            $i = 8;
            foreach ($kategori1 as $key => $value) {
                $sheet1->setCellValue('B'.$i, $value['tanggal']);
                $sheet1->setCellValue('C'.$i, $value['kategori']);
                $sheet1->setCellValue('D'.$i, $value['status']);
                $sheet1->setCellValue('E'.$i, $value['ref']);
                $sheet1->setCellValue('F'.$i, $value['note']);
                $sheet1->setCellValue('G'.$i, $value['negara_tujuan']);
                $sheet1->setCellValue('H'.$i, $value['negara_trading']);
                $sheet1->setCellValue('I'.$i, $value['jumlah_personil']);
                $sheet1->getStyle('B6:J'.$i+1)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $i++;
            }

            $i = 8;
            foreach ($biaya as $key => $value) {
                $id_valas = $value['id_valas'];
                $id_pjum = $value['id_pjum'];
                $id_pb = $value['id_pb'];
                $biaya = $value['biaya'];
                if ($id_valas != 76 && $id_pjum != null) {
                    $kurs = $this->m_kurs->where('id_pjum', $id_pjum)->select('id_valas, kode_valas, tanggal, kurs')->findAll();
                    if (empty($kurs)) {
                        if ($id_valas != 76) {
                            $kurs = 1;
                            $kurs_biaya = $biaya * $kurs;
                            $sheet1->setCellValue('J'.$i, $kurs_biaya);
                            $i++;
                        }
                    } else if (!empty($kurs)) {
                        foreach ($kurs as $k => $kur) {
                            if ($id_valas != 76 && $kur['id_valas']) {
                                $kurs = $kur['kurs'];
                                $kurs_biaya = $biaya * $kurs;
                                $sheet1->setCellValue('J'.$i, $kurs_biaya);
                                $i++;
                            }
                        }
                    }
                } else if ($id_valas != 76 && $id_pb != null) {
                    $kurs = $this->m_kurs->where('id_pb', $id_pb)->select('id_valas, kode_valas, tanggal, kurs')->findAll();
                    if (empty($kurs)) {
                        if ($id_valas != 76) {
                            $kurs = 1;
                            $kurs_biaya = $biaya * $kurs;
                            $sheet1->setCellValue('J'.$i, $kurs_biaya);
                            $i++;
                        }
                    } else if (!empty($kurs)) {
                        foreach ($kurs as $k => $kur) {
                            if ($id_valas != 76 && $kur['id_valas']) {
                                $kurs = $kur['kurs'];
                                $kurs_biaya = $biaya * $kurs;
                                $sheet1->setCellValue('J'.$i, $kurs_biaya);
                                $i++;
                            }
                        }
                    }
                } else if ($id_valas == 76) {
                    $sheet1->setCellValue('J'.$i, $biaya);
                    $i++;
                }
                
                $sheet1->setCellValue('J'.$baris_total, '=SUM(J8:J'.($baris_total - 1).')');
            }

            // Biaya Support

            if (empty($biayasupport)) {
                
            } else {
                $sheet1->setCellValue('B'.$baris, 'Biaya Support Perjalanan Dinas Luar Negeri');
                $sheet1->setCellValue('G'.$baris, 'TOTAL BIAYA (PJUM + PB + SUPPORT)');
                $sheet1->setCellValue('J'.$baris, 'Valas');
                $sheet1->setCellValue('J'.$baris+1, 'IDR');
                $sheet1->setCellValue('B'.$indexkat, 'Tanggal');
                $sheet1->setCellValue('C'.$indexkat, 'Kategori');
                $sheet1->setCellValue('D'.$indexkat, 'Jumlah Personil');
                $sheet1->setCellValue('E'.$indexkat, 'Biaya');
                $sheet1->setCellValue('E'.$indexkat+1, 'IDR');
                $sheet1->setCellValue('B'.$indexsupport, 'TOTAL BIAYA SUPPORT');

                $sheet1->mergeCells('B'.$baris.':E'.$baris + 1);
                $sheet1->mergeCells('G'.$baris.':I'.$baris + 2);
                $sheet1->mergeCells('B'.$indexkat.':B'.$indexkat + 1);
                $sheet1->mergeCells('C'.$indexkat.':C'.$indexkat + 1);
                $sheet1->mergeCells('D'.$indexkat.':D'.$indexkat + 1);
                $sheet1->mergeCells('B'.$indexsupport.':D'.$indexsupport);

                $sheet1->getStyle('B'.$baris)->getFont()->setBold(true);
                $sheet1->getStyle('G'.$baris)->getFont()->setBold(true);
                $sheet1->getStyle('B'.$indexsupport.':E'.$indexsupport)->getFont()->setBold(true);
                $sheet1->getStyle('J'.$indexkat)->getFont()->setBold(true);
                $sheet1->getStyle('E'.$inde.':E'.$indexsupport)->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet1->getStyle('E'.$inde.':E'.$indexsupport)->getAlignment()->setHorizontal('right');
                $sheet1->getStyle('J'.$indexkat)->getAlignment()->setHorizontal('right');

                for ($i=$baris; $i <= $indexsupport; $i++) { 
                    $sheet1->getStyle('B'.$baris.':E'.$indexsupport)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                    $sheet1->getStyle('G'.$baris.':J'.$baris + 2)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                    $sheet1->getStyle('D'.$baris.':D'.$i)->getNumberFormat()->setFormatCode('#');
                    $i++;
                }
    
                $row = $baris + 4;
                foreach ($kategorisupport as $key => $value) {
                    $sheet1->setCellValue('B'.$row, $value['tanggal']);
                    $sheet1->setCellValue('C'.$row, $value['kategori']);
                    $sheet1->setCellValue('D'.$row, $value['jumlah_personil']);
                    $row++;
                }

                $row = $baris + 4;
                foreach ($biayasupport as $key => $value) {
                    $sheet1->setCellValue('E'.$row, $value['biaya']);
                    $row++;
                }

                $sheet1->setCellValue('E'.$indexsupport, '=SUM(E'.$inde.':E'.($indexsupport - 1).')');
                $sheet1->setCellValue('J'.$indexkat, '=(J'.$baris_total.') + (E'.($indexsupport).')');
            }

            if(empty($strorgnm)) {
                $strorgnm = "Semua";
                $sheet->setCellValue('C2', $strorgnm);
                $sheet1->setCellValue('C2', $strorgnm);
            } else {
                $tmp_strorgnm = '';
                for ($i=0; $i < $count5; $i++) {
                    $tmp_strorgnm .= $strorgnm[$i].', ';
                    $sheet->setCellValue('C2', substr($tmp_strorgnm, 0, -2));
                    $sheet1->setCellValue('C2', substr($tmp_strorgnm, 0, -2));
                }
            }

            if(empty($negara)){
                $tmp_negara = "Semua";
                $sheet->setCellValue('C3', $tmp_negara);
                $sheet1->setCellValue('C3', $tmp_negara);
            } else {
                $tmp_negara = '';
                for ($i=0; $i < $count2; $i++) {
                    $tmp_negara .= $negara[$i].', ';
                    $sheet->setCellValue('C3', substr($tmp_negara, 0, -2));
                    $sheet1->setCellValue('C3', substr($tmp_negara, 0, -2));
                }
            }

            if(empty($kategori)) {
                $tmp_kategori = "Semua";
                $sheet->setCellValue('C4', $tmp_kategori);
                $sheet1->setCellValue('C4', $tmp_kategori);
            } else {
                $tmp_kategori = '';
                for ($i=0; $i < $count3; $i++) {
                    $tmp_kategori .= $kategori[$i].', ';
                    $sheet->setCellValue('C4', substr($tmp_kategori, 0, -2));
                    $sheet1->setCellValue('C4', substr($tmp_kategori, 0, -2));
                }
            }

            $spreadsheet->setActiveSheetIndex(0);

            $writer = new Xls($spreadsheet);
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment; filename=Biaya Perjalanan Dinas LN periode '.tanggal_indo1($tanggal_awal).' sampai '.tanggal_indo1($tanggal_akhir).'.xls');
            $writer->save("php://output");
            // $writer->save('Biaya PDLN periode '.tanggal_indo1($tanggal_awal).' sampai '.tanggal_indo1($tanggal_akhir).'.xls');
            // return redirect()->to('transaksi');
        }

        $strorg = session()->get('strorg');
        $nik = session()->get('akun_nik');
        $niknm = session()->get('niknm');
        $role = session()->get('akun_role');

        $id_transaksi = $this->m_id->select('id_transaksi')->findAll();

        foreach ($id_transaksi as $key => $value) {
            $login_by = $this->m_id->where('id_transaksi', $value['id_transaksi'])->select('login_by')->first();
            if ($login_by['login_by'] == $niknm) {
                $data = [
                    'id_transaksi' => $value['id_transaksi'],
                    'login' => 0,
                    'login_by' => null,
                ];
                $this->m_id->save($data);
                return redirect()->to('transaksi');
            } else if ($login_by['login_by'] == null) {

            }
        }

        $bm06 = $this->m_bm06->getData($strorg);

        $akun = [
            'strorgnm' => $bm06['strorgnm'],
            'tglsls' => $bm06['tglsls'],
        ];
        session()->set($akun);
        $strorgnm = session()->get('strorgnm');

        if($role == 'admin') {
            $hasil = $this->m_id->listAdminId(substr($strorg, 0, 4));
        } else if($role == 'user') {
            $hasil = $this->m_id->listNikId($strorg, $nik);
        } else if($role == 'treasury') {
            $hasil = $this->m_id->listTreasury();
        } else if($role == 'gs') {
            $hasil = $this->m_id->listGS();
        }

        // echo substr($strorg, 0, 4);

        // $timestamp = date('Y-m-d H:i:s');
        // $time = (strtotime($timestamp));

        // $logout = $time - session()->get('login_at');

        // if($logout > 5){
        //     return redirect()->to('logout');
        // }

        session()->set('url_transaksi', current_url());

        $data = [
            'header' => "ID Transaksi Perjalanan Dinas Luar Negeri",
            'hasil' => $hasil,
            'id_t' => $this->m_id->getDataAll(),
            'role' => $role,
            'bag' => $this->m_bm06->bagian(substr($strorg, 0, 4)),
            'neg' => $this->m_negara->getDataAll(),
            'submit' => $submit,
            'date_min' => $this->m_id->where('SUBSTRING(strorg, 1, 4)', substr($strorg, 0, 4))->where('submit_pjum', 4)->where('submit_pb >=', 3)->select('tanggal_berangkat')->orderBy('tanggal_berangkat', 'asc')->first(),
            'date_max' => $this->m_id->where('SUBSTRING(strorg, 1, 4)', substr($strorg, 0, 4))->where('submit_pjum', 4)->where('submit_pb >=', 3)->select('tanggal_pulang')->orderBy('tanggal_pulang', 'desc')->first(),
        ];
        echo view('transaksi/v_transaksi', $data);
        // print_r(session()->get(''));
    }

    public function islogin($id_transaksi)
    {
        $nik = session()->get('akun_nik');
        $role = session()->get('akun_role');
        $niknm = session()->get('niknm');

        $login = $this->m_id->where('id_transaksi', $id_transaksi)->select('login')->first();

        if ($login['login'] == 0) {
            if ($role == 'admin' || $role == 'user') {
                $data = [
                    'id_transaksi' => $id_transaksi,
                    'login' => 1,
                    'login_by' => $niknm,
                ];
                $this->m_id->save($data);
                return redirect()->to('dashboard/'.$id_transaksi);
            }
        } else {
            session()->setFlashdata('warning', ['Id transaksi sedang diedit, harap menunggu beberapa saat lagi']);
            return redirect()->to("transaksi");
        }
    }

    public function tambahdataid()
    {
        $role = session()->get('akun_role');
        $strorg = session()->get('strorg');

        $nik = $this->m_am21->nik(substr($strorg, 0, 4));

        if($role == 'treasury') {
            return redirect()->to("transaksi");
        } elseif($role == 'gs') {
            return redirect()->to("transaksi");
        }

        $data = [];
        if($this->request->getMethod() == 'post') {
            $data = $this->request->getVar(); //setiap yang diinputkan akan dikembalikan ke view
            $aturan = [
                'jumlah_personil' => [
                    'rules' => 'required',
                    'errors' => [
                        'required' => 'Jumlah Personil harus diisi'
                    ]
                ],
                'tanggal_berangkat' => [
                    'rules' => 'required',
                    'errors' => [
                        'required' => 'Tanggal Keberangkatan harus diisi'
                    ]
                ],
                'tanggal_pulang' => [
                    'rules' => 'required',
                    'errors' => [
                        'required' => 'Tanggal Pulang harus diisi'
                    ]
                ],
            ];
            if(!$this->validate($aturan)) {
                session()->setFlashdata('warning', $this->validation->getErrors());
            } else {
                $session = \Config\Services::session();
                $ni = $this->request->getVar('nik');
                $split = explode(" - ", $ni);
                if($role == 'admin') {
                    $ni = $split[0] ;
                    $strorg = $split[2];
                    $bagian = $this->m_bm06->where('strorg', $split[2])->select('strorgnm')->first();
                    $strorgnm = $bagian['strorgnm'];
                } elseif($role == 'user') {
                    $ni = session()->get('akun_nik');
                    $strorg = session()->get('strorg');
                    $strorgnm = session()->get('strorgnm');
                }
                $record = [
                    'nik' => $ni,
                    'role' => 'user',
                    'strorg' => $strorg,
                    'strorgnm' => $strorgnm,
                    'jumlah_personil' => $this->request->getVar('jumlah_personil'),
                    'tanggal_berangkat' => $this->request->getVar('tanggal_berangkat'),
                    'tanggal_pulang' => $this->request->getVar('tanggal_pulang'),
                    'created_by' => session()->get('akun_nik'),
                ];
                $aksi = $this->m_id->insertTransaksi($record);

                if($aksi != false) { //dibagian aksi tidak false atau ada isinya
                    $page_id = $aksi;
                    session()->setFlashdata('success', 'ID Transaksi Perjalanan Dinas Luar Negeri berhasil dibuat');
                    return redirect()->to('transaksi');
                } else {
                    session()->setFlashdata('warning', ['ID Transaksi Perjalanan Dinas Luar Negeri gagal dibuat']);
                    return redirect()->to('transaksi');
                }
            }
        }
        $data = [
            'header' => "Tambah Data ID Transaksi Perjalanan Dinas Luar Negeri",
            'id_t' => $this->m_id->getDataAll(),
            'role' => $role,
            'nik' => $nik,
        ];
        $data['id'] = $this->m_admin->selectData();
        echo view('transaksi/v_tambahdataid', $data);
        // print_r(session()->get());
    }

    public function detailtransaksi($id_transaksi)
    {
        $session = [
            'id_transaksi' => $id_transaksi,
        ];
        session()->set($session);
        $id_transaksi = session()->get('id_transaksi');

        $strorg = session()->get('strorg');
        $nik = session()->get('akun_nik');
        $role = session()->get('akun_role');
        if($role == 'admin') {
            $dataPost = $this->m_id->getPostId($id_transaksi, substr($strorg, 0, 4));
        } elseif($role == 'user') {
            $dataPost = $this->m_id->getId($id_transaksi, $nik);
        } elseif($role == 'treasury') {
            $dataPost = $this->m_id->getTreasury($id_transaksi);
        } elseif($role == 'gs') {
            $dataPost = $this->m_id->getGS($id_transaksi);
        }
        if(empty($dataPost)) {
            return redirect()-> to("transaksi");
        }
        $data = $dataPost;

        $submit_pjum = $this->m_id->where('id_transaksi', $id_transaksi)->select('submit_pjum')->first();
        $submit_pb = $this->m_id->where('id_transaksi', $id_transaksi)->select('submit_pb')->first();

        if ($role == 'treasury' && $submit_pjum['submit_pjum'] != 1 && $submit_pb['submit_pb'] != 1) {
            return redirect()-> to("transaksi");
        } elseif ($role == 'gs' && $submit_pjum['submit_pjum'] < 2 && $submit_pb['submit_pb'] < 2) {
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
        $kota = $this->m_id->where('id_transaksi', $id_transaksi)->select('kota as kota')->first();

        if($role == 'treasury') {
            $id = $this->m_id->getTreasury($id_transaksi);
        } elseif($role == 'gs') {
            $id = $this->m_id->getGS($id_transaksi);
        } else {
            $id = $this->m_id->getPostId($id_transaksi, substr($strorg, 0, 4));
        }

        $data = [
            'header' => " Detail ID Transaksi Perjalanan Dinas Luar Negeri",
            'id' => $id,
            'kot' => $kota['kota'],
            'personil' => $personil,
            'neg' => $negara,
            'negara' => $negara,
        ];
        echo view('transaksi/v_detailtransaksi', $data);
        // print_r(session()->get());
    }

    public function tambahpersonil($id_transaksi)
    {
        $nik = session()->get('akun_nik');
        $role = session()->get('akun_role');

        if($role == 'treasury') {
            return redirect()->to("transaksi");
        } elseif($role == 'gs') {
            return redirect()->to("transaksi");
        }

        $strorg = session()->get('strorg');
        if($role == 'admin') {
            $dataPost = $this->m_id->getPostId($id_transaksi, substr($strorg, 0, 4));
        } elseif($role == 'user') {
            $dataPost = $this->m_id->getId($id_transaksi, $nik);
        }
        if(empty($dataPost)) {
            return redirect()-> to("transaksi");
        }
        $data = $dataPost;

        $session = [
            'id_transaksi' => $id_transaksi,
        ];
        session()->set($session);
        $id_transaksi = session()->get('id_transaksi');

        if($this->request->getMethod() == 'post') {
            $data = $this->request->getVar(); //setiap yang diinputkan akan dikembalikan ke view
            $aturan = [
                'niknm' => [
                    'rules' => 'required',
                    'errors' => [
                        'required' => 'Nama Lengkap Personil harus diisi'
                    ]
                ],
                'strorgnm' => [
                    'rules' => 'required',
                    'errors' => [
                        'required' => 'Bagian harus diisi'
                    ]
                ],
                'kota' => [
                    'rules' => 'required',
                    'errors' => [
                        'required' => 'Harap isi berangkat dari kota mana'
                    ]
                ],
            ];
            if(!$this->validate($aturan)) {
                session()->setFlashdata('warning', $this->validation->getErrors());
            } else {
                $session = \Config\Services::session();
                // $nama_lengkap_string = implode(" ",$nama_lengkap);
                // $inisial_string = implode(" ",$inisial);
                // $nik_string = implode(" ",$nik);
                // $jabatan_string = implode(" ",$jabatan);

                if(isset($_POST['submit'])) {
                    $n = $_POST['niknm'];
                    if(!empty($n)) {
                        for($a = 0; $a < count($n); $a++) {
                            if(!empty($n[$a])) {
                                $username = $n[$a];
                                //membuat insert data sementara
                                // echo 'Data ke -' .($a+1).'=> Nama: '.$username.';</br>';
                                $nama = $_POST['niknm'][$a];
                                $split = explode(" - ", $nama);
                                $record[] = array(
                                    'niknm' => $split[0],
                                    'nik' => $split[1],
                                    'strorgnm' => $_POST['strorgnm'],
                                    'id_transaksi' => $id_transaksi,
                                );
                                $kota = [
                                    'id_transaksi' => $id_transaksi,
                                    'kota' => $this->request->getVar('kota'),
                                ];
                            }
                        }
                    }
                }
                $aksi = $this->m_personil->insertPersonil($record);
                $this->m_id->save($kota);

                if($aksi != true) { //dibagian aksi tidak false atau ada isinya
                    $page_id = $aksi;
                    session()->setFlashdata('success', 'Data berhasil ditambahkan');
                    return redirect()->to('tambahnegara/'.$id_transaksi);
                } else {
                    session()->setFlashdata('warning', ['Data gagal ditambahkan']);
                    return redirect()->to('tambahpersonil/'.$id_transaksi);
                }
            }
        }
        $data = [
            'header' => "Tambah Data Personil",
            'bag' => $this->m_bm06->bagian(substr($strorg, 0, 4)),
            'nama' => $this->m_am21->nik(substr($strorg, 0, 4)),
            'neg' => $this->m_negara->getDataAll(),
            'kot' => $this->m_kota->getDataAll(),
            'id_t' => $this->m_id->getDataAll(),
            'personil' => $this->m_personil->getDataAllId($id_transaksi),
        ];
        $data['id'] = $this->m_admin->selectData();
        echo view('transaksi/v_tambahpersonil', $data);
        // print_r(session()->get());
    }

    public function tambahnegara($id_transaksi)
    {
        $nik = session()->get('akun_nik');
        $niknm = session()->get('niknm');
        $role = session()->get('akun_role');

        if($role == 'treasury') {
            return redirect()->to("transaksi");
        } elseif($role == 'gs') {
            return redirect()->to("transaksi");
        }

        $strorg = session()->get('strorg');
        if($role == 'admin') {
            $dataPost = $this->m_id->getPostId($id_transaksi, substr($strorg, 0, 4));
        } elseif($role == 'user') {
            $dataPost = $this->m_id->getId($id_transaksi, $nik);
        }
        if(empty($dataPost)) {
            return redirect()-> to("transaksi");
        }
        $data = $dataPost;

        $session = [
            'id_transaksi' => $id_transaksi,
        ];
        session()->set($session);
        $id_transaksi = session()->get('id_transaksi');

        if($this->request->getMethod() == 'post') {
            $data = $this->request->getVar(); //setiap yang diinputkan akan dikembalikan ke view
            $aturan = [
                'negara_tujuan' => [
                    'rules' => 'required',
                    'errors' => [
                        'required' => 'Negara Tujuan harus diisi'
                    ]
                ],
            ];
            if(!$this->validate($aturan)) {
                session()->setFlashdata('warning', $this->validation->getErrors());
            } else {
                $session = \Config\Services::session();

                if(isset($_POST['submit'])) {
                    $n = $_POST['negara_tujuan'];
                    if(!empty($n)) {
                        for($a = 0; $a < count($n); $a++) {
                            if(!empty($n[$a])) {
                                $username = $n[$a];
                                //membuat insert data sementara
                                // echo 'Data ke -' .($a+1).'=> Nama: '.$username.';</br>';
                                $record[] = array(
                                    'negara_tujuan' => $_POST['negara_tujuan'][$a],
                                    'id_transaksi' => $id_transaksi,
                                );
                            }
                        }
                    }
                }
                $aksi = $this->m_negara_tujuan->insertNegara($record);

                if($aksi != true) { //dibagian aksi tidak false atau ada isinya
                    $page_id = $aksi;
                    $data = [
                        'id_transaksi' => $id_transaksi,
                        'login' => 1,
                        'login_by' => $niknm,
                    ];
                    $this->m_id->save($data);
                    session()->setFlashdata('success', 'Data berhasil ditambahkan');
                    return redirect()->to('dashboard/'.$id_transaksi);
                } else {
                    session()->setFlashdata('warning', ['Data gagal ditambahkan']);
                    return redirect()->to('tambahnegara/'.$id_transaksi);
                }
            }
        }
        $data = [
            'header' => "Tambah Data Negara Tujuan",
            'neg' => $this->m_negara->getDataAll(),
            'id_t' => $this->m_id->getDataAll(),
        ];
        $data['id'] = $this->m_admin->selectData();
        echo view('transaksi/v_tambahnegara', $data);
        // print_r(session()->get());
    }
}
