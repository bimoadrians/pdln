<?php
namespace App\Controllers\Admin;

date_default_timezone_set("Asia/Jakarta");

use App\Controllers\BaseController;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use CodeIgniter\HTTP\IncomingRequest;
use App\Models\Am21Model;
use App\Models\Am21bModel;
use App\Models\TransaksiModel;
use App\Models\NegaraTujuanModel;
use App\Models\PersonilModel;
use App\Models\ValasModel;
use App\Models\BiayaModel;
use App\Models\KategoriModel;
use App\Models\PumModel;
use App\Models\PbModel;
use App\Models\KursModel;
use App\Models\LogEmailModel;
use App\Models\LogEmailAllModel;

class PB extends BaseController
{
    public function __construct()
    {
        $this->validation = \Config\Services::validation();

        $this->m_am21 = new Am21Model();
        $this->m_am21b = new Am21bModel();
        $this->m_id = new TransaksiModel();
        $this->m_negara_tujuan = new NegaraTujuanModel();
        $this->m_personil = new PersonilModel();
        $this->m_valas = new ValasModel();
        $this->m_biaya = new BiayaModel();
        $this->m_kategori = new KategoriModel();
        $this->m_pum = new PumModel();
        $this->m_pb = new PbModel();
        $this->m_kurs = new KursModel();
        $this->m_log_email = new LogEmailModel();
        $this->m_log_email_all = new LogEmailAllModel();

        helper('global_fungsi_helper');
        helper('url');

        require 'vendor/autoload.php';
        require 'vendor/phpmailer/phpmailer/src/Exception.php';
        require 'vendor/phpmailer/phpmailer/src/PHPMailer.php';
        require 'vendor/phpmailer/phpmailer/src/SMTP.php';
    }

    //PB
    public function listpb($jenis_biaya, $id_transaksi)
    {
        $nik= session()->get('akun_nik');
        $niknm= session()->get('niknm');
        $role= session()->get('akun_role');
        $strorg = session()->get('strorg');

        $login = $this->m_id->where('id_transaksi', $id_transaksi)->select('login_by')->first();

        if ($role == 'admin' && $login['login_by'] != $niknm || $role == 'user' && $login['login_by'] != $niknm) {
            session()->setFlashdata('warning', ['Id transaksi sedang diedit, harap menunggu beberapa saat lagi']);
            return redirect()->to("transaksi");
        } else {

        }

        $submit_pjum = $this->m_id->where('id_transaksi', $id_transaksi)->select('submit_pjum, kirim_pjum')->first();
        $submit_pb = $this->m_id->where('id_transaksi', $id_transaksi)->select('submit_pb, kirim_pb')->first();

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

        if ($role == 'gs' && $submit_pb['submit_pb'] < 2) {
            return redirect()-> to("transaksi");
        }

        $kat = $this->m_kategori->alldataId($id_transaksi, $jenis_biaya);
        if(empty($kat)) {
            session()->setFlashdata('warning', ['Tidak ada data, silahkan upload data biaya terlebih dahulu']);
            return redirect()-> to('dashboard/'.$id_transaksi);
        }

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

        if($jenis_biaya == 'pjum'){
            return redirect()-> to('listpb/pb/'.$id_transaksi);
        }

        $nopb = $this->m_pb->where('id_transaksi', $id_transaksi)->select('created_by')->findAll();
        foreach ($nopb as $nb => $nopb) {
            $created_by = $nopb['created_by'];
            if ($role == 'admin' && $created_by == '05080' || $role == 'user' && $created_by == '05080') {
                return redirect()-> to("transaksi");
            }
        }

        $ceknomor = $this->m_pb->ceknomor($id_transaksi, $this->request->getVar('nomor'));

        if ($ceknomor == null) {
            
        } else if($id_transaksi == $ceknomor['id_transaksi'] && $this->request->getVar('nomor') == $ceknomor['nomor']){
            session()->setFlashdata('warning', ['Nomor PB tidak boleh sama']);
            return redirect()->to('listpb/'.$jenis_biaya.'/'.$id_transaksi);
        }

        $timestamp = date('Y-m-d H:i:s');

        $mail = new PHPMailer(true);
        $nikuser = $this->m_id->where('id_transaksi', $id_transaksi)->select('nik, strorgnm')->first();
        $niknmuser = $this->m_am21->where('nik', $nikuser['nik'])->select('niknm')->first();
        $emailuser = $this->m_am21b->where('nik', $nikuser['nik'])->select('noemailint')->first();
        if($this->request->getMethod()=='post') {
            if ($this->request->getVar('nomor') == null || $this->request->getVar('nomor') == '') {
                if ($role == 'admin' || $role == 'user') {
                    $id_pb = $this->m_kategori->where('id_transaksi', $id_transaksi)->select('id_pb')->findAll();

                    foreach ($id_pb as $key => $value) {
                        if ($value['id_pb'] != null) {
                            $nopb = $this->m_pb->where('id_pb', $value['id_pb'])->select('id_pb, tanggal')->findAll();
                            foreach ($nopb as $nb => $nopb) {
                                if($nopb['tanggal'] == null){
                                    session()->setFlashdata('warning', ['Tambahkan tanggal pembuatan no PB terlebih dahulu untuk melakukan submit data']);
                                    return redirect()-> to('listpb/pb/'.$id_transaksi);
                                }
                            }
                        }
                    }
                    try {
                        //Server settings
                        $mail->SMTPDebug = SMTP::DEBUG_SERVER;
                        $mail->isSMTP();
                        $mail->Host       = 'mail.konimex.com';
                        $mail->SMTPAuth   = true;
                        $mail->Username   = PDLN_EMAIL;
                        $mail->Password   = PDLN_PASS;
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port       = 587;
                    
                        //Recipients
                        $mail->setFrom('noreply@konimex.com');
                        $mail->addAddress('renny.lowis@konimex.com');//Add a recipient (Treasury) renny.lowis@konimex.com
                        $mail->addCC('edy_santoso@konimex.com');//Add a recipient (Treasury Manager) edy_santoso@konimex.com
                        $mail->addBCC('09002@intra.net');//Add a recipient (BAS) 09002@intra.net
                    
                        //Content
                        $link='https://konimex.com:446/pdln/listpb/pb/'.$id_transaksi;
                        $mail->Subject = 'Data PB Siap Periksa';
                        $mail->Body    = "Dengan hormat,\n\n".
                        "User bagian (".$niknmuser['niknm']."/".$nikuser['nik']."/".$nikuser['strorgnm'].") telah selesai mengisi data PB, silahkan periksa data PB dengan cara klik link: $link\n\n".
                        "Terima kasih.";
                    
                        $mail->send();
                        echo 'Message has been sent';
                    } catch (Exception $e) {
                        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                    }

                    $data = [
                        'id_transaksi' => $id_transaksi,
                        'submit_pb' => 1,
                        'kirim_pb' => 1,
                    ];

                    $cek_log_email = $this->m_log_email->where('id_transaksi', $id_transaksi)->select('id_log_email')->first();

                    if (empty($cek_log_email)) {
                        $log_email = [
                            'id_transaksi' => $id_transaksi,
                            'title' => 'Data PB '.$nikuser['nik'].' Siap Periksa',
                            'nik' => $nikuser['nik'],
                            'submit_pb' => 1,
                            'kirim_pb' => 1,
                            'waktu_kirim' => $timestamp,
                        ];
                        $this->m_log_email->insert($log_email);
                    } else {
                        $log_email = [
                            'id_log_email' => $cek_log_email['id_log_email'],
                            'submit_pb' => 1,
                            'kirim_pb' => 1,
                            'waktu_kirim' => $timestamp,
                        ];
                        $this->m_log_email->save($log_email);
                    }

                    $log_email_all = [
                        'id_transaksi' => $id_transaksi,
                        'title' => 'Data PB '.$nikuser['nik'].' Siap Periksa',
                        'nik' => $nikuser['nik'],
                        'submit_pb' => 1,
                        'waktu_kirim' => $timestamp,
                    ];
                    $this->m_log_email_all->insert($log_email_all);
                    $this->m_id->save($data);

                    session()->setFlashdata('success', 'Data PB berhasil disubmit');
                    return redirect()->to('listpb/'.$jenis_biaya.'/'.$id_transaksi);
                } else if ($role == 'treasury'){
                    try {
                        //Server settings
                        $mail->SMTPDebug = SMTP::DEBUG_SERVER;
                        $mail->isSMTP();
                        $mail->Host       = 'mail.konimex.com';
                        $mail->SMTPAuth   = true;
                        $mail->Username   = PDLN_EMAIL;
                        $mail->Password   = PDLN_PASS;
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port       = 587;
                    
                        //Recipients
                        $mail->setFrom('noreply@konimex.com');
                        $mail->addAddress($emailuser['noemailint']);//Add a recipient (User Bagian)
                        $mail->addBCC('09002@intra.net');//Add a recipient (BAS) 09002@intra.net
                    
                        //Content
                        $link='https://konimex.com:446/pdln/listpb/pb/'.$id_transaksi;
                        $mail->Subject = 'Data PB Telah Diperiksa Bagian Treasury';
                        $mail->Body    = "Dengan hormat,\n\n".
                        "Bagian Treasury telah selesai melakukan pengecekan data PB, untuk melihat revisi data PB silahkan klik link: $link\n\n".
                        "Terima kasih.";
                    
                        $mail->send();
                        echo 'Message has been sent';
                    } catch (Exception $e) {
                        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                    }

                    $data = [
                        'id_transaksi' => $id_transaksi,
                        'submit_pb' => 0,
                    ];

                    $cek_log_email = $this->m_log_email->where('id_transaksi', $id_transaksi)->select('id_log_email')->first();

                    if (empty($cek_log_email)) {
                        $log_email = [
                            'id_transaksi' => $id_transaksi,
                            'title' => 'Data PB Telah Diperiksa Bagian Treasury',
                            'nik' => $nikuser['nik'],
                            'submit_pb' => 0,
                            'kirim_pb' => 1,
                            'waktu_kirim' => $timestamp,
                        ];
                        $this->m_log_email->insert($log_email);
                    } else {
                        $log_email = [
                            'id_log_email' => $cek_log_email['id_log_email'],
                            'submit_pb' => 0,
                            'kirim_pb' => 1,
                            'waktu_kirim' => $timestamp,
                        ];
                        $this->m_log_email->save($log_email);
                    }

                    $log_email_all = [
                        'id_transaksi' => $id_transaksi,
                        'title' => 'Data PB Telah Diperiksa Bagian Treasury',
                        'nik' => $nikuser['nik'],
                        'submit_pb' => 0,
                        'waktu_kirim' => $timestamp,
                    ];
                    $this->m_log_email_all->insert($log_email_all);
                    $this->m_id->save($data);

                    session()->setFlashdata('success', 'Data PB sedang direvisi');
                    return redirect()->to('dashboard/'.$id_transaksi);
                } else if ($role == 'gs') {
                    try {
                        //Server settings
                        $mail->SMTPDebug = SMTP::DEBUG_SERVER;
                        $mail->isSMTP();
                        $mail->Host       = 'mail.konimex.com';
                        $mail->SMTPAuth   = true;
                        $mail->Username   = PDLN_EMAIL;
                        $mail->Password   = PDLN_PASS;
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port       = 587;
                    
                        //Recipients
                        $mail->setFrom('noreply@konimex.com');
                        $mail->addAddress('renny.lowis@konimex.com');//Add a recipient (Treasury) renny.lowis@konimex.com
                        $mail->addCC('edy_santoso@konimex.com');//Add a recipient (Treasury Manager) edy_santoso@konimex.com
                        $mail->addBCC('09002@intra.net');//Add a recipient (BAS) 09002@intra.net
                    
                        //Content
                        $link='https://konimex.com:446/pdln/listpb/pb/'.$id_transaksi;
                        $mail->Subject = 'Data PB Telah Diperiksa Bagian GS';
                        $mail->Body    = "Dengan hormat,\n\n".
                        "Bagian GS telah selesai melakukan pengecekan data PB User bagian (".$niknmuser['niknm']."/".$nikuser['nik']."/".$nikuser['strorgnm']."), untuk melihat revisi data PB silahkan klik link: $link\n\n".
                        "Terima kasih.";
                    
                        $mail->send();
                        echo 'Message has been sent';
                    } catch (Exception $e) {
                        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                    }

                    $data = [
                        'id_transaksi' => $id_transaksi,
                        'submit_pb' => 1,
                    ];

                    $cek_log_email = $this->m_log_email->where('id_transaksi', $id_transaksi)->select('id_log_email')->first();

                    if (empty($cek_log_email)) {
                        $log_email = [
                            'id_transaksi' => $id_transaksi,
                            'title' => 'Data PB Telah Diperiksa Bagian GS',
                            'nik' => $nikuser['nik'],
                            'submit_pb' => 1,
                            'kirim_pb' => 1,
                            'waktu_kirim' => $timestamp,
                        ];
                        $this->m_log_email->insert($log_email);
                    } else {
                        $log_email = [
                            'id_log_email' => $cek_log_email['id_log_email'],
                            'submit_pb' => 1,
                            'kirim_pb' => 1,
                            'waktu_kirim' => $timestamp,
                        ];
                        $this->m_log_email->save($log_email);
                    }

                    $log_email_all = [
                        'id_transaksi' => $id_transaksi,
                        'title' => 'Data PB Telah Diperiksa Bagian GS',
                        'nik' => $nikuser['nik'],
                        'submit_pb' => 1,
                        'waktu_kirim' => $timestamp,
                    ];
                    $this->m_log_email_all->insert($log_email_all);
                    $this->m_id->save($data);

                    session()->setFlashdata('success', 'Data PB sedang direvisi');
                    return redirect()->to('dashboard/'.$id_transaksi);

                    // $cekdatapb = $this->m_kategori->cekdata2($id_transaksi, 'PB');

                    // if(empty($cekdatapb['jenis_biaya'])) {
                    //     // echo 'PB KOSONG - ';
                    // } else {
                    //     // echo 'PB ISI - ';
                    //     if ($cekdatapb['created_by'] == '05080') {
                    //         $data = [
                    //             'id_transaksi' => $id_transaksi,
                    //             'submit_pb' => 0,
                    //             'kirim_pb' => 0,
                    //         ];
                    //         $this->m_id->save($data);
                    //     } else {
                    //         $data = [
                    //             'id_transaksi' => $id_transaksi,
                    //             'submit_pb' => 1,
                    //         ];
                    //         $this->m_id->save($data);
                    //     }
                        
                    //     session()->setFlashdata('success', 'Data PB sedang direvisi');
                    //     return redirect()->to('dashboard/'.$id_transaksi);
                    // }
                }
            } else {
                $data = [
                    'id_transaksi' => $id_transaksi,
                    'nomor' => $this->request->getVar('nomor'),
                ];
                $this->m_pb->insert($data);
                
                session()->setFlashdata('success', 'No PB berhasil ditambahkan');
                return redirect()->to('listpb/'.$jenis_biaya.'/'.$id_transaksi);
            }
        }

        if($this->request->getVar('aksi')=='hapus' && $this->request->getVar('id_pb')){
            $dataPost = $this->m_pb->getPostId($id_transaksi, $this->request->getVar('id_pb'));
            if($dataPost['id_pb']){//memastikan bahwa ada data   
                if ($submit_pb['submit_pb'] == 0) {   
                    $aksi = $this->m_pb->deletePostId($this->request->getVar('id_pb'));
                    $cekidkategori = $this->m_kategori->where('id_transaksi', $id_transaksi)->where('jenis_biaya', 'Support')->select('id_kategori as id_kategori')->findAll();
                    foreach ($cekidkategori as $key => $value) {
                        $data_id = [
                            'id_kategori' => $value['id_kategori'],
                        ];
                        $this->m_kategori->deleteKategori($data_id['id_kategori']);
                    }
                } else {
                    return redirect()-> to("transaksi");
                }
                if($aksi == true){
                    $this->m_pb->query('ALTER TABLE pb AUTO_INCREMENT 1');
                    $this->m_kurs->query('ALTER TABLE kurs AUTO_INCREMENT 1');
                    $this->m_kategori->query('ALTER TABLE kategori AUTO_INCREMENT 1');
                    $this->m_biaya->query('ALTER TABLE biaya AUTO_INCREMENT 1');
                    session()->setFlashdata('success', "Data PB berhasil dihapus");
                } else {
                    session()->setFlashdata('warning', ['Data PB gagal dihapus']);
                }
            }

            $cek_kat = $this->m_kategori->where('id_transaksi', $id_transaksi)->select('id_kategori')->findAll();

            if (empty($cek_kat)) {
                $data = [
                    'id_transaksi' => $id_transaksi,
                    'submit_pb' => 0,
                    'kirim_pb' => 0,
                ];
                $this->m_id->save($data);
            } else {

            }

            return redirect()->to('listpb/'.$jenis_biaya.'/'.$id_transaksi);
        }

        $kota = $this->m_id->where('id_transaksi', $id_transaksi)->select('kota')->first();

        $cek = $this->m_kategori->cek($id_transaksi);

        if($role == 'treasury' || $role == 'gs'){
            if(empty($cek)) {
                return redirect()-> to("transaksi");
            }
        }

        $kategori = $this->m_kategori->where('id_transaksi', $id_transaksi)->where('jenis_biaya', $jenis_biaya)->select('id_pb, treasury, edited_by')->orderBy('baris', 'desc')->findAll();

        $nopb = $this->m_pb->where('id_transaksi', $id_transaksi)->select('created_by')->findAll();

        session()->set('url_transaksi', current_url());

        $data=[
            'header' => "List PB",
            'dataPost' => $dataPost,
            'id' => $id,
            'role' => $role,
            'kota' => $this->m_id->kota($id_transaksi),
            'solo' => $kota['kota'],
            'submit_pjum' => $submit_pjum['submit_pjum'],
            'submit_pb' => $submit_pb['submit_pb'],
            'submit' => $submit_pb['submit_pb'],
            'kirim_pb' => $submit_pb['kirim_pb'],
            'kategori' => $kategori,
            'neg'=> $this->m_negara_tujuan->getDataAllId($id_transaksi),
            'nomor' => $this->m_pb->nomor($id_transaksi),
            'valas' => $this->m_biaya->getDataValas($id_transaksi),
            'cek' => $cek,
            'cekpjum' => $this->m_kategori->alldataId($id_transaksi, 'PJUM'),
            'cekpb' => $this->m_kategori->alldataId($id_transaksi, 'PB'),
            'nopb' => $nopb,
        ];
        echo view('pb/v_listpb', $data);
        // print_r(session()->get());
    }

    public function datapb($jenis_biaya, $id_transaksi, $id_pb)
    {
        $nik= session()->get('akun_nik');
        $niknm= session()->get('niknm');
        $role= session()->get('akun_role');
        $strorg = session()->get('strorg');

        $login = $this->m_id->where('id_transaksi', $id_transaksi)->select('login_by')->first();

        if ($role == 'admin' && $login['login_by'] != $niknm || $role == 'user' && $login['login_by'] != $niknm) {
            session()->setFlashdata('warning', ['Id transaksi sedang diedit, harap menunggu beberapa saat lagi']);
            return redirect()->to("transaksi");
        } else {

        }

        $submit_pjum = $this->m_id->where('id_transaksi', $id_transaksi)->select('submit_pjum, kirim_pjum')->first();
        $submit_pb = $this->m_id->where('id_transaksi', $id_transaksi)->select('submit_pb, kirim_pb')->first();

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

        if ($role == 'gs' && $submit_pb['submit_pb'] < 2) {
            return redirect()-> to("transaksi");
        }

        $kat = $this->m_kategori->alldataId($id_transaksi, $jenis_biaya);
        if(empty($kat)) {
            session()->setFlashdata('warning', ['Tidak ada data, silahkan upload data biaya terlebih dahulu']);
            return redirect()-> to('dashboard/'.$id_transaksi);
        }

        $bia = $this->m_biaya->alldatapb($id_transaksi, $jenis_biaya, $id_pb);
        if(empty($bia)) {
            session()->setFlashdata('warning', ['Tidak ada data, silahkan upload data biaya terlebih dahulu']);
            return redirect()-> to('listpb/pb/'.$id_transaksi);
        }

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
            'id_pb' => $id_pb,
        ];
        
        session()->set($ses);

        if($jenis_biaya == 'pjum'){
            return redirect()-> to('listpb/pb/'.$id_transaksi);
        }

        $nopb = $this->m_pb->where('id_transaksi', $id_transaksi)->select('created_by')->findAll();
        foreach ($nopb as $nb => $nopb) {
            $created_by = $nopb['created_by'];
            if ($role == 'admin' && $created_by == '05080' || $role == 'user' && $created_by == '05080') {
                return redirect()-> to("transaksi");
            }
        }

        $valas = $this->m_biaya->valaspb($id_transaksi, $jenis_biaya, $id_pb);
        $kode_valas = $this->m_biaya->kode_valaspb($id_transaksi, $jenis_biaya, $id_pb);

        $sumBiaya = $this->m_biaya->totalpb($id_transaksi, $jenis_biaya, $id_pb);

        $kota = $this->m_id->where('id_transaksi', $id_transaksi)->select('kota')->first();
        $valas_kurs = $this->m_biaya->where('id_pb', $id_pb)->select('kode_valas')->first();

        $cek = $this->m_kategori->cek($id_transaksi);

        if($role == 'treasury' || $role == 'gs'){
            if(empty($cek)) {
                return redirect()-> to("transaksi");
            }
        }

        $nopb = $this->m_pb->where('id_transaksi', $id_transaksi)->select('created_by')->findAll();

        session()->set('url_transaksi', current_url());
        
        $data=[
            'header' => "Data PB",
            'dataPost' => $dataPost,
            'neg'=> $this->m_negara_tujuan->getDataAllId($id_transaksi),
            'id' => $id,
            'valas' => $this->m_biaya->getDataValas($id_transaksi),
            'kategori' => $this->m_kategori->alldataNomorpb($id_transaksi, $jenis_biaya, $id_pb),
            'nomor' => $this->m_pb->getData($id_transaksi, $id_pb),
            'kota' => $this->m_id->kota($id_transaksi),
            'solo' => $kota['kota'],
            'valas_kurs' => $valas_kurs['kode_valas'],
            'biaya' => $this->m_biaya->alldatapb($id_transaksi, $jenis_biaya, $id_pb),
            'biaya1' => $this->m_biaya->alldatapb($id_transaksi, $jenis_biaya, $id_pb),
            'kode_valas' => $kode_valas,
            'index' => count((array)$valas),
            'total' => $sumBiaya,
            'submit' => $submit_pb['submit_pb'],
            'kirim' => $submit_pb['kirim_pb'],
            'role' => $role,
            'submit_pjum' => $submit_pjum['submit_pjum'],
            'submit_pb' => $submit_pb['submit_pb'],
            'id_pb' => $id_pb,
            'id_pb_tanggal' => $id_pb,
            'cek' => $cek,
            'cekpjum' => $this->m_kategori->alldataId($id_transaksi, 'PJUM'),
            'cekpb' => $this->m_kategori->alldataId($id_transaksi, 'PB'),
            'kurs' => $this->m_kurs->getkurspb($id_pb),
            'nopb' => $nopb,
        ];
        echo view('pb/v_datapb', $data);
        // print_r(session()->get());
        // echo Currencies::getSymbol('THB');
    }

    public function editbiayapb($id_biaya, $id_kategori, $id_transaksi, $jenis_biaya, $id_pb)
    {
        $nik= session()->get('akun_nik');
        $niknm= session()->get('niknm');
        $role= session()->get('akun_role');
        $strorg = session()->get('strorg');

        $login = $this->m_id->where('id_transaksi', $id_transaksi)->select('login_by')->first();

        if ($role == 'admin' && $login['login_by'] != $niknm || $role == 'user' && $login['login_by'] != $niknm) {
            session()->setFlashdata('warning', ['Id transaksi sedang diedit, harap menunggu beberapa saat lagi']);
            return redirect()->to("transaksi");
        } else {

        }

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

        $kat = $this->m_kategori->alldataId($id_transaksi, $jenis_biaya);
        if(empty($kat)) {
            session()->setFlashdata('warning', ['Tidak ada data, silahkan upload data biaya terlebih dahulu']);
            return redirect()-> to('dashboard/'.$id_transaksi);
        }

        $bia = $this->m_biaya->alldatapb($id_transaksi, $jenis_biaya, $id_pb);
        if(empty($bia)) {
            session()->setFlashdata('warning', ['Tidak ada data, silahkan upload data biaya terlebih dahulu']);
            return redirect()-> to('listpb/pb/'.$id_transaksi);
        }

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
            'id_pb' => $id_pb,
        ];
        
        session()->set($ses);

        if($jenis_biaya == 'pjum'){
            return redirect()-> to('listpb/pb/'.$id_transaksi);
        }

        $nopb = $this->m_pb->where('id_transaksi', $id_transaksi)->select('created_by')->findAll();
        foreach ($nopb as $nb => $nopb) {
            $created_by = $nopb['created_by'];
            if ($role == 'admin' && $created_by == '05080' || $role == 'user' && $created_by == '05080') {
                return redirect()-> to("transaksi");
            }
        }

        if ($role == 'treasury' && $submit_pjum['submit_pjum'] == 0 && $submit_pb['submit_pb'] == 0) {
            return redirect()-> to("transaksi");
        } else if ($role == 'gs' && $submit_pb['submit_pb'] < 2) {
            return redirect()-> to("transaksi");
        }

        $valas = $this->m_biaya->valaspb($id_transaksi, $jenis_biaya, $id_pb);
        $kode_valas = $this->m_biaya->kode_valaspb($id_transaksi, $jenis_biaya, $id_pb);

        $sumBiaya = $this->m_biaya->totalpb($id_transaksi, $jenis_biaya, $id_pb);

        $kota = $this->m_id->where('id_transaksi', $id_transaksi)->select('kota')->first();

        $cek = $this->m_kategori->cek($id_transaksi);

        if($role == 'treasury'){
            if(empty($cek)) {
                return redirect()-> to("transaksi");
            }
        }

        if($role == 'admin' && $submit_pb['submit_pb'] != 0 || $role == 'user' && $submit_pb['submit_pb'] != 0) {
            return redirect()-> to("transaksi");
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
            $kirim = $this->m_id->where('id_transaksi', $id_transaksi)->select('kirim_pb')->first();

            if ($kirim['kirim_pb'] == 0) {
                $data = [
                    'id_biaya' => $id_biaya,
                    'biaya' => $string,
                ];
                $this->m_biaya->save($data);

                session()->setFlashdata('success', 'Biaya PB berhasil diubah');
                return redirect()->to('datapb/'.$jenis_biaya.'/'.$id_transaksi.'/'.$id_pb);
            } else {
                $kategori = [
                    'id_kategori' => $id_kategori,
                    'edited_at' => $timestamp,
                    'edited_by' => $nik,
                ];
                $this->m_kategori->save($kategori);

                $data = [
                    'id_biaya' => $id_biaya,
                    'biaya' => $string,
                    'edited_at' => $timestamp,
                    'edited_by' => $nik,
                ];
                $this->m_biaya->save($data);

                session()->setFlashdata('success', 'Biaya PB berhasil diubah');
                return redirect()->to('datapb/'.$jenis_biaya.'/'.$id_transaksi.'/'.$id_pb);
            }
        }

        $nopb = $this->m_pb->where('id_transaksi', $id_transaksi)->select('created_by')->findAll();

        session()->set('url_transaksi', current_url());
        
        $data=[
            'header' => "Edit Biaya PB",
            'id' => $id,
            'dataPost' => $dataPost,
            'biaya' => $this->m_biaya->allbiayapb($id_biaya),
            'role' => $role,
            'solo' => $kota['kota'],
            'submit' => $submit_pb['submit_pb'],
            'submit_pjum' => $submit_pjum['submit_pjum'],
            'submit_pb' => $submit_pb['submit_pb'],
            'cek' => $cek,
            'cekpjum' => $this->m_kategori->alldataId($id_transaksi, 'PJUM'),
            'cekpb' => $this->m_kategori->alldataId($id_transaksi, 'PB'),
            'id_pb' => $id_pb,
            'id_pb_tanggal' => $id_pb,
            'index' => count((array)$valas),
            'kode_valas' => $kode_valas,
            'nomor' => $this->m_pb->getData($id_transaksi, $id_pb),
            'neg' => $this->m_negara_tujuan->getDataAllId($id_transaksi),
            'nopb' => $nopb,
        ];
        echo view('proses/pb/v_biayapb', $data);
        // print_r(session()->get());
        // echo Currencies::getSymbol('THB');
    }

    public function kurspb($id_transaksi, $jenis_biaya, $id_pb)
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
        } else if($role == 'treasury'){
            $dataPost=$this->m_id->getTreasuryDashboard($id_transaksi);
        } else if($role == 'gs'){
            $dataPost=$this->m_id->getGSDashboard($id_transaksi);
        }

        if(empty($dataPost)) {
            return redirect()-> to("transaksi");
        }
        
        $data = $dataPost;

        $kat = $this->m_kategori->alldataId($id_transaksi, $jenis_biaya);
        if(empty($kat)) {
            session()->setFlashdata('warning', ['Tidak ada data, silahkan upload data biaya terlebih dahulu']);
            return redirect()-> to('dashboard/'.$id_transaksi);
        }

        $bia = $this->m_biaya->alldatapb($id_transaksi, $jenis_biaya, $id_pb);
        if(empty($bia)) {
            session()->setFlashdata('warning', ['Tidak ada data, silahkan upload data biaya terlebih dahulu']);
            return redirect()-> to('listpb/pb/'.$id_transaksi);
        }

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
            'id_pb' => $id_pb,
        ];
        
        session()->set($ses);

        if($jenis_biaya == 'pjum'){
            return redirect()-> to('listpb/pb/'.$id_transaksi);
        }

        $valas = $this->m_biaya->valaspb($id_transaksi, $jenis_biaya, $id_pb);
        $kode_valas = $this->m_biaya->kode_valaspb($id_transaksi, $jenis_biaya, $id_pb);

        $sumBiaya = $this->m_biaya->totalpb($id_transaksi, $jenis_biaya, $id_pb);

        $kota = $this->m_id->where('id_transaksi', $id_transaksi)->select('kota')->first();

        $cek = $this->m_kategori->cek($id_transaksi);

        if($role == 'treasury') {
            if(empty($cek)) {
                return redirect()-> to("transaksi");
            }
        }
        
        if ($role != 'treasury') {
            return redirect()-> to("transaksi");
        }

        if($this->request->getMethod() == 'post') {
            $data = $this->request->getVar(); //setiap yang diinputkan akan dikembalikan ke view

            $kurs = $this->request->getVar('kurs');
            $string = preg_replace('~[.,](?=\d{2}\b)|\p{Sc}~u', '#', $kurs);
            $string = strtr(rtrim($string, '#'), ['#' => '.', '.' => '', '.' => '']);
            $data = [
                'id_transaksi' => $id_transaksi,
                'id_pjum' => null,
                'id_pb' => $id_pb,
                'tanggal' => $this->request->getVar('tanggal'),
                'id_valas' => $this->request->getVar('id_valas'),
                'kode_valas' => $this->request->getVar('kode_valas'),
                'kurs' => $string,
            ];
            $this->m_kurs->insert($data);
            session()->setFlashdata('success', 'Kurs berhasil ditambahkan');
            return redirect()->to('datapb/'.$jenis_biaya.'/'.$id_transaksi.'/'.$id_pb);
        }

        $nopb = $this->m_pb->where('id_transaksi', $id_transaksi)->select('created_by')->findAll();

        session()->set('url_transaksi', current_url());
        
        $data=[
            'header' => "Kurs PB",
            'id' => $id,
            'dataPost' => $dataPost,
            'biaya' => $this->m_biaya->alldatapb($id_transaksi, $jenis_biaya, $id_pb),
            'role' => $role,
            'solo' => $kota['kota'],
            'submit' => $submit_pb['submit_pb'],
            'submit_pjum' => $submit_pjum['submit_pjum'],
            'submit_pb' => $submit_pb['submit_pb'],
            'cek' => $cek,
            'cekpjum' => $this->m_kategori->alldataId($id_transaksi, 'PJUM'),
            'cekpb' => $this->m_kategori->alldataId($id_transaksi, 'PB'),
            'id_pb' => $id_pb,
            'index' => count((array)$valas),
            'kode_valas' => $kode_valas,
            'nomor' => $this->m_pb->getData($id_transaksi, $id_pb),
            'neg' => $this->m_negara_tujuan->getDataAllId($id_transaksi),
            'kurs' => $this->m_kurs->getkurspb($id_pb),
            'nopb' => $nopb,
        ];
        echo view('proses/pb/v_kurspb', $data);
        // print_r(session()->get());
        // echo Currencies::getSymbol('THB');
    }

    public function editkurspb($id_transaksi, $jenis_biaya, $id_pb, $id_kurs)
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
        } else if($role == 'treasury'){
            $dataPost=$this->m_id->getTreasuryDashboard($id_transaksi);
        } else if($role == 'gs'){
            $dataPost=$this->m_id->getGSDashboard($id_transaksi);
        }

        if(empty($dataPost)) {
            return redirect()-> to("transaksi");
        }
        
        $data = $dataPost;

        $kat = $this->m_kategori->alldataId($id_transaksi, $jenis_biaya);
        if(empty($kat)) {
            session()->setFlashdata('warning', ['Tidak ada data, silahkan upload data biaya terlebih dahulu']);
            return redirect()-> to('dashboard/'.$id_transaksi);
        }

        $bia = $this->m_biaya->alldatapb($id_transaksi, $jenis_biaya, $id_pb);
        if(empty($bia)) {
            session()->setFlashdata('warning', ['Tidak ada data, silahkan upload data biaya terlebih dahulu']);
            return redirect()-> to('listpb/pb/'.$id_transaksi);
        }

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
            'id_pb' => $id_pb,
        ];
        
        session()->set($ses);

        if($jenis_biaya == 'pjum'){
            return redirect()-> to('listpb/pb/'.$id_transaksi);
        }

        if ($role != 'treasury') {
            return redirect()-> to("transaksi");
        }

        $valas = $this->m_biaya->valaspb($id_transaksi, $jenis_biaya, $id_pb);
        $kode_valas = $this->m_biaya->kode_valaspb($id_transaksi, $jenis_biaya, $id_pb);

        $sumBiaya = $this->m_biaya->totalpb($id_transaksi, $jenis_biaya, $id_pb);

        $kota = $this->m_id->where('id_transaksi', $id_transaksi)->select('kota')->first();

        $cek = $this->m_kategori->cek($id_transaksi);

        if($role == 'treasury'){
            if(empty($cek)) {
                return redirect()-> to("transaksi");
            }
        }

        if($this->request->getMethod() == 'post') {
            $data = $this->request->getVar(); //setiap yang diinputkan akan dikembalikan ke view

            $kurs = $this->request->getVar('kurs');
            $string = preg_replace('~[.,](?=\d{2}\b)|\p{Sc}~u', '#', $kurs);
            $string = strtr(rtrim($string, '#'), ['#' => '.', '.' => '', '.' => '']);
            $data = [
                'id_kurs' => $id_kurs,
                'tanggal' => $this->request->getVar('tanggal'),
                'kurs' => $string,
            ];
            $this->m_kurs->save($data);
            session()->setFlashdata('success', 'Kurs berhasil diubah');
            return redirect()->to('datapb/'.$jenis_biaya.'/'.$id_transaksi.'/'.$id_pb);
        }

        $nopb = $this->m_pb->where('id_transaksi', $id_transaksi)->select('created_by')->findAll();

        session()->set('url_transaksi', current_url());
        
        $data=[
            'header' => "Edit Kurs PB",
            'id' => $id,
            'dataPost' => $dataPost,
            'biaya' => $this->m_biaya->alldatapb($id_transaksi, $jenis_biaya, $id_pb),
            'role' => $role,
            'solo' => $kota['kota'],
            'submit' => $submit_pb['submit_pb'],
            'submit_pjum' => $submit_pjum['submit_pjum'],
            'submit_pb' => $submit_pb['submit_pb'],
            'cek' => $cek,
            'cekpjum' => $this->m_kategori->alldataId($id_transaksi, 'PJUM'),
            'cekpb' => $this->m_kategori->alldataId($id_transaksi, 'PB'),
            'id_pb' => $id_pb,
            'index' => count((array)$valas),
            'kode_valas' => $kode_valas,
            'nomor' => $this->m_pb->getData($id_transaksi, $id_pb),
            'neg' => $this->m_negara_tujuan->getDataAllId($id_transaksi),
            'kurs' => $this->m_kurs->getkurspb($id_pb),
            'nopb' => $nopb,
        ];
        echo view('proses/pb/v_kurspb', $data);
        // print_r(session()->get());
        // echo Currencies::getSymbol('THB');
    }

    public function editnopb($id_pb, $id_transaksi, $jenis_biaya)
    {
        if($this->request->getMethod()=='post') {
            $nik= session()->get('akun_nik');
            $timestamp = date('Y-m-d H:i:s');
            $kurs_bi = ['AUD', 'BND', 'CAD', 'CHF', 'CNH', 'CNY', 'DKK', 'EUR', 'GBP', 'HKD', 'JPY', 'KRW', 'KWD', 'LAK', 'MYR', 'NOK', 'NZD', 'PGK', 'PHP', 'SAR', 'SEK', 'SGD', 'THB', 'USD', 'VND'];

            $nomor = $this->request->getVar('nomor');
            $tanggal = $this->request->getVar('tanggal');
            $id_valas = $this->request->getVar('id_valas');
            $kode_valas = $this->request->getVar('kode_valas');

            $tanggal_date = (strtotime($tanggal));

            if(in_array(strtoupper($kode_valas), $kurs_bi)){
                $matauang = $kode_valas;
                $tanggal_mulai = $tanggal;
                $tanggal_selesai = $tanggal;

                $apiURL = 'https://www.bi.go.id/biwebservice/wskursbi.asmx/getSubKursLokal3?mts=' . $matauang . '&startdate=' . $tanggal_mulai . '&enddate=' . $tanggal_selesai;

                $curl = service('curlrequest');

                $response = $curl->request("GET", $apiURL, [
                    "headers" => [
                        "Accept" => "application/json"
                    ]
                ]);

                $contents = $response->getBody();

                $sxe = new \SimpleXMLElement($contents);
                $sxe->registerXPathNamespace('d', 'urn:schemas-microsoft-com:xml-diffgram-v1');
                $tables = $sxe->xpath('//Table[@d:id="Table1"]');

                if(empty($tables)) {
                    $i = 1;
                    while($i > 0) {
                        $tanggal_date = $tanggal_date - 86400;
                        $tanggal = date('Y-m-d', $tanggal_date);

                        // get table
                        $matauang = $kode_valas;
                        $tanggal_mulai = $tanggal;
                        $tanggal_selesai = $tanggal;

                        //jika hari libur atau sabtu minggu

                        $apiURL = 'https://www.bi.go.id/biwebservice/wskursbi.asmx/getSubKursLokal3?mts=' . $matauang . '&startdate=' . $tanggal_mulai . '&enddate=' . $tanggal_selesai;

                        $curl = service('curlrequest');

                        $response = $curl->request("GET", $apiURL, [
                            "headers" => [
                                "Accept" => "application/json"
                            ]
                        ]);

                        $contents = $response->getBody();

                        $sxe = new \SimpleXMLElement($contents);
                        $sxe->registerXPathNamespace('d', 'urn:schemas-microsoft-com:xml-diffgram-v1');
                        $tables = $sxe->xpath('//Table[@d:id="Table1"]');

                        if(empty($tables)) {
                            // do nothing, for lanjut
                        } else {
                            // baru jalan, ambil nilai
                            $tanggal = date('Y-m-d', $tanggal_date);
                            break;
                        }
                    }
                }

                foreach ($tables as $key => $value) {
                    $kurs_beli = (string)$value->children()->beli_subkurslokal;
                    $kurs_jual = (string)$value->children()->jual_subkurslokal;
                    $hitung_kurs_tengah = ($kurs_beli + $kurs_jual)/2;
                    $kurs_tengah = (string)round($hitung_kurs_tengah, 2);

                    $id_kurs = $this->m_kurs->where('id_pb', $id_pb)->select('id_kurs')->first();

                    if (empty($id_kurs)) {
                        $data_kurs = [
                            'id_transaksi' => $id_transaksi,
                            'id_pjum' => null,
                            'id_pb' => $id_pb,
                            'id_valas' => $id_valas,
                            'kode_valas' => $kode_valas,
                            'tanggal' => $tanggal,
                            'kurs' => $kurs_tengah,
                        ];
                        $this->m_kurs->insert($data_kurs);
                    } else if (!empty($id_kurs)) {
                        $data_kurs_edit = [
                            'id_kurs' => $id_kurs['id_kurs'],
                            'id_pjum' => null,
                            'id_pb' => $id_pb,
                            'id_valas' => $id_valas,
                            'kode_valas' => $kode_valas,
                            'tanggal' => $tanggal,
                            'kurs' => $kurs_tengah,
                        ];
                        $this->m_kurs->save($data_kurs_edit);
                    }
                }

                $tanggal = $this->request->getVar('tanggal');

                $data_pb = [
                    'id_pb' => $id_pb,
                    'id_valas' => $id_valas,
                    'kode_valas' => $kode_valas,
                    'nomor' => $nomor,
                    'tanggal' => $tanggal,
                    'edited_at' => $timestamp,
                    'edited_by' => $nik,
                ];
                $this->m_pb->save($data_pb);

                session()->setFlashdata('success', 'Nomor PB berhasil diubah');
                return redirect()->to('listpb/'.$jenis_biaya.'/'.$id_transaksi);
            } else {
                $tanggal = $this->request->getVar('tanggal');

                $data_pb = [
                    'id_pb' => $id_pb,
                    'id_valas' => $id_valas,
                    'kode_valas' => $kode_valas,
                    'nomor' => $nomor,
                    'tanggal' => $tanggal,
                    'edited_at' => $timestamp,
                    'edited_by' => $nik,
                ];
                $this->m_pb->save($data_pb);

                session()->setFlashdata('success', 'Nomor PB berhasil diubah');
                return redirect()->to('listpb/'.$jenis_biaya.'/'.$id_transaksi);
            }
        }

        // $kurs = $this->m_kurs->findAll();
        // $index_kurs = $this->m_kurs->where('id_valas', $id_valas)->where('tanggal', $tanggal)->select('id_valas, tanggal, kurs')->first();
        // if (empty($index_kurs)) {
        //     foreach ($kurs as $key => $value) {
        //         if (empty($id_kurs['id_kurs']) && $id_valas == $value['id_valas'] && $tanggal == $value['tanggal']) {
        //             $data_kurs = [
        //                 'id_transaksi' => $id_transaksi,
        //                 'id_pjum' => null,
        //                 'id_pb' => $id_pb,
        //                 'id_valas' => $id_valas,
        //                 'kode_valas' => $kode_valas,
        //                 'tanggal' => $tanggal,
        //                 'kurs' => $value['kurs'],
        //             ];
        //             // $this->m_kurs->insert($data_kurs);
        //         } 
                
        //         // else if (!empty($id_kurs['id_kurs']) && $id_valas == $value['id_valas'] && $tanggal == $value['tanggal']){
        //         //     $data_kurs_edit = [
        //         //         'id_kurs' => $id_kurs['id_kurs'],
        //         //         'id_transaksi' => $id_transaksi,
        //         //         'id_pjum' => null,
        //         //         'id_pb' => $id_pb,
        //         //         'id_valas' => $id_valas,
        //         //         'kode_valas' => $kode_valas,
        //         //         'tanggal' => $tanggal,
        //         //         'kurs' => $value['kurs'],
        //         //     ];
        //         //     $this->m_kurs->save($data_kurs_edit);
        //         // }
        //     }
        // } else {
        //     if (empty($id_kurs['id_kurs']) && $id_valas == $index_kurs['id_valas'] && $tanggal == $index_kurs['tanggal']) {
        //         $data_kurs = [
        //             'id_transaksi' => $id_transaksi,
        //             'id_pjum' => null,
        //             'id_pb' => $id_pb,
        //             'id_valas' => $id_valas,
        //             'kode_valas' => $kode_valas,
        //             'tanggal' => $tanggal,
        //             'kurs' => $index_kurs['kurs'],
        //         ];
        //         // $this->m_kurs->insert($data_kurs);
        //     }
            
        //     // else if (!empty($id_kurs['id_kurs']) && $id_valas == $index_kurs['id_valas'] && $tanggal == $index_kurs['tanggal']){
        //     //     $data_kurs_edit = [
        //     //         'id_kurs' => $id_kurs['id_kurs'],
        //     //         'id_transaksi' => $id_transaksi,
        //     //         'id_pjum' => null,
        //     //         'id_pb' => $id_pb,
        //     //         'id_valas' => $id_valas,
        //     //         'kode_valas' => $kode_valas,
        //     //         'tanggal' => $tanggal,
        //     //         'kurs' => $index_kurs['kurs'],
        //     //     ];
        //     //     $this->m_kurs->save($data_kurs_edit);
        //     // }
        // }
    }

    public function tanggalpb($id_pb, $id_transaksi, $jenis_biaya)
    {
        $nik= session()->get('akun_nik');
        $niknm= session()->get('niknm');
        $role= session()->get('akun_role');
        $strorg = session()->get('strorg');

        $login = $this->m_id->where('id_transaksi', $id_transaksi)->select('login_by')->first();

        if ($role == 'admin' && $login['login_by'] != $niknm || $role == 'user' && $login['login_by'] != $niknm) {
            session()->setFlashdata('warning', ['Id transaksi sedang diedit, harap menunggu beberapa saat lagi']);
            return redirect()->to("transaksi");
        } else {

        }

        $submit_pjum = $this->m_id->where('id_transaksi', $id_transaksi)->select('submit_pjum, kirim_pjum')->first();
        $submit_pb = $this->m_id->where('id_transaksi', $id_transaksi)->select('submit_pb, kirim_pb')->first();

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

        $kat = $this->m_kategori->alldataId($id_transaksi, $jenis_biaya);
        if(empty($kat)) {
            session()->setFlashdata('warning', ['Tidak ada data, silahkan upload data biaya terlebih dahulu']);
            return redirect()-> to('dashboard/'.$id_transaksi);
        }

        $bia = $this->m_biaya->alldatapb($id_transaksi, $jenis_biaya, $id_pb);
        if(empty($bia)) {
            session()->setFlashdata('warning', ['Tidak ada data, silahkan upload data biaya terlebih dahulu']);
            return redirect()-> to('listpb/pb/'.$id_transaksi);
        }

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
            'id_pb' => $id_pb,
        ];
        
        session()->set($ses);

        if($jenis_biaya == 'pjum'){
            return redirect()-> to('listpb/pb/'.$id_transaksi);
        }

        $nopb = $this->m_pb->where('id_transaksi', $id_transaksi)->select('created_by')->findAll();
        foreach ($nopb as $nb => $nopb) {
            $created_by = $nopb['created_by'];
            if ($role == 'admin' && $created_by == '05080' || $role == 'user' && $created_by == '05080') {
                return redirect()-> to("transaksi");
            }
        }

        $valas = $this->m_biaya->valaspb($id_transaksi, $jenis_biaya, $id_pb);
        $kode_valas = $this->m_biaya->kode_valaspb($id_transaksi, $jenis_biaya, $id_pb);

        $sumBiaya = $this->m_biaya->totalpb($id_transaksi, $jenis_biaya, $id_pb);

        $kota = $this->m_id->where('id_transaksi', $id_transaksi)->select('kota')->first();
        
        $cek = $this->m_kategori->cek($id_transaksi);

        if($role == 'treasury'){
            if(empty($cek)) {
                return redirect()-> to("transaksi");
            }
        }

        if($role == 'admin' && $submit_pb['submit_pb'] != 0 || $role == 'user' && $submit_pb['submit_pb'] != 0) {
            return redirect()-> to("transaksi");
        }

        if($this->request->getMethod() == 'post') {
            $nik= session()->get('akun_nik');
            $timestamp = date('Y-m-d H:i:s');
            $kurs_bi = ['AUD', 'BND', 'CAD', 'CHF', 'CNH', 'CNY', 'DKK', 'EUR', 'GBP', 'HKD', 'JPY', 'KRW', 'KWD', 'LAK', 'MYR', 'NOK', 'NZD', 'PGK', 'PHP', 'SAR', 'SEK', 'SGD', 'THB', 'USD', 'VND'];
            
            $tanggal = $this->request->getVar('tanggal');
            $id_valas = $this->request->getVar('id_valas');
            $kode_valas = $this->request->getVar('kode_valas');

            $tanggal_date = (strtotime($tanggal));

            if(in_array(strtoupper($kode_valas), $kurs_bi)){
                $matauang = $kode_valas;
                $tanggal_mulai = $tanggal;
                $tanggal_selesai = $tanggal;

                $apiURL = 'https://www.bi.go.id/biwebservice/wskursbi.asmx/getSubKursLokal3?mts=' . $matauang . '&startdate=' . $tanggal_mulai . '&enddate=' . $tanggal_selesai;

                $curl = service('curlrequest');

                $response = $curl->request("GET", $apiURL, [
                    "headers" => [
                        "Accept" => "application/json"
                    ]
                ]);

                $contents = $response->getBody();

                $sxe = new \SimpleXMLElement($contents);
                $sxe->registerXPathNamespace('d', 'urn:schemas-microsoft-com:xml-diffgram-v1');
                $tables = $sxe->xpath('//Table[@d:id="Table1"]');

                if(empty($tables)) {
                    $i = 1;
                    while($i > 0) {
                        $tanggal_date = $tanggal_date - 86400;
                        $tanggal = date('Y-m-d', $tanggal_date);

                        // get table
                        $matauang = $kode_valas;
                        $tanggal_mulai = $tanggal;
                        $tanggal_selesai = $tanggal;

                        //jika hari libur atau sabtu minggu

                        $apiURL = 'https://www.bi.go.id/biwebservice/wskursbi.asmx/getSubKursLokal3?mts=' . $matauang . '&startdate=' . $tanggal_mulai . '&enddate=' . $tanggal_selesai;

                        $curl = service('curlrequest');

                        $response = $curl->request("GET", $apiURL, [
                            "headers" => [
                                "Accept" => "application/json"
                            ]
                        ]);

                        $contents = $response->getBody();

                        $sxe = new \SimpleXMLElement($contents);
                        $sxe->registerXPathNamespace('d', 'urn:schemas-microsoft-com:xml-diffgram-v1');
                        $tables = $sxe->xpath('//Table[@d:id="Table1"]');

                        if(empty($tables)) {
                            // do nothing, for lanjut
                        } else {
                            // baru jalan, ambil nilai
                            $tanggal = date('Y-m-d', $tanggal_date);
                            break;
                        }
                    }
                }

                foreach ($tables as $key => $value) {
                    $kurs_beli = (string)$value->children()->beli_subkurslokal;
                    $kurs_jual = (string)$value->children()->jual_subkurslokal;
                    $hitung_kurs_tengah = ($kurs_beli + $kurs_jual)/2;
                    $kurs_tengah = (string)round($hitung_kurs_tengah, 2);

                    $id_kurs = $this->m_kurs->where('id_pb', $id_pb)->select('id_kurs')->first();

                    if (empty($id_kurs)) {
                        $data_kurs = [
                            'id_transaksi' => $id_transaksi,
                            'id_pjum' => null,
                            'id_pb' => $id_pb,
                            'id_valas' => $id_valas,
                            'kode_valas' => $kode_valas,
                            'tanggal' => $tanggal,
                            'kurs' => $kurs_tengah,
                        ];
                        $this->m_kurs->insert($data_kurs);
                    } else if (!empty($id_kurs)) {
                        $data_kurs_edit = [
                            'id_kurs' => $id_kurs['id_kurs'],
                            'id_pjum' => null,
                            'id_pb' => $id_pb,
                            'id_valas' => $id_valas,
                            'kode_valas' => $kode_valas,
                            'tanggal' => $tanggal,
                            'kurs' => $kurs_tengah,
                        ];
                        $this->m_kurs->save($data_kurs_edit);
                    }
                }

                $tanggal = $this->request->getVar('tanggal');

                $data_pb = [
                    'id_pb' => $id_pb,
                    'id_valas' => $id_valas,
                    'kode_valas' => $kode_valas,
                    'tanggal' => $tanggal,
                    'edited_at' => $timestamp,
                    'edited_by' => $nik,
                ];
                $this->m_pb->save($data_pb);

                session()->setFlashdata('success', 'Tanggal Pembuatan No PB berhasil ditambahkan');
                return redirect()->to('datapb/'.$jenis_biaya.'/'.$id_transaksi.'/'.$id_pb);
            } else {
                $tanggal = $this->request->getVar('tanggal');

                $data_pb = [
                    'id_pb' => $id_pb,
                    'id_valas' => $id_valas,
                    'kode_valas' => $kode_valas,
                    'tanggal' => $tanggal,
                    'edited_at' => $timestamp,
                    'edited_by' => $nik,
                ];
                $this->m_pb->save($data_pb);

                session()->setFlashdata('success', 'Tanggal Pembuatan No PB berhasil ditambahkan');
                return redirect()->to('datapb/'.$jenis_biaya.'/'.$id_transaksi.'/'.$id_pb);
            }
        }

        $nopb = $this->m_pb->where('id_transaksi', $id_transaksi)->select('created_by')->findAll();

        session()->set('url_transaksi', current_url());
        
        $data=[
            'header' => "Tanggal Pembuatan No PB",
            'id' => $id,
            'dataPost' => $dataPost,
            'biaya' => $this->m_biaya->alldatapb($id_transaksi, $jenis_biaya, $id_pb),
            'role' => $role,
            'solo' => $kota['kota'],
            'submit' => $submit_pb['submit_pb'],
            'submit_pjum' => $submit_pjum['submit_pjum'],
            'submit_pb' => $submit_pb['submit_pb'],
            'cek' => $cek,
            'cekpjum' => $this->m_kategori->alldataId($id_transaksi, 'PJUM'),
            'cekpb' => $this->m_kategori->alldataId($id_transaksi, 'PB'),
            'id_pb' => $id_pb,
            'id_pb_tanggal' => $id_pb,
            'index' => count((array)$valas),
            'kode_valas' => $kode_valas,
            'nomor' => $this->m_pb->getData($id_transaksi, $id_pb),
            'neg' => $this->m_negara_tujuan->getDataAllId($id_transaksi),
            'kurs' => $this->m_kurs->getkurspb($id_pb),
            'nomor1' => $this->m_pb->getData($id_transaksi, $id_pb),
            'valas1' => $this->m_biaya->getDataValas($id_transaksi),
            'nopb' => $nopb,
        ];
        echo view('proses/pb/v_tanggalpb', $data);
        // print_r(session()->get());
        // echo Currencies::getSymbol('THB');
    }

    public function importpb($jenis_biaya, $id_transaksi, $id_pb)
    {
        $file = $this->request->getFile('file_excel_pb');
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

        // echo("<pre>");
        // echo $this->m_kategori->getLastQuery()->getQuery();
        // echo("<pre>");

        $a=1;
        $b=1;
        $c=1;
        $d=1;

        for($r = 7; $r < $highestRow; $r++) {
            $tanggal = $sheet[$r][1];
            if (empty($tanggal)) {
                $tanggal = null;
            }
            $kategori = $sheet[$r][2];
            if (empty($kategori)) {
                $kategori = null;
            }
            $status = $sheet[$r][3];
            if (empty($status)) {
                $status = null;
            }
            $ref = $sheet[$r][4];
            if (empty($ref)) {
                $ref = null;
            }
            $note = $sheet[$r][5];
            if (empty($note)) {
                $note = null;
            }
            $jumlah_personil = $sheet[$r][6];
            if (empty($jumlah_personil)) {
                $jumlah_personil = null;
            }
            $nopb = $this->m_pb->where('id_pb', $id_pb)->select('nomor as nomor')->first();

            $cekdata = $this->m_kategori->cekdatapb($id_transaksi, $r+1, $jenis_biaya, $id_pb);

            $data = [];
            if(empty($cekdata)) {
                $data = [
                    'baris' => $r+1,
                    'id_transaksi' => $id_transaksi,
                    'id_pb' => $id_pb,
                    'jenis_biaya' => 'PB',
                    'kategori' => $kategori,
                    'status' => $status,
                    'tanggal' => $tanggal,
                    'note' => $note,
                    'ref' => $ref,
                    'jumlah_personil' => $jumlah_personil,
                ];
                $this->m_kategori->insert($data);
            } else if(empty($cekdata['baris'])) {
                continue;
            } else if($id_transaksi == $cekdata['id_transaksi'] && $r+1 == $cekdata['baris'] && "PB" == $cekdata['jenis_biaya'] && $id_pb == $cekdata['id_pb']) {
                $resultKategori = $this->m_kategori->where('id_transaksi', $id_transaksi)->where('baris', $r+1)->where('jenis_biaya', $jenis_biaya)->where('id_pb', $id_pb)->select('id_kategori as id_kategori')->first();
                $data = [
                    'id_kategori' => $resultKategori['id_kategori'],
                    'kategori' => $kategori,
                    'status' => $status,
                    'tanggal' => $tanggal,
                    'note' => $note,
                    'ref' => $ref,
                    'jumlah_personil' => $jumlah_personil,
                ];
                $this->m_kategori->distinct($data['id_kategori']);
                $this->m_kategori->save($data);
            }
            
            // if($sheet[$r][0] == null) {
            //     $this->m_kategori->query("SET foreign_key_checks = 0;");
            //     $this->m_kategori->truncate('kategori');
            // }

            for($k = 7; $k < $highestColumnIndex; ++$k) {
                $data = [];
                $kode_valas = $sheet[6][$k];
                $biaya = preg_replace("/[^0-9\.]/", "", $sheet[$r][$k]);
                $id_valas = $this->m_valas->where('kode_valas', $kode_valas)->select('id_valas as id_valas')->first();
                $simbol = $this->m_valas->where('kode_valas', $kode_valas)->select('simbol as simbol')->first();
                $kategori = $sheet[$r][2];

                if (empty($biaya)) {
                    $biaya = 0;
                }
                if (empty($kode_valas)) {
                    $kode_valas = null;
                    $id_valas = null;
                    $simbol = null;
                }
                if (empty($kategori)) {
                    $kategori = null;
                }

                $resultKategori = $this->m_kategori->where('id_transaksi', $id_transaksi)->where('baris', $r+1)->where('jenis_biaya', $jenis_biaya)->where('id_pb', $id_pb)->select('id_kategori as id_kategori')->first();
                
                $cekvaluta = $this->m_biaya->cekvalutapb($id_transaksi, $r+1, $k+1, $jenis_biaya, $id_pb);
                if(empty($cekvaluta)) {
                    $data = [
                        'id_kategori' => $resultKategori['id_kategori'],
                        'id_pb' => $id_pb,
                        'baris' => $r+1,
                        'kolom' => $k+1,
                        'kategori' => $kategori,
                        'id_transaksi' => $id_transaksi,
                        'id_valas' => $id_valas,
                        'kode_valas' => $kode_valas,
                        'simbol' => $simbol,
                        'jenis_biaya' => 'PB',
                        'biaya' => $biaya,
                    ];
                    $this->m_biaya->insert($data);
                } else if(empty($cekvaluta['baris'])) {
                    
                } else if(empty($cekvaluta['kolom'])) {
                    
                } else if($id_transaksi == $cekvaluta['id_transaksi'] && $r+1 == $cekvaluta['baris'] && $k+1 == $cekvaluta['kolom'] && "PB" == $cekvaluta['jenis_biaya'] && $id_pb == $cekvaluta['id_pb']) {
                    $resultBiaya = $this->m_biaya->where('id_transaksi', $id_transaksi)->where('baris', $r+1)->where('kolom', $k+1)->where('jenis_biaya', $jenis_biaya)->where('id_pb', $id_pb)->select('id_biaya as id_biaya')->first();
                    $data = [
                        'id_biaya' => $resultBiaya['id_biaya'],
                        'id_kategori' => $resultKategori['id_kategori'],
                        'kategori' => $kategori,
                        'id_valas' => $id_valas,
                        'kode_valas' => $kode_valas,
                        'simbol' => $simbol,
                        'jenis_biaya' => 'PB',
                        'biaya' => $biaya,
                    ];
                    $this->m_biaya->distinct($data['id_biaya']);
                    $this->m_biaya->save($data);
                }

                // if($sheet[$r][0] == null) {
                //     $this->m_biaya->query("SET foreign_key_checks = 0;");
                //     $this->m_biaya->truncate('biaya');
                // }
            }
        }
        session()->setFlashdata('success', 'Data PB berhasil di import');
        return redirect()->to('datapb/'.$jenis_biaya.'/'.$id_transaksi.'/'.$id_pb);
    }

    public function exportpb($jenis_biaya, $id_transaksi, $id_pb)
    {
        $kategori = $this->m_kategori->getDataIdtransaksipb($id_transaksi, $jenis_biaya, $id_pb);
        $biaya = $this->m_biaya->getDataBiayapb($id_transaksi, $jenis_biaya, $id_pb);

        $bawah = $this->m_kategori->where('id_transaksi', $id_transaksi)->where('jenis_biaya', $jenis_biaya)->where('id_pb', $id_pb)->select('baris')->orderBy('id_kategori', 'desc')->first();
        $baris = $this->m_kategori->where('id_transaksi', $id_transaksi)->where('jenis_biaya', $jenis_biaya)->where('id_pb', $id_pb)->select('baris')->orderBy('id_kategori', 'asc')->findAll();
        $nik = $this->m_id->where('id_transaksi', $id_transaksi)->select('nik')->first();
        $nomor = $this->m_pb->where('id_transaksi', $id_transaksi)->where('id_pb', $id_pb)->select('nomor')->first();

        $nomorpb = $nomor['nomor'];
        if ($nomorpb == null) {
            $nomorpb = "no pb masih kosong";
        }

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName('Times New Roman');
        $spreadsheet->getDefaultStyle()->getFont()->setSize(12);
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle("Data PB");

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

        $arr1 = implode(' ', array_map(function ($entry) {
            return ($entry[key($entry)]);
        }, $kategori));

        $exp1 = explode(' ', $arr1);
        
        $sheet->setCellValue('B1', 'PERJALANAN DINAS LUAR NEGERI '.substr($nik_perso, 0, -1).'_'.$id_transaksi);
        $sheet->setCellValue('B4', 'Negara Tujuan =>');
        $sheet->setCellValue('C4', substr($tmp_negara, 0, -2));
        $sheet->setCellValue('B6', 'Tanggal');
        $sheet->setCellValue('C6', 'Jenis_biaya');
        $sheet->setCellValue('D6', 'Kategori');
        $sheet->setCellValue('E6', 'Status');
        $sheet->setCellValue('F6', 'Ref');
        $sheet->setCellValue('G6', 'Note');
        $sheet->setCellValue('H6', 'No PB');
        $sheet->setCellValue('I6', 'Jumlah Personil');
        $sheet->setCellValue('J6', 'Valas');

        $array = array('I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK');
        $valas = $this->m_biaya->valaspb($id_transaksi, $jenis_biaya, $id_pb);
        $count = count((array)$valas);
        $alpha = $array[$count];

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
            $column = $value['kolom'] - 1;
            $sheet->setCellValueByColumnAndRow($column, $i, $value['kode_valas']);
            $i++;
            for ($j=8; $j <= $bawah['baris']; $j++) {
                $bia = $value['biaya'];
                if ($bia == 0) {
                    $bia = null;
                }
                $sheet->setCellValueByColumnAndRow($column, $value['baris'], $bia);
            }
        }

        foreach ($kategori as $key => $value) {
            foreach ($baris as $b => $bar) {
                $sheet->setCellValue('B'.$bar['baris'], $value['tanggal']);
                $sheet->setCellValue('C'.$bar['baris'], $value['jenis_biaya']);
                $sheet->setCellValue('D'.$bar['baris'], $value['kategori']);
                $sheet->setCellValue('E'.$bar['baris'], $value['status']);
                $sheet->setCellValue('F'.$bar['baris'], $value['ref']);
                $sheet->setCellValue('G'.$bar['baris'], $value['note']);
                $sheet->setCellValue('H'.$bar['baris'], $nomor['nomor']);
                $sheet->setCellValue('I'.$bar['baris'], $value['jumlah_personil']);
                $sheet->getStyle('B6:'.$alpha.$bar['baris'])->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            }
        }

        for ($k = 'B'; $k <= $alpha; $k++) {
            $spreadsheet->getActiveSheet()->getColumnDimension($k)->setWidth(20);
        }

        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('A')->setVisible(false);

        $sheet->getStyle('B:I')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('B:I')->getAlignment()->setVertical('center');
        $sheet->getStyle('J:'.$alpha)->getAlignment()->setHorizontal('center');
        $sheet->getStyle('J:'.$alpha)->getNumberFormat()->setFormatCode('#,##0.00');

        $writer = new Xls($spreadsheet);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename=PB Perjalanan Dinas Luar Negeri/'.substr($nik_perso, 0, -1).'/'.$id_transaksi.'/'.$nomorpb.'.xls');
        $writer->save("php://output");
        // $writer->save('PB '.substr($nik_perso, 0, -1).'_'.$id_transaksi.'.xls');
        // return redirect()->to('datapb/'.$jenis_biaya.'/'.$id_transaksi.'/'.$id_pb);
    }

    public function treasurypb($id_kategori, $id_transaksi, $jenis_biaya, $id_pb)
    {
        $nik= session()->get('akun_nik');
        $role= session()->get('akun_role');
        $strorg = session()->get('strorg');

        $submit_pjum = $this->m_id->where('id_transaksi', $id_transaksi)->select('submit_pjum, kirim_pjum')->first();
        $submit_pb = $this->m_id->where('id_transaksi', $id_transaksi)->select('submit_pb, kirim_pb')->first();
        
        if ($role == 'treasury'){
            $dataPost=$this->m_id->getTreasuryDashboard($id_transaksi);
        }

        if(empty($dataPost)) {
            return redirect()-> to("transaksi");
        }

        $data = $dataPost;

        if($role == 'treasury'){
            $id=$this->m_id->getTreasury($id_transaksi);
        }

        $cek = $this->m_kategori->cek($id_transaksi);

        if($role == 'treasury') {
            if(empty($cek)) {
                return redirect()-> to("transaksi");
            }
        }
        
        if ($role != 'treasury') {
            return redirect()-> to("transaksi");
        }

        if($jenis_biaya == 'pjum'){
            return redirect()-> to('datapb/'.$jenis_biaya.'/'.$id_transaksi.'/'.$id_pb);
        }

        if($this->request->getVar('aksi')=='hapus' && $this->request->getVar('id_kategori')){
            $record = [
                'id_kategori' => $id_kategori,
                'treasury' => null,
                'edited_at' => null,
                'edited_by' => null,
            ];
            $this->m_kategori->save($record);
            session()->setFlashdata('success', 'Note Treasury berhasil dihapus');
            return redirect()->to('datapb/'.$jenis_biaya.'/'.$id_transaksi.'/'.$id_pb);
        }

        $valas = $this->m_biaya->valas($id_transaksi, $jenis_biaya);
        $kode_valas = $this->m_biaya->kode_valaspb($id_transaksi, $jenis_biaya, $id_pb);

        if($this->request->getMethod()=='post') {
            $data = $this->request->getVar(); //setiap yang diinputkan akan dikembalikan ke view

            $record = [
                'id_kategori' => $id_kategori,
                'treasury' => $this->request->getVar('treasury'),
                'edited_at' => null,
                'edited_by' => null,
            ];
            $this->m_kategori->save($record);
            session()->setFlashdata('success', 'Note Treasury berhasil ditambahkan');
            return redirect()->to('datapb/'.$jenis_biaya.'/'.$id_transaksi.'/'.$id_pb);
        }

        $cek = $this->m_kategori->cek($id_transaksi);

        if($role == 'treasury'){
            if(empty($cek)) {
                return redirect()-> to("transaksi");
            }
        }

        $nopb = $this->m_pb->where('id_transaksi', $id_transaksi)->select('created_by')->findAll();

        session()->set('url_transaksi', current_url());
        
        $data=[
            'header' => "Note Treasury PB",
            'id' => $id,
            'neg'=> $this->m_negara_tujuan->getDataAllId($id_transaksi),
            'dataPost' => $dataPost,
            'kategori' => $this->m_kategori->alldataId($id_transaksi, $jenis_biaya),
            'index' => count((array)$valas),
            'kode_valas' => $kode_valas,
            'nomor' => $this->m_pb->getData($id_transaksi, $id_pb),
            'id_pb' => $id_pb,
            'notetreasury' => $this->m_kategori->getDataEdit($id_kategori, $id_transaksi, $jenis_biaya, $id_pb),
            'role' => $role,
            'submit_pjum' => $submit_pjum['submit_pjum'],
            'submit_pb' => $submit_pb['submit_pb'],
            'cek' => $cek,
            'cekpjum' => $this->m_kategori->alldataId($id_transaksi, 'PJUM'),
            'cekpb' => $this->m_kategori->alldataId($id_transaksi, 'PB'),
            'nopb' => $nopb,
        ];
        echo view('pb/v_treasurypb', $data);
        // print_r(session()->get());       
    }

    public function treasuryselesaipb($jenis_biaya, $id_transaksi)
    {
        $role= session()->get('akun_role');

        if($role != 'treasury'){
            return redirect()-> to("transaksi");
        }

        $id_pb = $this->m_biaya->where('id_transaksi', $id_transaksi)->select('id_pb, id_valas')->findAll();

        foreach ($id_pb as $key => $value) {
            if ($value['id_pb'] != null && $value['id_valas'] != 76) {
                $kurs = $this->m_kurs->where('id_pb', $value['id_pb'])->select('id_kurs, id_valas, id_pb, kode_valas, tanggal, kurs')->findAll();
                if(empty($kurs)){
                    session()->setFlashdata('warning', ['Tambahkan kurs terlebih dahulu untuk melakukan submit data']);
                    return redirect()-> to('listpb/pb/'.$id_transaksi);
                }
            }
        }

        $mail = new PHPMailer(true);
        $nikuser = $this->m_id->where('id_transaksi', $id_transaksi)->select('nik, strorgnm')->first();
        $niknmuser = $this->m_am21->where('nik', $nikuser['nik'])->select('niknm')->first();
        try {
            //Server settings
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;
            $mail->isSMTP();
            $mail->Host       = 'mail.konimex.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = PDLN_EMAIL;
            $mail->Password   = PDLN_PASS;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
        
            //Recipients
            $mail->setFrom('noreply@konimex.com');
            $mail->addAddress('gsta@konimex.com');//Add a recipient (GSTA) gsta@konimex.com
            $mail->addCC('eriwati@konimex.com');//Add a recipient (GS Manager) eriwati@konimex.com
            $mail->addCC('djiangming@konimex.com');//Add a recipient (GS Officer) djiangming@konimex.com
            $mail->addBCC('09002@intra.net');//Add a recipient (BAS) 09002@intra.net
        
            //Content
            $link='https://konimex.com:446/pdln/listpb/pb/'.$id_transaksi;
            $mail->Subject = 'Data PB Telah Divalidasi Bagian Treasury';
            $mail->Body    = "Dengan hormat,\n\n".
            "Bagian Treasury telah melakukan validasi data PB User bagian (".$niknmuser['niknm']."/".$nikuser['nik']."/".$nikuser['strorgnm']."), untuk melihat data PB silahkan klik link: $link\n\n".
            "Terima kasih.";
        
            $mail->send();
            echo 'Message has been sent';
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }

        $data = [
            'id_transaksi' => $id_transaksi,
            'submit_pb' => 2,
            'kirim_pb' => 1,
        ];

        $timestamp = date('Y-m-d H:i:s');

        $cek_log_email = $this->m_log_email->where('id_transaksi', $id_transaksi)->select('id_log_email')->first();

        if (empty($cek_log_email)) {
            $log_email = [
                'id_transaksi' => $id_transaksi,
                'title' => 'Data PB Telah Divalidasi Bagian Treasury',
                'nik' => $nikuser['nik'],
                'submit_pb' => 2,
                'kirim_pb' => 1,
                'waktu_kirim' => $timestamp,
            ];
            $this->m_log_email->insert($log_email);
        } else {
            $log_email = [
                'id_log_email' => $cek_log_email['id_log_email'],
                'submit_pb' => 2,
                'kirim_pb' => 1,
                'waktu_kirim' => $timestamp,
            ];
            $this->m_log_email->save($log_email);
        }

        $log_email_all = [
            'id_transaksi' => $id_transaksi,
            'title' => 'Data PB Telah Divalidasi Bagian Treasury',
            'nik' => $nikuser['nik'],
            'submit_pb' => 2,
            'waktu_kirim' => $timestamp,
        ];
        $this->m_log_email_all->insert($log_email_all);
        $this->m_id->save($data);

        session()->setFlashdata('success', 'Data PB berhasil disubmit');
        return redirect()->to('dashboard/'.$id_transaksi);
    }

    public function gsselesaipb($jenis_biaya, $id_transaksi)
    {
        $role= session()->get('akun_role');
        if($role != 'gs'){
            return redirect()-> to("transaksi");
        }

        $id_pb = $this->m_biaya->where('id_transaksi', $id_transaksi)->select('id_pb, biaya')->findAll();

        foreach ($id_pb as $key => $value) {
            if ($value['id_pb'] != null) {
                if($value['biaya'] == 0){
                    session()->setFlashdata('warning', ['Masukkan biaya terlebih dahulu untuk melakukan submit data']);
                    return redirect()-> to('listpb/pb/'.$id_transaksi);
                }
            }
        }

        $kota = $this->m_id->where('id_transaksi', $id_transaksi)->select('kota')->first();

        $timestamp = date('Y-m-d H:i:s');

        $nikuser = $this->m_id->where('id_transaksi', $id_transaksi)->select('nik, strorgnm')->first();

        if($kota['kota'] == 'Surakarta'){
            $data = [
                'id_transaksi' => $id_transaksi,
                'submit_pb' => 4,
            ];

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
        } else {
            $data = [
                'id_transaksi' => $id_transaksi,
                'submit_pb' => 3,
            ];

            $cek_log_email = $this->m_log_email->where('id_transaksi', $id_transaksi)->select('id_log_email')->first();
    
            if (empty($cek_log_email)) {
                $log_email = [
                    'id_transaksi' => $id_transaksi,
                    'title' => 'Lengkapi Biaya Support Data PB',
                    'nik' => $nikuser['nik'],
                    'submit_pb' => 3,
                    'kirim_pb' => 1,
                    'waktu_kirim' => $timestamp,
                ];
                $this->m_log_email->insert($log_email);
            } else {
                $log_email = [
                    'id_log_email' => $cek_log_email['id_log_email'],
                    'submit_pb' => 3,
                    'kirim_pb' => 1,
                    'waktu_kirim' => $timestamp,
                ];
                $this->m_log_email->save($log_email);
            }
    
            $log_email_all = [
                'id_transaksi' => $id_transaksi,
                'title' => 'Lengkapi Biaya Support Data PB',
                'nik' => $nikuser['nik'],
                'submit_pb' => 3,
                'waktu_kirim' => $timestamp,
            ];
            $this->m_log_email_all->insert($log_email_all);
            $this->m_id->save($data);
        }
        session()->setFlashdata('success', 'Data PB berhasil disubmit');
        return redirect()->to('listpb/'.$jenis_biaya.'/'.$id_transaksi);
    }

    public function tanggalp($id_pb, $id_transaksi, $jenis_biaya)
    {
        if($this->request->getMethod()=='post') {
            $nik= session()->get('akun_nik');
            $timestamp = date('Y-m-d H:i:s');

            $nomor = $this->request->getVar('nomor');
            $tanggal = $this->request->getVar('tanggal');
            $id_valas = $this->request->getVar('id_valas');
            $kode_valas = $this->request->getVar('kode_valas');

            $tanggal_date = (strtotime($tanggal));

            if($kode_valas != 'IDR' || $kode_valas != 'KHR' || $kode_valas != 'MMK') {
                $kurs = $this->m_kurs->findAll();
                $id_kurs = $this->m_kurs->where('id_pb', $id_pb)->select('id_kurs')->first();
                $index_kurs = $this->m_kurs->where('id_valas', $id_valas)->where('tanggal', $tanggal)->select('id_valas, tanggal, kurs')->first();

                $matauang = $kode_valas;
                $tanggal_mulai = $tanggal;
                $tanggal_selesai = $tanggal;

                $apiURL = 'https://www.bi.go.id/biwebservice/wskursbi.asmx/getSubKursLokal3?mts=' . $matauang . '&startdate=' . $tanggal_mulai . '&enddate=' . $tanggal_selesai;

                $curl = service('curlrequest');

                $response = $curl->request("GET", $apiURL, [
                    "headers" => [
                        "Accept" => "application/json"
                    ]
                ]);

                $contents = $response->getBody();

                $sxe = new \SimpleXMLElement($contents);
                $sxe->registerXPathNamespace('d', 'urn:schemas-microsoft-com:xml-diffgram-v1');
                $tables = $sxe->xpath('//Table[@d:id="Table1"]');

                if(empty($tables)) {
                    $i = 1;
                    while($i > 0) {
                        $tanggal_date = $tanggal_date - 86400;
                        $tanggal = date('Y-m-d', $tanggal_date);

                        // get table
                        $kurs = $this->m_kurs->findAll();
                        $id_kurs = $this->m_kurs->where('id_pb', $id_pb)->select('id_kurs')->first();
                        $index_kurs = $this->m_kurs->where('id_valas', $id_valas)->where('tanggal', $tanggal)->select('id_valas, tanggal, kurs')->first();

                        $matauang = $kode_valas;
                        $tanggal_mulai = $tanggal;
                        $tanggal_selesai = $tanggal;

                        //jika hari libur atau sabtu minggu

                        $apiURL = 'https://www.bi.go.id/biwebservice/wskursbi.asmx/getSubKursLokal3?mts=' . $matauang . '&startdate=' . $tanggal_mulai . '&enddate=' . $tanggal_selesai;

                        $curl = service('curlrequest');

                        $response = $curl->request("GET", $apiURL, [
                            "headers" => [
                                "Accept" => "application/json"
                            ]
                        ]);

                        $contents = $response->getBody();

                        $sxe = new \SimpleXMLElement($contents);
                        $sxe->registerXPathNamespace('d', 'urn:schemas-microsoft-com:xml-diffgram-v1');
                        $tables = $sxe->xpath('//Table[@d:id="Table1"]');

                        if(empty($tables)) {
                            // do nothing, for lanjut
                        } else {
                            // baru jalan, ambil nilai
                            $tanggal = date('Y-m-d', $tanggal_date);
                            break;
                        }
                    }
                }

                foreach ($tables as $key => $value) {
                    $kurs_beli = (string)$value->children()->beli_subkurslokal;
                    $kurs_jual = (string)$value->children()->jual_subkurslokal;
                    $hitung_kurs_tengah = ($kurs_beli + $kurs_jual)/2;
                    $kurs_tengah = (string)round($hitung_kurs_tengah, 2);

                    if (empty($id_kurs)) {
                        $data_kurs = [
                            'id_transaksi' => $id_transaksi,
                            'id_pjum' => null,
                            'id_pb' => $id_pb,
                            'id_valas' => $id_valas,
                            'kode_valas' => $kode_valas,
                            'tanggal' => $tanggal,
                            'kurs' => $kurs_tengah,
                        ];
                        $this->m_kurs->insert($data_kurs);
                    } else if (!empty($id_kurs)) {
                        $data_kurs_edit = [
                            'id_kurs' => $id_kurs['id_kurs'],
                            'id_pjum' => null,
                            'id_pb' => $id_pb,
                            'id_valas' => $id_valas,
                            'kode_valas' => $kode_valas,
                            'tanggal' => $tanggal,
                            'kurs' => $kurs_tengah,
                        ];
                        $this->m_kurs->save($data_kurs_edit);
                    }
                }

                $tanggal = $this->request->getVar('tanggal');

                $data_pb = [
                    'id_pb' => $id_pb,
                    'id_valas' => $id_valas,
                    'kode_valas' => $kode_valas,
                    'nomor' => $nomor,
                    'tanggal' => $tanggal,
                    'edited_at' => $timestamp,
                    'edited_by' => $nik,
                ];
                $this->m_pb->save($data_pb);

                session()->setFlashdata('success', 'Tanggal Pembuatan No PB berhasil ditambahkan');
                return redirect()->to('datapb/'.$jenis_biaya.'/'.$id_transaksi.'/'.$id_pb);
            } else {
                $tanggal = $this->request->getVar('tanggal');

                $data_pb = [
                    'id_pb' => $id_pb,
                    'id_valas' => $id_valas,
                    'kode_valas' => $kode_valas,
                    'nomor' => $nomor,
                    'tanggal' => $tanggal,
                    'edited_at' => $timestamp,
                    'edited_by' => $nik,
                ];
                $this->m_pb->save($data_pb);

                session()->setFlashdata('success', 'Tanggal Pembuatan No PB berhasil ditambahkan');
                return redirect()->to('datapb/'.$jenis_biaya.'/'.$id_transaksi.'/'.$id_pb);
            }
        }

        // if (empty($index_kurs)) {
        //     foreach ($kurs as $key => $value) {
        //         if (empty($id_kurs['id_kurs']) && $id_valas == $value['id_valas'] && $tanggal == $value['tanggal']) {
        //             $data_kurs = [
        //                 'id_transaksi' => $id_transaksi,
        //                 'id_pjum' => null,
        //                 'id_pb' => $id_pb,
        //                 'id_valas' => $id_valas,
        //                 'kode_valas' => $kode_valas,
        //                 'tanggal' => $tanggal,
        //                 'kurs' => $value['kurs'],
        //             ];
        //             $this->m_kurs->insert($data_kurs);
        //         } 
                
        //         // else if (!empty($id_kurs['id_kurs']) && $id_valas == $value['id_valas'] && $tanggal == $value['tanggal']){
        //         //     $data_kurs_edit = [
        //         //         'id_kurs' => $id_kurs['id_kurs'],
        //         //         'id_transaksi' => $id_transaksi,
        //         //         'id_pjum' => null,
        //         //         'id_pb' => $id_pb,
        //         //         'id_valas' => $id_valas,
        //         //         'kode_valas' => $kode_valas,
        //         //         'tanggal' => $tanggal,
        //         //         'kurs' => $value['kurs'],
        //         //     ];
        //         //     $this->m_kurs->save($data_kurs_edit);
        //         // }
        //     }
        // } else {
        //     if (empty($id_kurs['id_kurs']) && $id_valas == $index_kurs['id_valas'] && $tanggal == $index_kurs['tanggal']) {
        //         $data_kurs = [
        //             'id_transaksi' => $id_transaksi,
        //             'id_pjum' => null,
        //             'id_pb' => $id_pb,
        //             'id_valas' => $id_valas,
        //             'kode_valas' => $kode_valas,
        //             'tanggal' => $tanggal,
        //             'kurs' => $index_kurs['kurs'],
        //         ];
        //         $this->m_kurs->insert($data_kurs);
        //     }
            
        //     // else if (!empty($id_kurs['id_kurs']) && $id_valas == $index_kurs['id_valas'] && $tanggal == $index_kurs['tanggal']){
        //     //     $data_kurs_edit = [
        //     //         'id_kurs' => $id_kurs['id_kurs'],
        //     //         'id_transaksi' => $id_transaksi,
        //     //         'id_pjum' => null,
        //     //         'id_pb' => $id_pb,
        //     //         'id_valas' => $id_valas,
        //     //         'kode_valas' => $kode_valas,
        //     //         'tanggal' => $tanggal,
        //     //         'kurs' => $index_kurs['kurs'],
        //     //     ];
        //     //     $this->m_kurs->save($data_kurs_edit);
        //     // }
        // }
    }

    public function editbiayap($id_biaya, $id_kategori, $id_transaksi, $jenis_biaya, $id_pb)
    {
        if($this->request->getMethod()=='post') {
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
            $kirim = $this->m_id->where('id_transaksi', $id_transaksi)->select('kirim_pb')->first();

            if ($kirim['kirim_pb'] == 0) {
                $data = [
                    'id_biaya' => $id_biaya,
                    'biaya' => $string,
                ];
                $this->m_biaya->save($data);

                session()->setFlashdata('success', 'Biaya PB berhasil diubah');
                return redirect()->to('datapb/'.$jenis_biaya.'/'.$id_transaksi.'/'.$id_pb);
            } else {
                $kategori = [
                    'id_kategori' => $id_kategori,
                    'edited_at' => $timestamp,
                    'edited_by' => $nik,
                ];
                $this->m_kategori->save($kategori);

                $data = [
                    'id_biaya' => $id_biaya,
                    'biaya' => $string,
                    'edited_at' => $timestamp,
                    'edited_by' => $nik,
                ];
                $this->m_biaya->save($data);

                session()->setFlashdata('success', 'Biaya PB berhasil diubah');
                return redirect()->to('datapb/'.$jenis_biaya.'/'.$id_transaksi.'/'.$id_pb);
            }
        }
    }

    public function kursp($id_transaksi, $jenis_biaya, $id_pb)
    {
        $role= session()->get('akun_role');
        if($role != 'treasury'){
            return redirect()-> to("transaksi");
        }
        $kurs = $this->request->getVar('kurs');
        $string = preg_replace('~[.,](?=\d{2}\b)|\p{Sc}~u', '#', $kurs);
        $string = strtr(rtrim($string, '#'), ['#' => '.', '.' => '', '.' => '']);
        $data = [
            'id_transaksi' => $id_transaksi,
            'id_pjum' => null,
            'id_pb' => $id_pb,
            'tanggal' => $this->request->getVar('tanggal'),
            'id_valas' => $this->request->getVar('id_valas'),
            'kode_valas' => $this->request->getVar('kode_valas'),
            'kurs' => $string,
        ];
        $this->m_kurs->insert($data);
        session()->setFlashdata('success', 'Kurs berhasil ditambahkan');
        return redirect()->to('datapb/'.$jenis_biaya.'/'.$id_transaksi.'/'.$id_pb);
    }

    public function editkursp($id_transaksi, $jenis_biaya, $id_pb, $id_kurs)
    {
        $role= session()->get('akun_role');
        if($role != 'treasury'){
            return redirect()-> to("transaksi");
        }
        $kurs = $this->request->getVar('kurs');
        $string = preg_replace('~[.,](?=\d{2}\b)|\p{Sc}~u', '#', $kurs);
        $string = strtr(rtrim($string, '#'), ['#' => '.', '.' => '', '.' => '']);
        $data = [
            'id_kurs' => $id_kurs,
            'tanggal' => $this->request->getVar('tanggal'),
            'kurs' => $string,
        ];
        $this->m_kurs->save($data);
        session()->setFlashdata('success', 'Kurs berhasil diubah');
        return redirect()->to('datapb/'.$jenis_biaya.'/'.$id_transaksi.'/'.$id_pb);
    }
}