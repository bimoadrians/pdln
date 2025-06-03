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
use App\Models\PjumModel;
use App\Models\PbModel;
use App\Models\KursModel;
use App\Models\LogEmailModel;
use App\Models\LogEmailAllModel;

class PJUM extends BaseController
{
    public function __construct()
    {
        $this->validation = \Config\Services::validation();

        $this->m_am21 = new Am21Model();
        $this->m_am21b = new Am21bModel();
        $this->m_id = new TransaksiModel();
        $this->m_negara_tujuan = new NegaraTujuanModel();
        $this->m_valas = new ValasModel();
        $this->m_biaya = new BiayaModel();
        $this->m_kategori = new KategoriModel();
        $this->m_pum = new PumModel();
        $this->m_personil = new PersonilModel();
        $this->m_pjum = new PjumModel();
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

    //PJUM
    public function listpjum($jenis_biaya, $id_transaksi)
    {
        $nik = session()->get('akun_nik');
        $niknm = session()->get('niknm');
        $role = session()->get('akun_role');
        $strorg = session()->get('strorg');

        $login = $this->m_id->where('id_transaksi', $id_transaksi)->select('login_by')->first();

        if ($role == 'admin' && $login['login_by'] != $niknm || $role == 'user' && $login['login_by'] != $niknm) {
            session()->setFlashdata('warning', ['Id transaksi sedang diedit, harap menunggu beberapa saat lagi']);
            return redirect()->to("transaksi");
        } else {

        }

        if($role == 'admin') {
            $dataPost = $this->m_id->getPostId($id_transaksi, substr($strorg, 0, 4));
        } elseif($role == 'user') {
            $dataPost = $this->m_id->getId($id_transaksi, $nik);
        } elseif($role == 'treasury') {
            $dataPost = $this->m_id->getTreasuryDashboard($id_transaksi);
        } elseif($role == 'gs') {
            $dataPost = $this->m_id->getGSDashboard($id_transaksi);
        }
        if(empty($dataPost)) {
            return redirect()-> to("transaksi");
        }
        $data = $dataPost;

        $kat = $this->m_kategori->where('id_transaksi', $id_transaksi)->where('jenis_biaya', $jenis_biaya)->select('id_kategori')->first();
        if(empty($kat)) {
            session()->setFlashdata('warning', ['Tidak ada data, silahkan upload data biaya terlebih dahulu']);
            return redirect()-> to('dashboard/'.$id_transaksi);
        }

        if($role == 'treasury') {
            $id = $this->m_id->getTreasury($id_transaksi);
        } elseif($role == 'gs') {
            $id = $this->m_id->getGS($id_transaksi);
        } else {
            $id = $this->m_id->getPostId($id_transaksi, substr($strorg, 0, 4));
        }

        $ses = [
            'id_transaksi' => $id_transaksi,
            'jenis_biaya' => $jenis_biaya,
        ];

        session()->set($ses);

        if($jenis_biaya == 'pb') {
            return redirect()-> to('listpjum/pjum/'.$id_transaksi);
        }

        $submit_pjum = $this->m_id->where('id_transaksi', $id_transaksi)->select('submit_pjum, kirim_pjum')->first();
        $submit_pb = $this->m_id->where('id_transaksi', $id_transaksi)->select('submit_pb')->first();

        if ($role == 'gs' && $submit_pjum['submit_pjum'] < 2) {
            return redirect()-> to("transaksi");
        }

        $ceknomor = $this->m_pjum->ceknomor($id_transaksi, $this->request->getVar('nomor'));

        if ($ceknomor == null) {

        } elseif($id_transaksi == $ceknomor['id_transaksi'] && $this->request->getVar('nomor') == $ceknomor['nomor']) {
            session()->setFlashdata('warning', ['Nomor PJUM tidak boleh sama']);
            return redirect()->to('listpjum/'.$jenis_biaya.'/'.$id_transaksi);
        }

        $timestamp = date('Y-m-d H:i:s');

        $mail = new PHPMailer(true);
        $nikuser = $this->m_id->where('id_transaksi', $id_transaksi)->select('nik, strorgnm')->first();
        $niknmuser = $this->m_am21->where('nik', $nikuser['nik'])->select('niknm')->first();
        $emailuser = $this->m_am21b->where('nik', $nikuser['nik'])->select('noemailint')->first();
        if($this->request->getMethod() == 'post') {
            if ($this->request->getVar('nomor') == null || $this->request->getVar('nomor') == '') {
                if ($role == 'admin' || $role == 'user') {
                    $id_pjum = $this->m_kategori->where('id_transaksi', $id_transaksi)->select('id_pjum')->findAll();

                    foreach ($id_pjum as $key => $value) {
                        if ($value['id_pjum'] != null) {
                            $nopjum = $this->m_pjum->where('id_pjum', $value['id_pjum'])->select('id_pjum, tanggal')->findAll();
                            foreach ($nopjum as $np => $nopj) {
                                if($nopj['tanggal'] == null) {
                                    session()->setFlashdata('warning', ['Tambahkan tanggal pembuatan no PJUM terlebih dahulu untuk melakukan submit data']);
                                    return redirect()-> to('listpjum/pjum/'.$id_transaksi);
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
                        $link = 'https://konimex.com:446/pdln/listpjum/pjum/'.$id_transaksi;
                        $mail->Subject = 'Data PJUM Siap Periksa';
                        $mail->Body    = "Dengan hormat,\n\n".
                        "User bagian (".$niknmuser['niknm']."/".$nikuser['nik']."/".$nikuser['strorgnm'].") telah selesai mengisi data PJUM, silahkan periksa data PJUM dengan cara klik link: $link\n\n".
                        "Terima kasih.";

                        $mail->send();
                        echo 'Message has been sent';
                    } catch (Exception $e) {
                        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                    }

                    $data = [
                        'id_transaksi' => $id_transaksi,
                        'submit_pjum' => 1,
                        'kirim_pjum' => 1,
                    ];

                    $cek_log_email = $this->m_log_email->where('id_transaksi', $id_transaksi)->select('id_log_email')->first();

                    if (empty($cek_log_email)) {
                        $log_email = [
                            'id_transaksi' => $id_transaksi,
                            'title' => 'Data PJUM '.$nikuser['nik'].' Siap Periksa',
                            'nik' => $nikuser['nik'],
                            'submit_pjum' => 1,
                            'kirim_pjum' => 1,
                            'waktu_kirim' => $timestamp,
                        ];
                        $this->m_log_email->insert($log_email);
                    } else {
                        $log_email = [
                            'id_log_email' => $cek_log_email['id_log_email'],
                            'submit_pjum' => 1,
                            'kirim_pjum' => 1,
                            'waktu_kirim' => $timestamp,
                        ];
                        $this->m_log_email->save($log_email);
                    }

                    $log_email_all = [
                        'id_transaksi' => $id_transaksi,
                        'title' => 'Data PJUM '.$nikuser['nik'].' Siap Periksa',
                        'nik' => $nikuser['nik'],
                        'submit_pjum' => 1,
                        'waktu_kirim' => $timestamp,
                    ];
                    $this->m_log_email_all->insert($log_email_all);
                    $this->m_id->save($data);

                    session()->setFlashdata('success', 'Data PJUM berhasil disubmit');
                    return redirect()->to('listpjum/'.$jenis_biaya.'/'.$id_transaksi);
                } elseif ($role == 'treasury') {
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
                        $link = 'https://konimex.com:446/pdln/listpjum/pjum/'.$id_transaksi;
                        $mail->Subject = 'Data PJUM Telah Diperiksa Bagian Treasury';
                        $mail->Body    = "Dengan hormat,\n\n".
                        "Bagian Treasury telah selesai melakukan pengecekan data PJUM, untuk melihat revisi data PJUM silahkan klik link: $link\n\n".
                        "Terima kasih.";

                        $mail->send();
                        echo 'Message has been sent';
                    } catch (Exception $e) {
                        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                    }

                    $data = [
                        'id_transaksi' => $id_transaksi,
                        'submit_pjum' => 0,
                    ];

                    $cek_log_email = $this->m_log_email->where('id_transaksi', $id_transaksi)->select('id_log_email')->first();

                    if (empty($cek_log_email)) {
                        $log_email = [
                            'id_transaksi' => $id_transaksi,
                            'title' => 'Data PJUM Telah Diperiksa Bagian Treasury',
                            'nik' => $nikuser['nik'],
                            'submit_pjum' => 0,
                            'kirim_pjum' => 1,
                            'waktu_kirim' => $timestamp,
                        ];
                        $this->m_log_email->insert($log_email);
                    } else {
                        $log_email = [
                            'id_log_email' => $cek_log_email['id_log_email'],
                            'submit_pjum' => 0,
                            'kirim_pjum' => 1,
                            'waktu_kirim' => $timestamp,
                        ];
                        $this->m_log_email->save($log_email);
                    }

                    $log_email_all = [
                        'id_transaksi' => $id_transaksi,
                        'title' => 'Data PJUM Telah Diperiksa Bagian Treasury',
                        'nik' => $nikuser['nik'],
                        'submit_pjum' => 0,
                        'waktu_kirim' => $timestamp,
                    ];
                    $this->m_log_email_all->insert($log_email_all);
                    $this->m_id->save($data);

                    session()->setFlashdata('success', 'Data PJUM sedang direvisi');
                    return redirect()->to('dashboard/'.$id_transaksi);
                } elseif ($role == 'gs') {
                    // try {
                    //     //Server settings
                    //     $mail->SMTPDebug = SMTP::DEBUG_SERVER;
                    //     $mail->isSMTP();
                    //     $mail->Host       = 'mail.konimex.com';
                    //     $mail->SMTPAuth   = true;
                    //     $mail->Username   = PDLN_EMAIL;
                    //     $mail->Password   = PDLN_PASS;
                    //     $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    //     $mail->Port       = 587;

                    //     //Recipients
                    //     $mail->setFrom('noreply@konimex.com');
                    //     $mail->addAddress('renny.lowis@konimex.com');//Add a recipient (Treasury)
                    //     $mail->addBCC('09002@intra.net');//Add a recipient (BAS)

                    //     //Content
                    //     $link = 'https://konimex.com:446/pdln/listpjum/pjum/'.$id_transaksi;
                    //     $mail->Subject = 'Data PJUM Telah Diperiksa Bagian GS';
                    //     $mail->Body    = nl2br("Bagian GS telah selesai melakukan pengecekan data PJUM User bagian (").$niknmuser['niknm']."/".$nikuser['nik']."/".$nikuser['strorgnm'].("), untuk melihat revisi data PJUM silahkan klik link: $link");

                    //     $mail->send();
                    //     echo 'Message has been sent';
                    // } catch (Exception $e) {
                    //     echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                    // }

                    // $data = [
                    //     'id_transaksi' => $id_transaksi,
                    //     'submit_pjum' => 1,
                    // ];

                    // $cek_log_email = $this->m_log_email->where('id_transaksi', $id_transaksi)->select('id_log_email')->first();

                    // if (empty($cek_log_email)) {
                    //     $log_email = [
                    //         'id_transaksi' => $id_transaksi,
                    //         'title' => 'Data PJUM Telah Diperiksa Bagian GS',
                    //         'nik' => $nikuser['nik'],
                    //         'submit_pjum' => 1,
                    //         'waktu_kirim' => $timestamp,
                    //     ];
                    //     $this->m_log_email->insert($log_email);
                    // } else {
                    //     $log_email = [
                    //         'id_log_email' => $cek_log_email['id_log_email'],
                    //         'submit_pjum' => 1,
                    //         'waktu_kirim' => $timestamp,
                    //     ];
                    //     $this->m_log_email->save($log_email);
                    // }

                    // $log_email_all = [
                    //     'id_transaksi' => $id_transaksi,
                    //     'title' => 'Data PJUM Telah Diperiksa Bagian GS',
                    //     'nik' => $nikuser['nik'],
                    //     'submit_pjum' => 1,
                    //     'waktu_kirim' => $timestamp,
                    // ];
                    // $this->m_log_email_all->insert($log_email_all);
                    // $this->m_id->save($data);

                    // session()->setFlashdata('success', 'Data PJUM sedang direvisi');
                    // return redirect()->to('dashboard/'.$id_transaksi);
                }
            } else {
                $data = [
                    'id_transaksi' => $id_transaksi,
                    'nomor' => $this->request->getVar('nomor'),
                ];
                $this->m_pjum->insert($data);

                session()->setFlashdata('success', 'No PJUM berhasil ditambahkan');
                return redirect()->to('listpjum/'.$jenis_biaya.'/'.$id_transaksi);
            }
        }

        if($this->request->getVar('aksi') == 'hapus' && $this->request->getVar('id_pjum')) {
            $dataPost = $this->m_pjum->getPostId($id_transaksi, $this->request->getVar('id_pjum'));
            if($dataPost['id_pjum']) {//memastikan bahwa ada data
                if ($submit_pjum['submit_pjum'] == 0) {
                    $aksi = $this->m_pjum->deletePostId($this->request->getVar('id_pjum'));
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
                if($aksi == true) {
                    $this->m_pum->query('ALTER TABLE pum AUTO_INCREMENT 1');
                    $this->m_pjum->query('ALTER TABLE pjum AUTO_INCREMENT 1');
                    $this->m_kurs->query('ALTER TABLE kurs AUTO_INCREMENT 1');
                    $this->m_kategori->query('ALTER TABLE kategori AUTO_INCREMENT 1');
                    $this->m_biaya->query('ALTER TABLE biaya AUTO_INCREMENT 1');
                    session()->setFlashdata('success', "Data PJUM berhasil dihapus");
                } else {
                    session()->setFlashdata('warning', ['Data PJUM gagal dihapus']);
                }
            }

            $cek_kat = $this->m_kategori->where('id_transaksi', $id_transaksi)->select('id_kategori')->findAll();

            if (empty($cek_kat)) {
                $data = [
                    'id_transaksi' => $id_transaksi,
                    'submit_pjum' => 0,
                    'kirim_pjum' => 0,
                ];
                $this->m_id->save($data);
            } else {
                
            }

            return redirect()->to('listpjum/'.$jenis_biaya.'/'.$id_transaksi);
        }

        $kota = $this->m_id->where('id_transaksi', $id_transaksi)->select('kota')->first();

        $cek = $this->m_kategori->cek($id_transaksi);

        if($role == 'treasury' || $role == 'gs') {
            if(empty($cek)) {
                return redirect()-> to("transaksi");
            }
        }

        $kategori = $this->m_kategori->where('id_transaksi', $id_transaksi)->where('jenis_biaya', $jenis_biaya)->select('id_pjum, treasury, edited_by')->orderBy('baris', 'desc')->findAll();

        $nopb = $this->m_pb->where('id_transaksi', $id_transaksi)->select('created_by')->findAll();

        $cekdatapb = $this->m_kategori->cekpb($id_transaksi, 'PB');

        session()->set('url_transaksi', current_url());

        $data = [
            'header' => "List PJUM",
            'dataPost' => $dataPost,
            'id' => $id,
            'role' => $role,
            'kota' => $this->m_id->kota($id_transaksi),
            'solo' => $kota['kota'],
            'submit_pjum' => $submit_pjum['submit_pjum'],
            'submit_pb' => $submit_pb['submit_pb'],
            'submit' => $submit_pjum['submit_pjum'],
            'kirim_pjum' => $submit_pjum['kirim_pjum'],
            'kategori' => $kategori,
            'neg' => $this->m_negara_tujuan->getDataAllId($id_transaksi),
            'nomor' => $this->m_pjum->nomor($id_transaksi),
            'valas' => $this->m_pum->valas($id_transaksi),
            'cek' => $cek,
            'cekpjum' => $this->m_kategori->alldataId($id_transaksi, 'PJUM'),
            'cekpb' => $this->m_kategori->alldataId($id_transaksi, 'PB'),
            'nopb' => $nopb,
            'cekdatapb' => $cekdatapb,
        ];
        echo view('pjum/v_listpjum', $data);
        // print_r(session()->get());
    }

    public function datapjum($jenis_biaya, $id_transaksi, $id_pjum)
    {
        $nik = session()->get('akun_nik');
        $niknm = session()->get('niknm');
        $role = session()->get('akun_role');
        $strorg = session()->get('strorg');

        $login = $this->m_id->where('id_transaksi', $id_transaksi)->select('login_by')->first();

        if ($role == 'admin' && $login['login_by'] != $niknm || $role == 'user' && $login['login_by'] != $niknm) {
            session()->setFlashdata('warning', ['Id transaksi sedang diedit, harap menunggu beberapa saat lagi']);
            return redirect()->to("transaksi");
        } else {

        }

        if($role == 'admin') {
            $user = $this->m_id->getPostId($id_transaksi, substr($strorg, 0, 4));
        } elseif($role == 'user') {
            $user = $this->m_id->getId($id_transaksi, $nik);
        } elseif($role == 'treasury') {
            $user = $this->m_id->getTreasuryDashboard($id_transaksi);
        } elseif($role == 'gs') {
            $user = $this->m_id->getGSDashboard($id_transaksi);
        }

        $dataPost = $this->m_pjum->getData($id_transaksi, $id_pjum);

        if(empty($dataPost)) {
            return redirect()-> to("transaksi");
        }
        if(empty($user)) {
            return redirect()-> to("transaksi");
        }

        $data = $dataPost;

        if($role == 'treasury') {
            $id = $this->m_id->getTreasury($id_transaksi);
        } elseif($role == 'gs') {
            $id = $this->m_id->getGS($id_transaksi);
        } else {
            $id = $this->m_id->getPostId($id_transaksi, substr($strorg, 0, 4));
        }

        $ses = [
            'id_transaksi' => $id_transaksi,
            'jenis_biaya' => $jenis_biaya,
            'id_pjum' => $id_pjum,
        ];

        session()->set($ses);

        if($jenis_biaya == 'pb') {
            return redirect()-> to('datapjum/'.$jenis_biaya.'/'.$id_transaksi.'/'.$id_pjum);
        }

        $submit_pjum = $this->m_id->where('id_transaksi', $id_transaksi)->select('submit_pjum, kirim_pjum')->first();
        $submit_pb = $this->m_id->where('id_transaksi', $id_transaksi)->select('submit_pb, kirim_pb')->first();

        if ($role == 'gs' && $submit_pjum['submit_pjum'] < 2) {
            return redirect()-> to("transaksi");
        }

        $valas = $this->m_biaya->valaspjum($id_transaksi, $jenis_biaya, $id_pjum);
        $kode_valas = $this->m_biaya->kode_valaspjum($id_transaksi, $jenis_biaya, $id_pjum);

        $tukarUangMasuk = $this->m_biaya->tukarUangMasuk($id_transaksi, $jenis_biaya, $id_pjum);
        if (empty($tukarUangMasuk)) {
            $tukarUangMasuk[] = (array)0;
        }
        $sumBiaya = $this->m_biaya->total($id_transaksi, $jenis_biaya, $id_pjum);
        $uangMasuk = $this->m_biaya->uangMasuk($id_transaksi, $jenis_biaya, $id_pjum);
        if (empty($uangMasuk)) {
            $uangMasuk = $this->m_biaya->id_valas($id_transaksi, $jenis_biaya, $id_pjum);
        }
        $pum = $this->m_pum->sisa($id_transaksi, $id_pjum);
        $tukarUangKeluar = $this->m_biaya->tukarUangKeluar($id_transaksi, $jenis_biaya, $id_pjum);
        if (empty($tukarUangKeluar)) {
            $tukarUangKeluar[] = (array)0;
        }
        $kembalian = $this->m_biaya->kembalian($id_transaksi, $jenis_biaya, $id_pjum);

        $kota = $this->m_id->where('id_transaksi', $id_transaksi)->select('kota')->first();
        $valas_kurs = $this->m_biaya->where('id_pjum', $id_pjum)->select('kode_valas')->first();

        $cek = $this->m_kategori->cek($id_transaksi);

        if($role == 'treasury' || $role == 'gs') {
            if(empty($cek)) {
                return redirect()-> to("transaksi");
            }
        }

        $nopb = $this->m_pb->where('id_transaksi', $id_transaksi)->select('created_by')->findAll();

        session()->set('url_transaksi', current_url());

        $data = [
            'header' => "Data PJUM",
            'dataPost' => $dataPost,
            'neg' => $this->m_negara_tujuan->getDataAllId($id_transaksi),
            'id' => $id,
            'valas' => $this->m_pum->valas($id_transaksi),
            'kategori' => $this->m_kategori->alldataNomorpjum($id_transaksi, $jenis_biaya, $id_pjum),
            'nomor' => $this->m_pjum->getData($id_transaksi, $id_pjum),
            'kota' => $this->m_id->kota($id_transaksi),
            'solo' => $kota['kota'],
            'valas_kurs' => $valas_kurs['kode_valas'],
            'biaya' => $this->m_biaya->alldatapjum($id_transaksi, $jenis_biaya, $id_pjum),
            'pum' => $pum,
            'kode_valas' => $kode_valas,
            'index' => count((array)$valas),
            'total' => $sumBiaya,
            'tukarMasuk' => $tukarUangMasuk,
            'tukarKeluar' => $tukarUangKeluar,
            'kembalian' => $kembalian,
            'uangMasuk' => $uangMasuk,
            'submit' => $submit_pjum['submit_pjum'],
            'kirim' => $submit_pjum['kirim_pjum'],
            'role' => $role,
            'submit_pjum' => $submit_pjum['submit_pjum'],
            'submit_pb' => $submit_pb['submit_pb'],
            'id_pjum' => $id_pjum,
            'id_pjum_tanggal' => $id_pjum,
            'cek' => $cek,
            'cekpjum' => $this->m_kategori->alldataId($id_transaksi, 'PJUM'),
            'cekpb' => $this->m_kategori->alldataId($id_transaksi, 'PB'),
            'kurs' => $this->m_kurs->getkurspjum($id_pjum),
            'nopb' => $nopb,
        ];
        echo view('pjum/v_datapjum', $data);
        // print_r(session()->get());
    }

    public function editbiayapjum($id_biaya, $id_kategori, $id_transaksi, $jenis_biaya, $id_pjum)
    {
        $nik = session()->get('akun_nik');
        $niknm = session()->get('niknm');
        $role = session()->get('akun_role');
        $strorg = session()->get('strorg');

        $login = $this->m_id->where('id_transaksi', $id_transaksi)->select('login_by')->first();

        if ($role == 'admin' && $login['login_by'] != $niknm || $role == 'user' && $login['login_by'] != $niknm) {
            session()->setFlashdata('warning', ['Id transaksi sedang diedit, harap menunggu beberapa saat lagi']);
            return redirect()->to("transaksi");
        } else {

        }

        if($role == 'admin') {
            $user = $this->m_id->getPostId($id_transaksi, substr($strorg, 0, 4));
        } elseif($role == 'user') {
            $user = $this->m_id->getId($id_transaksi, $nik);
        } elseif($role == 'treasury') {
            $user = $this->m_id->getTreasuryDashboard($id_transaksi);
        } elseif($role == 'gs') {
            $user = $this->m_id->getGSDashboard($id_transaksi);
        }
        $dataPost = $this->m_pjum->getData($id_transaksi, $id_pjum);
        if(empty($dataPost)) {
            return redirect()-> to("transaksi");
        }
        if(empty($user)) {
            return redirect()-> to("transaksi");
        }

        $data = $dataPost;

        $kat = $this->m_kategori->alldataId($id_transaksi, $jenis_biaya);
        if(empty($kat)) {
            session()->setFlashdata('warning', ['Tidak ada data, silahkan upload data biaya terlebih dahulu']);
            return redirect()-> to('dashboard/'.$id_transaksi);
        }

        $bia = $this->m_biaya->alldatapjum($id_transaksi, $jenis_biaya, $id_pjum);
        if(empty($bia)) {
            session()->setFlashdata('warning', ['Tidak ada data, silahkan upload data biaya terlebih dahulu']);
            return redirect()-> to('listpjum/pjum/'.$id_transaksi);
        }

        if($role == 'treasury') {
            $id = $this->m_id->getTreasury($id_transaksi);
        } elseif($role == 'gs') {
            $id = $this->m_id->getGS($id_transaksi);
        } else {
            $id = $this->m_id->getPostId($id_transaksi, substr($strorg, 0, 4));
        }

        $ses = [
            'id_transaksi' => $id_transaksi,
            'jenis_biaya' => $jenis_biaya,
            'id_pjum' => $id_pjum,
        ];

        session()->set($ses);

        if($jenis_biaya == 'pb') {
            return redirect()-> to('datapjum/'.$jenis_biaya.'/'.$id_transaksi.'/'.$id_pjum);
        }

        $submit_pjum = $this->m_id->where('id_transaksi', $id_transaksi)->select('submit_pjum, kirim_pjum')->first();
        $submit_pb = $this->m_id->where('id_transaksi', $id_transaksi)->select('submit_pb, kirim_pb')->first();

        $cek = $this->m_kategori->cek($id_transaksi);

        if ($role == 'treasury' || $role == 'gs') {
            return redirect()-> to("transaksi");
        }

        if($role == 'admin' && $submit_pjum['submit_pjum'] != 0 || $role == 'user' && $submit_pjum['submit_pjum'] != 0) {
            return redirect()-> to("transaksi");
        }

        if($this->request->getMethod() == 'post') {
            $nik = session()->get('akun_nik');
            $timestamp = date('Y-m-d H:i:s');
            $biaya = $this->request->getVar('biaya');
            $comma = ',';
            $number = preg_replace('/[^0-9\\-]+/', '', $biaya);
            if(strpos($biaya, $comma) !== false) {
                $string = $number / 100;
            } else {
                $string = $number;
            }
            $kirim = $this->m_id->where('id_transaksi', $id_transaksi)->select('kirim_pjum')->first();

            if ($kirim['kirim_pjum'] == 0) {
                $data = [
                    'id_biaya' => $id_biaya,
                    'biaya' => $string,
                ];
                $this->m_biaya->save($data);

                session()->setFlashdata('success', 'Biaya PJUM berhasil diubah');
                return redirect()->to('datapjum/'.$jenis_biaya.'/'.$id_transaksi.'/'.$id_pjum);
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

                session()->setFlashdata('success', 'Biaya PJUM berhasil diubah');
                return redirect()->to('datapjum/'.$jenis_biaya.'/'.$id_transaksi.'/'.$id_pjum);
            }
        }

        $valas = $this->m_biaya->valaspjum($id_transaksi, $jenis_biaya, $id_pjum);
        $kode_valas = $this->m_biaya->kode_valaspjum($id_transaksi, $jenis_biaya, $id_pjum);

        $kota = $this->m_id->where('id_transaksi', $id_transaksi)->select('kota')->first();
        $nopb = $this->m_pb->where('id_transaksi', $id_transaksi)->select('created_by')->findAll();
        session()->set('url_transaksi', current_url());

        $data = [
            'header' => "Edit Biaya PJUM",
            'id' => $id,
            'dataPost' => $dataPost,
            'biaya' => $this->m_biaya->allbiayapjum($id_biaya),
            'role' => $role,
            'solo' => $kota['kota'],
            'submit' => $submit_pjum['submit_pjum'],
            'submit_pjum' => $submit_pjum['submit_pjum'],
            'submit_pb' => $submit_pb['submit_pb'],
            'cek' => $cek,
            'cekpjum' => $this->m_kategori->alldataId($id_transaksi, 'PJUM'),
            'cekpb' => $this->m_kategori->alldataId($id_transaksi, 'PB'),
            'id_pjum' => $id_pjum,
            'index' => count((array)$valas),
            'kode_valas' => $kode_valas,
            'nomor' => $this->m_pjum->getData($id_transaksi, $id_pjum),
            'id_pjum_tanggal' => $id_pjum,
            'pum' => $this->m_pum->alldataId($id_transaksi, $id_pjum),
            'neg' => $this->m_negara_tujuan->getDataAllId($id_transaksi),
            'nopb' => $nopb,
        ];
        echo view('proses/pjum/v_biayapjum', $data);
        // print_r(session()->get());
    }

    public function editbiayapum($id_pum, $id_transaksi, $jenis_biaya, $id_pjum)
    {
        $nik = session()->get('akun_nik');
        $niknm = session()->get('niknm');
        $role = session()->get('akun_role');
        $strorg = session()->get('strorg');

        $login = $this->m_id->where('id_transaksi', $id_transaksi)->select('login_by')->first();

        if ($role == 'admin' && $login['login_by'] != $niknm || $role == 'user' && $login['login_by'] != $niknm) {
            session()->setFlashdata('warning', ['Id transaksi sedang diedit, harap menunggu beberapa saat lagi']);
            return redirect()->to("transaksi");
        } else {

        }

        if($role == 'admin') {
            $user = $this->m_id->getPostId($id_transaksi, substr($strorg, 0, 4));
        } elseif($role == 'user') {
            $user = $this->m_id->getId($id_transaksi, $nik);
        } elseif($role == 'treasury') {
            $user = $this->m_id->getTreasuryDashboard($id_transaksi);
        } elseif($role == 'gs') {
            $user = $this->m_id->getGSDashboard($id_transaksi);
        }
        $dataPost = $this->m_pjum->getData($id_transaksi, $id_pjum);
        if(empty($dataPost)) {
            return redirect()-> to("transaksi");
        }
        if(empty($user)) {
            return redirect()-> to("transaksi");
        }

        $data = $dataPost;

        $kat = $this->m_kategori->alldataId($id_transaksi, $jenis_biaya);
        if(empty($kat)) {
            session()->setFlashdata('warning', ['Tidak ada data, silahkan upload data biaya terlebih dahulu']);
            return redirect()-> to('dashboard/'.$id_transaksi);
        }

        $bia = $this->m_biaya->alldatapjum($id_transaksi, $jenis_biaya, $id_pjum);
        if(empty($bia)) {
            session()->setFlashdata('warning', ['Tidak ada data, silahkan upload data biaya terlebih dahulu']);
            return redirect()-> to('listpjum/pjum/'.$id_transaksi);
        }

        if($role == 'treasury') {
            $id = $this->m_id->getTreasury($id_transaksi);
        } elseif($role == 'gs') {
            $id = $this->m_id->getGS($id_transaksi);
        } else {
            $id = $this->m_id->getPostId($id_transaksi, substr($strorg, 0, 4));
        }

        $ses = [
            'id_transaksi' => $id_transaksi,
            'jenis_biaya' => $jenis_biaya,
            'id_pjum' => $id_pjum,
        ];

        session()->set($ses);

        if($jenis_biaya == 'pb') {
            return redirect()-> to('datapjum/'.$jenis_biaya.'/'.$id_transaksi.'/'.$id_pjum);
        }

        $submit_pjum = $this->m_id->where('id_transaksi', $id_transaksi)->select('submit_pjum, kirim_pjum')->first();
        $submit_pb = $this->m_id->where('id_transaksi', $id_transaksi)->select('submit_pb, kirim_pb')->first();

        $cek = $this->m_kategori->cek($id_transaksi);

        if ($role == 'treasury' || $role == 'gs') {
            return redirect()-> to("transaksi");
        }

        if($role == 'admin' && $submit_pjum['submit_pjum'] != 0 || $role == 'user' && $submit_pjum['submit_pjum'] != 0) {
            return redirect()-> to("transaksi");
        }

        if($this->request->getMethod() == 'post') {
            $nik = session()->get('akun_nik');
            $timestamp = date('Y-m-d H:i:s');
            $biaya = $this->request->getVar('biaya');
            $comma = ',';
            $number = preg_replace('/[^0-9\\-]+/', '', $biaya);
            if(strpos($biaya, $comma) !== false) {
                $string = $number / 100;
            } else {
                $string = $number;
            }

            $kirim = $this->m_id->where('id_transaksi', $id_transaksi)->select('kirim_pjum')->first();

            if ($kirim['kirim_pjum'] == 0) {
                $data = [
                    'id_pum' => $id_pum,
                    'pum' => $string,
                ];
                $this->m_pum->save($data);

                session()->setFlashdata('success', 'PUM berhasil diubah');
                return redirect()->to('datapjum/'.$jenis_biaya.'/'.$id_transaksi.'/'.$id_pjum);
            } else {
                $data = [
                    'id_pum' => $id_pum,
                    'pum' => $string,
                    'edited_at' => $timestamp,
                    'edited_by' => $nik,
                ];
                $this->m_pum->save($data);

                session()->setFlashdata('success', 'PUM berhasil diubah');
                return redirect()->to('datapjum/'.$jenis_biaya.'/'.$id_transaksi.'/'.$id_pjum);
            }
        }

        $valas = $this->m_biaya->valaspjum($id_transaksi, $jenis_biaya, $id_pjum);
        $kode_valas = $this->m_biaya->kode_valaspjum($id_transaksi, $jenis_biaya, $id_pjum);

        $kota = $this->m_id->where('id_transaksi', $id_transaksi)->select('kota')->first();
        $nopb = $this->m_pb->where('id_transaksi', $id_transaksi)->select('created_by')->findAll();
        session()->set('url_transaksi', current_url());

        $data = [
            'header' => "Edit Biaya PUM",
            'id' => $id,
            'dataPost' => $dataPost,
            'biaya' => $this->m_biaya->alldatapjum($id_transaksi, $jenis_biaya, $id_pjum),
            'role' => $role,
            'solo' => $kota['kota'],
            'submit' => $submit_pjum['submit_pjum'],
            'submit_pjum' => $submit_pjum['submit_pjum'],
            'submit_pb' => $submit_pb['submit_pb'],
            'cek' => $cek,
            'cekpjum' => $this->m_kategori->alldataId($id_transaksi, 'PJUM'),
            'cekpb' => $this->m_kategori->alldataId($id_transaksi, 'PB'),
            'id_pjum' => $id_pjum,
            'index' => count((array)$valas),
            'kode_valas' => $kode_valas,
            'nomor' => $this->m_pjum->getData($id_transaksi, $id_pjum),
            'id_pjum_tanggal' => $id_pjum,
            'pum' => $this->m_pum->alldataId($id_transaksi, $id_pjum),
            'neg' => $this->m_negara_tujuan->getDataAllId($id_transaksi),
            'nopb' => $nopb,
        ];
        echo view('proses/pjum/v_pum', $data);
        // print_r(session()->get());
    }

    public function editbiayasisa($id_pum, $id_transaksi, $jenis_biaya, $id_pjum)
    {
        $nik = session()->get('akun_nik');
        $niknm = session()->get('niknm');
        $role = session()->get('akun_role');
        $strorg = session()->get('strorg');

        $login = $this->m_id->where('id_transaksi', $id_transaksi)->select('login_by')->first();

        if ($role == 'admin' && $login['login_by'] != $niknm || $role == 'user' && $login['login_by'] != $niknm) {
            session()->setFlashdata('warning', ['Id transaksi sedang diedit, harap menunggu beberapa saat lagi']);
            return redirect()->to("transaksi");
        } else {

        }

        if($role == 'admin') {
            $user = $this->m_id->getPostId($id_transaksi, substr($strorg, 0, 4));
        } elseif($role == 'user') {
            $user = $this->m_id->getId($id_transaksi, $nik);
        } elseif($role == 'treasury') {
            $user = $this->m_id->getTreasuryDashboard($id_transaksi);
        } elseif($role == 'gs') {
            $user = $this->m_id->getGSDashboard($id_transaksi);
        }
        $dataPost = $this->m_pjum->getData($id_transaksi, $id_pjum);
        if(empty($dataPost)) {
            return redirect()-> to("transaksi");
        }
        if(empty($user)) {
            return redirect()-> to("transaksi");
        }

        $data = $dataPost;

        $kat = $this->m_kategori->alldataId($id_transaksi, $jenis_biaya);
        if(empty($kat)) {
            session()->setFlashdata('warning', ['Tidak ada data, silahkan upload data biaya terlebih dahulu']);
            return redirect()-> to('dashboard/'.$id_transaksi);
        }

        $bia = $this->m_biaya->alldatapjum($id_transaksi, $jenis_biaya, $id_pjum);
        if(empty($bia)) {
            session()->setFlashdata('warning', ['Tidak ada data, silahkan upload data biaya terlebih dahulu']);
            return redirect()-> to('listpjum/pjum/'.$id_transaksi);
        }

        if($role == 'treasury') {
            $id = $this->m_id->getTreasury($id_transaksi);
        } elseif($role == 'gs') {
            $id = $this->m_id->getGS($id_transaksi);
        } else {
            $id = $this->m_id->getPostId($id_transaksi, substr($strorg, 0, 4));
        }

        $ses = [
            'id_transaksi' => $id_transaksi,
            'jenis_biaya' => $jenis_biaya,
            'id_pjum' => $id_pjum,
        ];

        session()->set($ses);

        if($jenis_biaya == 'pb') {
            return redirect()-> to('datapjum/'.$jenis_biaya.'/'.$id_transaksi.'/'.$id_pjum);
        }

        $submit_pjum = $this->m_id->where('id_transaksi', $id_transaksi)->select('submit_pjum, kirim_pjum')->first();
        $submit_pb = $this->m_id->where('id_transaksi', $id_transaksi)->select('submit_pb, kirim_pb')->first();

        $cek = $this->m_kategori->cek($id_transaksi);

        if ($role == 'treasury' || $role == 'gs') {
            return redirect()-> to("transaksi");
        }

        if($role == 'admin' && $submit_pjum['submit_pjum'] != 0 || $role == 'user' && $submit_pjum['submit_pjum'] != 0) {
            return redirect()-> to("transaksi");
        }

        if($this->request->getMethod() == 'post') {
            $nik = session()->get('akun_nik');
            $timestamp = date('Y-m-d H:i:s');
            $biaya = $this->request->getVar('biaya');
            $comma = ',';
            $number = preg_replace('/[^0-9\\-]+/', '', $biaya);
            if(strpos($biaya, $comma) !== false) {
                $string = $number / 100;
            } else {
                $string = $number;
            }
            $kirim = $this->m_id->where('id_transaksi', $id_transaksi)->select('kirim_pjum')->first();

            if ($kirim['kirim_pjum'] == 0) {
                $data = [
                    'id_pum' => $id_pum,
                    'uang_kembali' => $string,
                ];
                $this->m_pum->save($data);

                session()->setFlashdata('success', 'Sisa Uang Dikembalikan berhasil diubah');
                return redirect()->to('datapjum/'.$jenis_biaya.'/'.$id_transaksi.'/'.$id_pjum);
            } else {
                $data = [
                    'id_pum' => $id_pum,
                    'uang_kembali' => $string,
                    'edited_at' => $timestamp,
                    'edited_by' => $nik,
                ];
                $this->m_pum->save($data);

                session()->setFlashdata('success', 'Sisa Uang Dikembalikan berhasil diubah');
                return redirect()->to('datapjum/'.$jenis_biaya.'/'.$id_transaksi.'/'.$id_pjum);
            }
        }

        $valas = $this->m_biaya->valaspjum($id_transaksi, $jenis_biaya, $id_pjum);
        $kode_valas = $this->m_biaya->kode_valaspjum($id_transaksi, $jenis_biaya, $id_pjum);

        $kota = $this->m_id->where('id_transaksi', $id_transaksi)->select('kota')->first();
        $nopb = $this->m_pb->where('id_transaksi', $id_transaksi)->select('created_by')->findAll();
        session()->set('url_transaksi', current_url());

        $data = [
            'header' => "Edit Biaya Sisa Uang Dikembalikan",
            'id' => $id,
            'dataPost' => $dataPost,
            'biaya' => $this->m_biaya->alldatapjum($id_transaksi, $jenis_biaya, $id_pjum),
            'role' => $role,
            'solo' => $kota['kota'],
            'submit' => $submit_pjum['submit_pjum'],
            'submit_pjum' => $submit_pjum['submit_pjum'],
            'submit_pb' => $submit_pb['submit_pb'],
            'cek' => $cek,
            'cekpjum' => $this->m_kategori->alldataId($id_transaksi, 'PJUM'),
            'cekpb' => $this->m_kategori->alldataId($id_transaksi, 'PB'),
            'id_pjum' => $id_pjum,
            'index' => count((array)$valas),
            'kode_valas' => $kode_valas,
            'nomor' => $this->m_pjum->getData($id_transaksi, $id_pjum),
            'pum' => $this->m_pum->sisa($id_transaksi, $id_pjum),
            'id_pjum_tanggal' => $id_pjum,
            'pum' => $this->m_pum->alldataId($id_transaksi, $id_pjum),
            'neg' => $this->m_negara_tujuan->getDataAllId($id_transaksi),
            'nopb' => $nopb,
        ];
        echo view('proses/pjum/v_sisa', $data);
        // print_r(session()->get());
    }

    public function editnopjum($id_pjum, $id_transaksi, $jenis_biaya)
    {
        if($this->request->getMethod() == 'post') {
            $nik = session()->get('akun_nik');
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
                    $hitung_kurs_tengah = ($kurs_beli + $kurs_jual) / 2;
                    $kurs_tengah = (string)round($hitung_kurs_tengah, 2);

                    $id_kurs = $this->m_kurs->where('id_pjum', $id_pjum)->select('id_kurs')->first();

                    if (empty($id_kurs)) {
                        $data_kurs = [
                            'id_transaksi' => $id_transaksi,
                            'id_pjum' => $id_pjum,
                            'id_pb' => null,
                            'id_valas' => $id_valas,
                            'kode_valas' => $kode_valas,
                            'tanggal' => $tanggal,
                            'kurs' => $kurs_tengah,
                        ];
                        $this->m_kurs->insert($data_kurs);
                    } else if (!empty($id_kurs)) {
                        $data_kurs_edit = [
                            'id_kurs' => $id_kurs['id_kurs'],
                            'id_pjum' => $id_pjum,
                            'id_pb' => null,
                            'id_valas' => $id_valas,
                            'kode_valas' => $kode_valas,
                            'tanggal' => $tanggal,
                            'kurs' => $kurs_tengah,
                        ];
                        $this->m_kurs->save($data_kurs_edit);
                    }
                }

                $tanggal = $this->request->getVar('tanggal');

                $data_pjum = [
                    'id_pjum' => $id_pjum,
                    'id_valas' => $id_valas,
                    'kode_valas' => $kode_valas,
                    'nomor' => $nomor,
                    'tanggal' => $tanggal,
                    'edited_at' => $timestamp,
                    'edited_by' => $nik,
                ];
                $this->m_pjum->save($data_pjum);

                session()->setFlashdata('success', 'Nomor PJUM berhasil diubah');
                return redirect()->to('listpjum/'.$jenis_biaya.'/'.$id_transaksi);
            } else {
                $tanggal = $this->request->getVar('tanggal');

                $data_pjum = [
                    'id_pjum' => $id_pjum,
                    'id_valas' => $id_valas,
                    'kode_valas' => $kode_valas,
                    'nomor' => $nomor,
                    'tanggal' => $tanggal,
                    'edited_at' => $timestamp,
                    'edited_by' => $nik,
                ];
                $this->m_pjum->save($data_pjum);

                session()->setFlashdata('success', 'Nomor PJUM berhasil diubah');
                return redirect()->to('listpjum/'.$jenis_biaya.'/'.$id_transaksi);
            }
        }

        // $kurs = $this->m_kurs->findAll();
        // $index_kurs = $this->m_kurs->where('id_valas', $id_valas)->where('tanggal', $tanggal)->select('id_valas, tanggal, kurs')->first();
        // if (empty($index_kurs)) {
        //     foreach ($kurs as $key => $value) {
        //         if (empty($id_kurs['id_kurs']) && $id_valas == $value['id_valas'] && $tanggal == $value['tanggal']) {
        //             $data_kurs = [
        //                 'id_transaksi' => $id_transaksi,
        //                 'id_pjum' => $id_pjum,
        //                 'id_pb' => null,
        //                 'id_valas' => $id_valas,
        //                 'kode_valas' => $kode_valas,
        //                 'tanggal' => $tanggal,
        //                 'kurs' => $kurs_tengah,
        //             ];
        //             // d($data_kurs);
        //             // $this->m_kurs->insert($data_kurs);
        //         }

        //         // else if (!empty($id_kurs['id_kurs']) && $id_valas == $value['id_valas'] && $tanggal == $value['tanggal']){
        //         //     $data_kurs_edit = [
        //         //         'id_kurs' => $id_kurs['id_kurs'],
        //         //         'id_transaksi' => $id_transaksi,
        //         //         'id_pjum' => $id_pjum,
        //         //         'id_pb' => null,
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
        //             'id_pjum' => $id_pjum,
        //             'id_pb' => null,
        //             'id_valas' => $id_valas,
        //             'kode_valas' => $kode_valas,
        //             'tanggal' => $tanggal,
        //             'kurs' => $kurs_tengah,
        //         ];
        //         // d($data_kurs);
        //         // $this->m_kurs->insert($data_kurs);
        //     }

        //     // else if (!empty($id_kurs['id_kurs']) && $id_valas == $index_kurs['id_valas'] && $tanggal == $index_kurs['tanggal']){
        //     //     $data_kurs_edit = [
        //     //         'id_kurs' => $id_kurs['id_kurs'],
        //     //         'id_transaksi' => $id_transaksi,
        //     //         'id_pjum' => $id_pjum,
        //     //         'id_pb' => null,
        //     //         'id_valas' => $id_valas,
        //     //         'kode_valas' => $kode_valas,
        //     //         'tanggal' => $tanggal,
        //     //         'kurs' => $index_kurs['kurs'],
        //     //     ];
        //     //     $this->m_kurs->save($data_kurs_edit);
        //     // }
        // }
    }

    public function tanggalpjum($id_pjum, $id_transaksi, $jenis_biaya)
    {
        $nik = session()->get('akun_nik');
        $niknm = session()->get('niknm');
        $role = session()->get('akun_role');
        $strorg = session()->get('strorg');

        $login = $this->m_id->where('id_transaksi', $id_transaksi)->select('login_by')->first();

        if ($role == 'admin' && $login['login_by'] != $niknm || $role == 'user' && $login['login_by'] != $niknm) {
            session()->setFlashdata('warning', ['Id transaksi sedang diedit, harap menunggu beberapa saat lagi']);
            return redirect()->to("transaksi");
        } else {

        }

        if($role == 'admin') {
            $user = $this->m_id->getPostId($id_transaksi, substr($strorg, 0, 4));
        } elseif($role == 'user') {
            $user = $this->m_id->getId($id_transaksi, $nik);
        } elseif($role == 'treasury') {
            $user = $this->m_id->getTreasuryDashboard($id_transaksi);
        } elseif($role == 'gs') {
            $user = $this->m_id->getGSDashboard($id_transaksi);
        }
        $dataPost = $this->m_pjum->getData($id_transaksi, $id_pjum);
        if(empty($dataPost)) {
            return redirect()-> to("transaksi");
        }
        if(empty($user)) {
            return redirect()-> to("transaksi");
        }

        $data = $dataPost;

        $kat = $this->m_kategori->alldataId($id_transaksi, $jenis_biaya);
        if(empty($kat)) {
            session()->setFlashdata('warning', ['Tidak ada data, silahkan upload data biaya terlebih dahulu']);
            return redirect()-> to('dashboard/'.$id_transaksi);
        }

        $bia = $this->m_biaya->alldatapjum($id_transaksi, $jenis_biaya, $id_pjum);
        if(empty($bia)) {
            session()->setFlashdata('warning', ['Tidak ada data, silahkan upload data biaya terlebih dahulu']);
            return redirect()-> to('listpjum/pjum/'.$id_transaksi);
        }

        if($role == 'treasury') {
            $id = $this->m_id->getTreasury($id_transaksi);
        } elseif($role == 'gs') {
            $id = $this->m_id->getGS($id_transaksi);
        } else {
            $id = $this->m_id->getPostId($id_transaksi, substr($strorg, 0, 4));
        }

        $ses = [
            'id_transaksi' => $id_transaksi,
            'jenis_biaya' => $jenis_biaya,
            'id_pjum' => $id_pjum,
        ];

        session()->set($ses);

        if($jenis_biaya == 'pb') {
            return redirect()-> to('datapjum/'.$jenis_biaya.'/'.$id_transaksi.'/'.$id_pjum);
        }

        $submit_pjum = $this->m_id->where('id_transaksi', $id_transaksi)->select('submit_pjum, kirim_pjum')->first();
        $submit_pb = $this->m_id->where('id_transaksi', $id_transaksi)->select('submit_pb, kirim_pb')->first();

        $cek = $this->m_kategori->cek($id_transaksi);

        if ($role == 'treasury' || $role == 'gs') {
            return redirect()-> to("transaksi");
        }

        if($role == 'admin' && $submit_pjum['submit_pjum'] != 0 || $role == 'user' && $submit_pjum['submit_pjum'] != 0) {
            return redirect()-> to("transaksi");
        }

        if($this->request->getMethod() == 'post') {
            $nik = session()->get('akun_nik');
            $timestamp = date('Y-m-d H:i:s');
            $kurs_bi = ['AUD', 'BND', 'CAD', 'CHF', 'CNH', 'CNY', 'DKK', 'EUR', 'GBP', 'HKD', 'JPY', 'KRW', 'KWD', 'LAK', 'MYR', 'NOK', 'NZD', 'PGK', 'PHP', 'SAR', 'SEK', 'SGD', 'THB', 'USD', 'VND'];

            $tanggal = $this->request->getVar('tanggal');
            $id_valas = $this->request->getVar('id_valas');
            $kode_valas = $this->request->getVar('kode_valas');

            $tanggal_date = (strtotime($tanggal));

            $kurs = $this->m_kurs->findAll();
            $id_kurs = $this->m_kurs->where('id_pjum', $id_pjum)->select('id_kurs')->first();
            $index_kurs = $this->m_kurs->where('id_valas', $id_valas)->where('tanggal', $tanggal)->select('id_valas, tanggal, kurs')->first();
            
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
                    $hitung_kurs_tengah = ($kurs_beli + $kurs_jual) / 2;
                    $kurs_tengah = (string)round($hitung_kurs_tengah, 2);

                    $id_kurs = $this->m_kurs->where('id_pjum', $id_pjum)->select('id_kurs')->first();

                    if (empty($id_kurs)) {
                        $data_kurs = [
                            'id_transaksi' => $id_transaksi,
                            'id_pjum' => $id_pjum,
                            'id_pb' => null,
                            'id_valas' => $id_valas,
                            'kode_valas' => $kode_valas,
                            'tanggal' => $tanggal,
                            'kurs' => $kurs_tengah,
                        ];
                        $this->m_kurs->insert($data_kurs);
                    } else if (!empty($id_kurs)) {
                        $data_kurs_edit = [
                            'id_kurs' => $id_kurs['id_kurs'],
                            'id_pjum' => $id_pjum,
                            'id_pb' => null,
                            'id_valas' => $id_valas,
                            'kode_valas' => $kode_valas,
                            'tanggal' => $tanggal,
                            'kurs' => $kurs_tengah,
                        ];
                        $this->m_kurs->save($data_kurs_edit);
                    }
                }

                $tanggal = $this->request->getVar('tanggal');

                $data_pjum = [
                    'id_pjum' => $id_pjum,
                    'id_valas' => $id_valas,
                    'kode_valas' => $kode_valas,
                    'tanggal' => $tanggal,
                    'edited_at' => $timestamp,
                    'edited_by' => $nik,
                ];
                $this->m_pjum->save($data_pjum);

                session()->setFlashdata('success', 'Tanggal Pembuatan No PJUM berhasil ditambahkan');
                return redirect()->to('datapjum/'.$jenis_biaya.'/'.$id_transaksi.'/'.$id_pjum);
            } else {
                $tanggal = $this->request->getVar('tanggal');

                $data_pjum = [
                    'id_pjum' => $id_pjum,
                    'id_valas' => $id_valas,
                    'kode_valas' => $kode_valas,
                    'tanggal' => $tanggal,
                    'edited_at' => $timestamp,
                    'edited_by' => $nik,
                ];
                $this->m_pjum->save($data_pjum);

                session()->setFlashdata('success', 'Tanggal Pembuatan No PJUM berhasil ditambahkan');
                return redirect()->to('datapjum/'.$jenis_biaya.'/'.$id_transaksi.'/'.$id_pjum);
            }
        }

        $valas = $this->m_biaya->valaspjum($id_transaksi, $jenis_biaya, $id_pjum);
        $kode_valas = $this->m_biaya->kode_valaspjum($id_transaksi, $jenis_biaya, $id_pjum);

        $kota = $this->m_id->where('id_transaksi', $id_transaksi)->select('kota')->first();
        $nopb = $this->m_pb->where('id_transaksi', $id_transaksi)->select('created_by')->findAll();
        session()->set('url_transaksi', current_url());

        $data = [
            'header' => "Tanggal Pembuatan No PJUM",
            'id' => $id,
            'dataPost' => $dataPost,
            'biaya' => $this->m_biaya->alldatapjum($id_transaksi, $jenis_biaya, $id_pjum),
            'role' => $role,
            'solo' => $kota['kota'],
            'submit' => $submit_pjum['submit_pjum'],
            'submit_pjum' => $submit_pjum['submit_pjum'],
            'submit_pb' => $submit_pb['submit_pb'],
            'cek' => $cek,
            'cekpjum' => $this->m_kategori->alldataId($id_transaksi, 'PJUM'),
            'cekpb' => $this->m_kategori->alldataId($id_transaksi, 'PB'),
            'id_pjum' => $id_pjum,
            'index' => count((array)$valas),
            'kode_valas' => $kode_valas,
            'valas1' => $this->m_pum->valas($id_transaksi),
            'nomor' => $this->m_pjum->getData($id_transaksi, $id_pjum),
            'nomor1' => $this->m_pjum->getData($id_transaksi, $id_pjum),
            'id_pjum_tanggal' => $id_pjum,
            'pum' => $this->m_pum->alldataId($id_transaksi, $id_pjum),
            'neg' => $this->m_negara_tujuan->getDataAllId($id_transaksi),
            'kurs' => $this->m_kurs->getkurspjum($id_pjum),
            'nopb' => $nopb,
        ];
        echo view('proses/pjum/v_tanggalpjum', $data);
        // print_r(session()->get());
    }

    public function kurspjum($id_transaksi, $jenis_biaya, $id_pjum)
    {
        $nik = session()->get('akun_nik');
        $role = session()->get('akun_role');
        $strorg = session()->get('strorg');
        if($role == 'treasury') {
            $dataPost = $this->m_id->getTreasuryDashboard($id_transaksi);
        } else {
            return redirect()-> to("transaksi");
        }

        if(empty($dataPost)) {
            return redirect()-> to("transaksi");
        }
        $data = $dataPost;

        if($role == 'treasury') {
            $id = $this->m_id->getTreasury($id_transaksi);
        }

        $submit_pjum = $this->m_id->where('id_transaksi', $id_transaksi)->select('submit_pjum')->first();
        $submit_pb = $this->m_id->where('id_transaksi', $id_transaksi)->select('submit_pb')->first();

        if ($role != 'treasury') {
            return redirect()-> to("transaksi");
        } elseif ($role == 'treasury' && $submit_pjum['submit_pjum'] != 1) {
            return redirect()-> to("transaksi");
        }

        if($jenis_biaya == 'pb') {
            return redirect()-> to('datapjum/'.$jenis_biaya.'/'.$id_transaksi.'/'.$id_pjum);
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

        if($this->request->getMethod() == 'post') {
            $data = $this->request->getVar(); //setiap yang diinputkan akan dikembalikan ke view

            $kurs = $this->request->getVar('kurs');
            $string = preg_replace('~[.,](?=\d{2}\b)|\p{Sc}~u', '#', $kurs);
            $string = strtr(rtrim($string, '#'), ['#' => '.', '.' => '', '.' => '']);
            $data = [
                'id_transaksi' => $id_transaksi,
                'id_pjum' => $id_pjum,
                'id_pb' => null,
                'id_valas' => $this->request->getVar('id_valas'),
                'kode_valas' => $this->request->getVar('kode_valas'),
                'tanggal' => $this->request->getVar('tanggal'),
                'kurs' => $string,
            ];
            $this->m_kurs->insert($data);
            session()->setFlashdata('success', 'Kurs berhasil ditambahkan');
            return redirect()->to('datapjum/'.$jenis_biaya.'/'.$id_transaksi.'/'.$id_pjum);
        }

        $valas = $this->m_biaya->valaspjum($id_transaksi, $jenis_biaya, $id_pjum);
        $kode_valas = $this->m_biaya->kode_valaspjum($id_transaksi, $jenis_biaya, $id_pjum);

        $kota = $this->m_id->where('id_transaksi', $id_transaksi)->select('kota')->first();
        $nopb = $this->m_pb->where('id_transaksi', $id_transaksi)->select('created_by')->findAll();
        session()->set('url_transaksi', current_url());

        $data = [
            'header' => "Kurs PJUM",
            'id' => $id,
            'dataPost' => $dataPost,
            'biaya' => $this->m_biaya->alldatapjum($id_transaksi, $jenis_biaya, $id_pjum),
            'role' => $role,
            'solo' => $kota['kota'],
            'submit' => $submit_pjum['submit_pjum'],
            'submit_pjum' => $submit_pjum['submit_pjum'],
            'submit_pb' => $submit_pb['submit_pb'],
            'cek' => $cek,
            'cekpjum' => $this->m_kategori->alldataId($id_transaksi, 'PJUM'),
            'cekpb' => $this->m_kategori->alldataId($id_transaksi, 'PB'),
            'id_pjum' => $id_pjum,
            'index' => count((array)$valas),
            'kode_valas' => $kode_valas,
            'nomor' => $this->m_pjum->getData($id_transaksi, $id_pjum),
            'pum' => $this->m_pum->alldataId($id_transaksi, $id_pjum),
            'neg' => $this->m_negara_tujuan->getDataAllId($id_transaksi),
            'kurs' => $this->m_kurs->getkurspjum($id_pjum),
            'nopb' => $nopb,
        ];
        echo view('proses/pjum/v_kurspjum', $data);
        // print_r(session()->get());
    }

    public function editkurspjum($id_transaksi, $jenis_biaya, $id_pjum, $id_kurs)
    {
        $nik = session()->get('akun_nik');
        $role = session()->get('akun_role');
        $strorg = session()->get('strorg');
        if($role == 'treasury') {
            $dataPost = $this->m_id->getTreasuryDashboard($id_transaksi);
        } else {
            return redirect()-> to("transaksi");
        }

        if(empty($dataPost)) {
            return redirect()-> to("transaksi");
        }
        $data = $dataPost;

        if($role == 'treasury') {
            $id = $this->m_id->getTreasury($id_transaksi);
        }

        $submit_pjum = $this->m_id->where('id_transaksi', $id_transaksi)->select('submit_pjum')->first();
        $submit_pb = $this->m_id->where('id_transaksi', $id_transaksi)->select('submit_pb')->first();

        if ($role != 'treasury') {
            return redirect()-> to("transaksi");
        } elseif ($role == 'treasury' && $submit_pjum['submit_pjum'] != 1) {
            return redirect()-> to("transaksi");
        }

        if($jenis_biaya == 'pb') {
            return redirect()-> to('datapjum/'.$jenis_biaya.'/'.$id_transaksi.'/'.$id_pjum);
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
            return redirect()->to('datapjum/'.$jenis_biaya.'/'.$id_transaksi.'/'.$id_pjum);
        }

        $valas = $this->m_biaya->valaspjum($id_transaksi, $jenis_biaya, $id_pjum);
        $kode_valas = $this->m_biaya->kode_valaspjum($id_transaksi, $jenis_biaya, $id_pjum);

        $kota = $this->m_id->where('id_transaksi', $id_transaksi)->select('kota')->first();
        $nopb = $this->m_pb->where('id_transaksi', $id_transaksi)->select('created_by')->findAll();
        session()->set('url_transaksi', current_url());

        $data = [
            'header' => "Edit Kurs PJUM",
            'id' => $id,
            'dataPost' => $dataPost,
            'biaya' => $this->m_biaya->alldatapjum($id_transaksi, $jenis_biaya, $id_pjum),
            'role' => $role,
            'solo' => $kota['kota'],
            'submit' => $submit_pjum['submit_pjum'],
            'submit_pjum' => $submit_pjum['submit_pjum'],
            'submit_pb' => $submit_pb['submit_pb'],
            'cek' => $cek,
            'cekpjum' => $this->m_kategori->alldataId($id_transaksi, 'PJUM'),
            'cekpb' => $this->m_kategori->alldataId($id_transaksi, 'PB'),
            'id_pjum' => $id_pjum,
            'index' => count((array)$valas),
            'kode_valas' => $kode_valas,
            'nomor' => $this->m_pjum->getData($id_transaksi, $id_pjum),
            'pum' => $this->m_pum->alldataId($id_transaksi, $id_pjum),
            'neg' => $this->m_negara_tujuan->getDataAllId($id_transaksi),
            'kurs' => $this->m_kurs->getkurspjum($id_pjum),
            'nopb' => $nopb,
        ];
        echo view('proses/pjum/v_kurspjum', $data);
        // print_r(session()->get());
    }

    public function importpjum($jenis_biaya, $id_transaksi, $id_pjum)
    {
        $file = $this->request->getFile('file_excel_pjum');
        $ext = $file->getClientExtension();

        if($ext == 'xls') {
            $render = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
        } else {
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

        // $start = $workSheet->getCell('C1');
        // $batas = count($sheet[0]);

        $a = 1;
        $b = 1;
        $c = 1;
        $d = 1;

        for($r = 7; $r < $highestRow; $r++) {
            $tanggal = $sheet[$r][1];
            if (empty($tanggal)) {
                $tanggal = null;
            }
            $jenis_biaya = $sheet[$r][2];
            if (empty($jenis_biaya)) {
                $jenis_biaya = null;
            }
            $kategori = $sheet[$r][3];
            if (empty($kategori)) {
                $kategori = null;
            }
            $ref = $sheet[$r][4];
            if (empty($ref)) {
                $ref = null;
            }
            $note = $sheet[$r][5];
            if (empty($note)) {
                $note = null;
            }
            $nopjum = $sheet[$r][6];
            if (empty($nopjum)) {
                $nopjum = null;
            }
            $jumlah_personil = $sheet[$r][7];
            if (empty($jumlah_personil)) {
                $jumlah_personil = null;
            }

            $cekdata = $this->m_kategori->cekdatapjum($id_transaksi, $r + 1, $jenis_biaya, $id_pjum);

            $data = [];
            if(empty($cekdata)) {
                $nomor_pjum = [
                    'id_transaksi' => $id_transaksi,
                    'nomor' => $nopjum,
                ];
                $this->m_pjum->insert($nomor_pjum);

                $id_pjum = $this->m_pjum->where('id_transaksi', $id_transaksi)->where('nomor', $nopjum)->select('id_pjum as id_pjum')->first();

                $data_kategori_pjum = [
                    'baris' => $r + 1,
                    'id_transaksi' => $id_transaksi,
                    'id_pjum' => $id_pjum['id_pjum'],
                    'jenis_biaya' => 'PJUM',
                    'kategori' => $kategori,
                    'tanggal' => $tanggal,
                    'note' => $note,
                    'ref' => $ref,
                    'jumlah_personil' => $jumlah_personil,
                ];
                $this->m_kategori->insert($data_kategori_pjum);
            } elseif(empty($cekdata['baris'])) {

            } elseif($id_transaksi == $cekdata['id_transaksi'] && $r + 1 == $cekdata['baris'] && "PJUM" == $cekdata['jenis_biaya'] && $id_pjum == $cekdata['id_pjum']) {
                $resultKategori = $this->m_kategori->where('id_transaksi', $id_transaksi)->where('baris', $r + 1)->where('jenis_biaya', $jenis_biaya)->where('id_pjum', $id_pjum)->select('id_kategori as id_kategori, id_pjum')->first();
                $data_kategori_pjum_edit = [
                        'id_kategori' => $resultKategori['id_kategori'],
                        'jenis_biaya' => 'PJUM',
                        'kategori' => $kategori,
                        'tanggal' => $tanggal,
                        'note' => $note,
                        'ref' => $ref,
                        'jumlah_personil' => $jumlah_personil,
                    ];
                $this->m_kategori->distinct($data_kategori_pjum_edit['id_kategori']);
                $this->m_kategori->save($data_kategori_pjum_edit);

                $nomor_pjum_edit = [
                    'id_pjum' => $resultKategori['id_pjum'],
                    'nomor' => $nopjum,
                ];
                $this->m_pjum->distinct($nomor_pjum_edit['id_pjum']);
                $this->m_pjum->save($nomor_pjum_edit);
            }

            // if($sheet[$r][0] == null) {
            //     $this->m_kategori->query("SET foreign_key_checks = 0;");
            //     $this->m_kategori->truncate('kategori');
            // }

            for($k = 8; $k < $highestColumnIndex; ++$k) {
                // if $sheet[$r][0] == 'TOTAL'
                //
                // else
                $data = [];
                $kode_valas = $sheet[6][$k];
                $biaya = preg_replace("/[^0-9\.]/", "", $sheet[$r][$k]);
                $id_valas = $this->m_valas->where('kode_valas', $kode_valas)->select('id_valas as id_valas')->first();
                $simbol = $this->m_valas->where('kode_valas', $kode_valas)->select('simbol as simbol')->first();
                $pum = preg_replace("/[^0-9\.]/", "", $sheet[2][$k]);
                $uang_kembali = preg_replace("/[^0-9\.]/", "", $sheet[3][$k]);

                if (empty($biaya)) {
                    $biaya = 0;
                }
                if (empty($kode_valas)) {
                    $kode_valas = null;
                    $id_valas = null;
                    $simbol = null;
                }
                if (empty($pum)) {
                    $pum = 0;
                }
                if (empty($uang_kembali)) {
                    $uang_kembali = 0;
                }

                $id_pjum = $this->m_pjum->where('id_transaksi', $id_transaksi)->where('nomor', $nopjum)->select('id_pjum as id_pjum')->first();

                $cekpum = $this->m_pum->cekpum($id_transaksi, $k + 1);
                if(empty($cekpum)) {
                    $data_pum = [
                        'id_pjum' => $id_pjum['id_pjum'],
                        'kolom' => $k + 1,
                        'pum' => $pum,
                        'uang_kembali' => $uang_kembali,
                        'id_transaksi' => $id_transaksi,
                        'id_valas' => $id_valas['id_valas'],
                        'kode_valas' => $kode_valas,
                        'simbol' => $simbol['simbol'],
                    ];
                    $this->m_pum->insert($data_pum);
                } elseif(empty($cekpum['kolom'])) {

                } elseif($id_transaksi == $cekpum['id_transaksi'] && $k + 1 == $cekpum['kolom']) {
                    $resultPum = $this->m_pum->where('id_transaksi', $id_transaksi)->where('kolom', $k + 1)->select('id_pum as id_pum')->first();
                    $data_pum_edit = [
                        'id_pum' => $resultPum['id_pum'],
                        'pum' => $pum,
                        'uang_kembali' => $uang_kembali,
                        'id_valas' => $id_valas['id_valas'],
                        'kode_valas' => $kode_valas,
                        'simbol' => $simbol['simbol'],
                    ];
                    $this->m_pum->distinct($data_pum_edit['id_pum']);
                    $this->m_pum->save($data_pum_edit);
                }

                $resultKategori = $this->m_kategori->where('id_transaksi', $id_transaksi)->where('baris', $r + 1)->where('jenis_biaya', $jenis_biaya)->where('id_pjum', $id_pjum)->select('id_kategori as id_kategori')->first();
                $resultPum = $this->m_pum->where('id_transaksi', $id_transaksi)->where('kolom', $k + 1)->select('id_pum as id_pum')->first();

                $cekvaluta = $this->m_biaya->cekvalutapjum($id_transaksi, $r + 1, $k + 1, $jenis_biaya, $id_pjum);
                if(empty($cekvaluta)) {
                    $data_biaya_pjum = [
                        'id_kategori' => $resultKategori['id_kategori'],
                        'id_pum' => $resultPum['id_pum'],
                        'id_pjum' => $id_pjum['id_pjum'],
                        'baris' => $r + 1,
                        'kolom' => $k + 1,
                        'kategori' => $kategori,
                        'id_transaksi' => $id_transaksi,
                        'id_valas' => $id_valas['id_valas'],
                        'kode_valas' => $kode_valas,
                        'simbol' => $simbol['simbol'],
                        'jenis_biaya' => 'PJUM',
                        'biaya' => $biaya,
                    ];
                    $this->m_biaya->insert($data_biaya_pjum);
                } elseif(empty($cekvaluta['baris'])) {

                } elseif(empty($cekvaluta['kolom'])) {

                } elseif($id_transaksi == $cekvaluta['id_transaksi'] && $r + 1 == $cekvaluta['baris'] && $k + 1 == $cekvaluta['kolom'] && "PJUM" == $cekvaluta['jenis_biaya'] && $id_pjum == $cekvaluta['id_pjum']) {
                    $resultBiaya = $this->m_biaya->where('id_transaksi', $id_transaksi)->where('baris', $r + 1)->where('kolom', $k + 1)->where('jenis_biaya', $jenis_biaya)->select('id_biaya as id_biaya')->first();
                    $data_biaya_pjum_edit = [
                        'id_biaya' => $resultBiaya['id_biaya'],
                        'kategori' => $kategori,
                        'id_valas' => $id_valas['id_valas'],
                        'kode_valas' => $kode_valas,
                        'simbol' => $simbol['simbol'],
                        'jenis_biaya' => 'PJUM',
                        'biaya' => $biaya,
                    ];
                    $this->m_biaya->distinct($data_biaya_pjum_edit['id_biaya']);
                    $this->m_biaya->save($data_biaya_pjum_edit);
                }

                // if($sheet[$r][0] == null) {
                //     $this->m_biaya->query("SET foreign_key_checks = 0;");
                //     $this->m_biaya->truncate('biaya');
                // }
            }
        }
        session()->setFlashdata('success', 'Data PJUM berhasil di import');
        return redirect()->to('datapjum/'.$jenis_biaya.'/'.$id_transaksi.'/'.$id_pjum);
    }

    public function exportpjum($jenis_biaya, $id_transaksi, $id_pjum)
    {
        $kategori = $this->m_kategori->getDataIdtransaksipjum($id_transaksi, $jenis_biaya, $id_pjum);
        $biaya = $this->m_biaya->getDataBiayapjum($id_transaksi, $jenis_biaya, $id_pjum);

        $bawah = $this->m_kategori->where('id_transaksi', $id_transaksi)->where('jenis_biaya', $jenis_biaya)->where('id_pjum', $id_pjum)->select('baris')->orderBy('id_kategori', 'desc')->first();
        $baris = $this->m_kategori->where('id_transaksi', $id_transaksi)->where('jenis_biaya', $jenis_biaya)->where('id_pjum', $id_pjum)->select('baris')->orderBy('id_kategori', 'asc')->findAll();
        $nik = $this->m_id->where('id_transaksi', $id_transaksi)->select('nik')->first();
        $nomor = $this->m_pjum->where('id_transaksi', $id_transaksi)->where('id_pjum', $id_pjum)->select('nomor')->first();

        $nopjum = $nomor['nomor'];
        if ($nopjum == null) {
            $nopjum = "no pjum masih kosong";
        }

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName('Times New Roman');
        $spreadsheet->getDefaultStyle()->getFont()->setSize(12);
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle("Data PJUM");

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

        $pum = $this->m_pum->where('id_transaksi', $id_transaksi)->where('id_pjum', $id_pjum)->groupBy(['pum', 'id_transaksi', 'id_valas'])->orderBy('id_pum', 'asc')->select('pum')->findAll();
        $uang_kembali = $this->m_pum->where('id_transaksi', $id_transaksi)->where('id_pjum', $id_pjum)->groupBy(['uang_kembali', 'id_transaksi', 'id_valas'])->orderBy('id_pum', 'asc')->select('uang_kembali')->findAll();

        $arr1 = implode(' ', array_map(function ($entry) {
            return ($entry[key($entry)]);
        }, $pum));

        $exp1 = explode(' ', $arr1);

        $arr2 = implode(' ', array_map(function ($entry) {
            return ($entry[key($entry)]);
        }, $uang_kembali));

        $exp2 = explode(' ', $arr2);

        $sheet->setCellValue('B1', 'PERJALANAN DINAS LUAR NEGERI '.substr($nik_perso, 0, -1).'_'.$id_transaksi);
        $sheet->setCellValue('H3', 'PUM =>');
        $sheet->setCellValue('H4', 'SISA UANG DIKEMBALIKAN =>');
        $sheet->setCellValue('B4', 'Negara Tujuan =>');
        $sheet->setCellValue('C4', substr($tmp_negara, 0, -2));
        $sheet->setCellValue('B4', 'Negara Tujuan =>');
        $sheet->setCellValue('B6', 'Tanggal');
        $sheet->setCellValue('C6', 'Jenis_biaya');
        $sheet->setCellValue('D6', 'Kategori');
        $sheet->setCellValue('E6', 'Ref');
        $sheet->setCellValue('F6', 'Note');
        $sheet->setCellValue('G6', 'No PJUM');
        $sheet->setCellValue('H6', 'Jumlah Personil');
        $sheet->setCellValue('I6', 'Valas');

        $sheet->fromArray($exp1, null, 'I3');
        $sheet->fromArray($exp2, null, 'I4');

        $array = array('H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK');
        $valas = $this->m_biaya->valaspjum($id_transaksi, $jenis_biaya, $id_pjum);
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
        $sheet->mergeCells('I6:'.$alpha.'6');

        foreach ($biaya as $key => $value) {
            $i = 7;
            $column = $value['kolom'] - 2;
            $sheet->setCellValueByColumnAndRow($column, $i, $value['kode_valas']);
            $i++;
            for ($j = 8; $j <= $bawah['baris']; $j++) {
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
                $sheet->setCellValue('E'.$bar['baris'], $value['ref']);
                $sheet->setCellValue('F'.$bar['baris'], $value['note']);
                $sheet->setCellValue('G'.$bar['baris'], $nomor['nomor']);
                $sheet->setCellValue('H'.$bar['baris'], $value['jumlah_personil']);
                $sheet->getStyle('B6:'.$alpha.$bar['baris'])->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            }
        }

        for ($k = 'B'; $k <= $alpha; $k++) {
            $spreadsheet->getActiveSheet()->getColumnDimension($k)->setWidth(20);
        }

        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('F')->setAutoSize(true);
        $sheet->getColumnDimension('H')->setAutoSize(true);
        $sheet->getColumnDimension('A')->setVisible(false);

        $sheet->getStyle('B:H')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('B:H')->getAlignment()->setVertical('center');
        $sheet->getStyle('I:'.$alpha)->getAlignment()->setHorizontal('center');
        $sheet->getStyle('I:'.$alpha)->getNumberFormat()->setFormatCode('#,##0.00');

        $writer = new Xls($spreadsheet);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename=PJUM Perjalanan Dinas Luar Negeri/'.substr($nik_perso, 0, -1).'/'.$id_transaksi.'/'.$nopjum.'.xls');
        $writer->save("php://output");
        // $writer->save('PJUM '.substr($nik_perso, 0, -1).'_'.$id_transaksi.'.xls');
        // return redirect()->to('datapjum/'.$jenis_biaya.'/'.$id_transaksi.'/'.$id_pjum);
    }

    public function treasurypjum($id_kategori, $id_transaksi, $jenis_biaya, $id_pjum)
    {
        $nik = session()->get('akun_nik');
        $role = session()->get('akun_role');
        $strorg = session()->get('strorg');
        if($role == 'treasury') {
            $dataPost = $this->m_id->getTreasuryDashboard($id_transaksi);
        } else {
            return redirect()-> to("transaksi");
        }

        if(empty($dataPost)) {
            return redirect()-> to("transaksi");
        }
        $data = $dataPost;

        if($role == 'treasury') {
            $id = $this->m_id->getTreasury($id_transaksi);
        }

        $submit_pjum = $this->m_id->where('id_transaksi', $id_transaksi)->select('submit_pjum')->first();
        $submit_pb = $this->m_id->where('id_transaksi', $id_transaksi)->select('submit_pb')->first();

        if ($role != 'treasury') {
            return redirect()-> to("transaksi");
        } elseif ($role == 'treasury' && $submit_pjum['submit_pjum'] != 1) {
            return redirect()-> to("transaksi");
        }

        if($jenis_biaya == 'pb') {
            return redirect()-> to('datapjum/'.$jenis_biaya.'/'.$id_transaksi.'/'.$id_pjum);
        }

        if($this->request->getVar('aksi') == 'hapus' && $this->request->getVar('id_kategori')) {
            $record = [
                'id_kategori' => $id_kategori,
                'treasury' => null,
                'edited_at' => null,
                'edited_by' => null,
            ];
            $this->m_kategori->save($record);
            session()->setFlashdata('success', 'Note Treasury berhasil dihapus');
            return redirect()->to('datapjum/'.$jenis_biaya.'/'.$id_transaksi.'/'.$id_pjum);
        }

        $valas = $this->m_biaya->valas($id_transaksi, $jenis_biaya);
        $kode_valas = $this->m_biaya->kode_valaspjum($id_transaksi, $jenis_biaya, $id_pjum);

        if($this->request->getMethod() == 'post') {
            $data = $this->request->getVar(); //setiap yang diinputkan akan dikembalikan ke view

            $record = [
                'id_kategori' => $id_kategori,
                'treasury' => $this->request->getVar('treasury'),
                'edited_at' => null,
                'edited_by' => null,
            ];
            $this->m_kategori->save($record);
            session()->setFlashdata('success', 'Note Treasury berhasil ditambahkan');
            return redirect()->to('datapjum/'.$jenis_biaya.'/'.$id_transaksi.'/'.$id_pjum);
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

        $nopb = $this->m_pb->where('id_transaksi', $id_transaksi)->select('created_by')->findAll();

        session()->set('url_transaksi', current_url());

        $data = [
            'header' => "Note Treasury PJUM",
            'id_transaksi' => $this->m_id->getDataAll(),
            'id' => $id,
            'neg' => $this->m_negara_tujuan->getDataAllId($id_transaksi),
            'dataPost' => $dataPost,
            'kategori' => $this->m_kategori->alldataId($id_transaksi, $jenis_biaya),
            'index' => count((array)$valas),
            'kode_valas' => $kode_valas,
            'nomor' => $this->m_pjum->getData($id_transaksi, $id_pjum),
            'pum' => $this->m_pum->alldataId($id_transaksi, $id_pjum),
            'notetreasury' => $this->m_kategori->getDataTreasury($id_kategori, $id_transaksi, $jenis_biaya, $id_pjum),
            'id_pjum' => $id_pjum,
            'submit_pjum' => $submit_pjum['submit_pjum'],
            'submit_pb' => $submit_pb['submit_pb'],
            'role' => $role,
            'cek' => $cek,
            'cekpjum' => $this->m_kategori->alldataId($id_transaksi, 'PJUM'),
            'cekpb' => $this->m_kategori->alldataId($id_transaksi, 'PB'),
            'nopb' => $nopb,
        ];
        echo view('pjum/v_treasurypjum', $data);
        // print_r(session()->get());
    }

    public function treasuryselesaipjum($jenis_biaya, $id_transaksi)
    {
        $role = session()->get('akun_role');

        if($role != 'treasury') {
            return redirect()-> to("transaksi");
        }

        $id_pjum = $this->m_biaya->where('id_transaksi', $id_transaksi)->select('id_pjum, id_valas')->findAll();

        foreach ($id_pjum as $key => $value) {
            if ($value['id_pjum'] != null && $value['id_valas'] != 76) {
                $kurs = $this->m_kurs->where('id_pjum', $value['id_pjum'])->select('id_kurs, id_valas, id_pjum, kode_valas, tanggal, kurs')->findAll();
                if(empty($kurs)) {
                    session()->setFlashdata('warning', ['Tambahkan kurs terlebih dahulu untuk melakukan submit data']);
                    return redirect()-> to('listpjum/pjum/'.$id_transaksi);
                }
            }
        }

        $mail = new PHPMailer(true);
        $nikuser = $this->m_id->where('id_transaksi', $id_transaksi)->select('nik, strorgnm')->first();
        $niknmuser = $this->m_am21->where('nik', $nikuser['nik'])->select('niknm')->first();
        // try {
        //     //Server settings
        //     $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        //     $mail->isSMTP();
        //     $mail->Host       = 'mail.konimex.com';
        //     $mail->SMTPAuth   = true;
        //     $mail->Username   = PDLN_EMAIL;
        //     $mail->Password   = PDLN_PASS;
        //     $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        //     $mail->Port       = 587;

        //     //Recipients
        //     $mail->setFrom('noreply@konimex.com');
        //     $mail->addAddress('gsta@konimex.com');//Add a recipient (GS)
        //     $mail->addBCC('09002@intra.net');//Add a recipient (BAS)

        //     //Content
        //     $link = 'https://konimex.com:446/pdln/listpjum/pjum/'.$id_transaksi;
        //     $mail->Subject = 'Data PJUM Telah Divalidasi Bagian Treasury';
        //     $mail->Body    = nl2br("Bagian Treasury telah melakukan validasi data PJUM User bagian (").$niknmuser['niknm']."/".$nikuser['nik']."/".$nikuser['strorgnm'].("), untuk melihat data PJUM silahkan klik link: $link");

        //     $mail->send();
        //     echo 'Message has been sent';
        // } catch (Exception $e) {
        //     echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        // }

        $data = [
            'id_transaksi' => $id_transaksi,
            'submit_pjum' => 4,
        ];

        $timestamp = date('Y-m-d H:i:s');

        $cek_log_email = $this->m_log_email->where('id_transaksi', $id_transaksi)->select('id_log_email')->first();

        if (empty($cek_log_email)) {
            $log_email = [
                'id_transaksi' => $id_transaksi,
                'title' => 'Data PJUM Telah Divalidasi Bagian Treasury',
                'nik' => $nikuser['nik'],
                'submit_pjum' => 4,
                'kirim_pjum' => 1,
                'waktu_kirim' => $timestamp,
            ];
            $this->m_log_email->insert($log_email);
        } else {
            $log_email = [
                'id_log_email' => $cek_log_email['id_log_email'],
                'submit_pjum' => 4,
                'kirim_pjum' => 1,
                'waktu_kirim' => $timestamp,
            ];
            $this->m_log_email->save($log_email);
        }
        $this->m_id->save($data);

        session()->setFlashdata('success', 'Data PJUM berhasil disubmit');
        return redirect()->to('dashboard/'.$id_transaksi);
    }

    public function gsselesaipjum($jenis_biaya, $id_transaksi)
    {
        $role = session()->get('akun_role');
        if($role != 'gs') {
            return redirect()-> to("transaksi");
        }
        $data = [
            'id_transaksi' => $id_transaksi,
            'submit_pjum' => 4,
        ];
        $this->m_id->save($data);
        session()->setFlashdata('success', 'Data PJUM berhasil disubmit');
        return redirect()->to('listpjum/'.$jenis_biaya.'/'.$id_transaksi);
    }

    public function tanggalpj($id_pjum, $id_transaksi, $jenis_biaya)
    {
        if($this->request->getMethod() == 'post') {
            $nik = session()->get('akun_nik');
            $timestamp = date('Y-m-d H:i:s');

            $nomor = $this->request->getVar('nomor');
            $tanggal = $this->request->getVar('tanggal');
            $id_valas = $this->request->getVar('id_valas');
            $kode_valas = $this->request->getVar('kode_valas');

            $tanggal_date = (strtotime($tanggal));

            $kurs = $this->m_kurs->findAll();
            $id_kurs = $this->m_kurs->where('id_pjum', $id_pjum)->select('id_kurs')->first();
            $index_kurs = $this->m_kurs->where('id_valas', $id_valas)->where('tanggal', $tanggal)->select('id_valas, tanggal, kurs')->first();
            
            if($kode_valas != 'IDR' || $kode_valas != 'KHR' || $kode_valas != 'MMK'){
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
                        $id_kurs = $this->m_kurs->where('id_pjum', $id_pjum)->select('id_kurs')->first();
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
                    $hitung_kurs_tengah = ($kurs_beli + $kurs_jual) / 2;
                    $kurs_tengah = (string)round($hitung_kurs_tengah, 2);

                    if (empty($id_kurs)) {
                        $data_kurs = [
                            'id_transaksi' => $id_transaksi,
                            'id_pjum' => $id_pjum,
                            'id_pb' => null,
                            'id_valas' => $id_valas,
                            'kode_valas' => $kode_valas,
                            'tanggal' => $tanggal,
                            'kurs' => $kurs_tengah,
                        ];
                        $this->m_kurs->insert($data_kurs);
                    } elseif (!empty($id_kurs)) {
                        $data_kurs_edit = [
                            'id_kurs' => $id_kurs['id_kurs'],
                            'id_pjum' => $id_pjum,
                            'id_pb' => null,
                            'id_valas' => $id_valas,
                            'kode_valas' => $kode_valas,
                            'tanggal' => $tanggal,
                            'kurs' => $kurs_tengah,
                        ];
                        $this->m_kurs->save($data_kurs_edit);
                    }
                }

                $tanggal = $this->request->getVar('tanggal');

                $data_pjum = [
                    'id_pjum' => $id_pjum,
                    'id_valas' => $id_valas,
                    'kode_valas' => $kode_valas,
                    'nomor' => $nomor,
                    'tanggal' => $tanggal,
                    'edited_at' => $timestamp,
                    'edited_by' => $nik,
                ];
                $this->m_pjum->save($data_pjum);

                session()->setFlashdata('success', 'Tanggal Pembuatan No PJUM berhasil ditambahkan');
                return redirect()->to('datapjum/'.$jenis_biaya.'/'.$id_transaksi.'/'.$id_pjum);
            } else {
                $tanggal = $this->request->getVar('tanggal');

                $data_pjum = [
                    'id_pjum' => $id_pjum,
                    'id_valas' => $id_valas,
                    'kode_valas' => $kode_valas,
                    'nomor' => $nomor,
                    'tanggal' => $tanggal,
                    'edited_at' => $timestamp,
                    'edited_by' => $nik,
                ];
                $this->m_pjum->save($data_pjum);

                session()->setFlashdata('success', 'Tanggal Pembuatan No PJUM berhasil ditambahkan');
                return redirect()->to('datapjum/'.$jenis_biaya.'/'.$id_transaksi.'/'.$id_pjum);
            }
        }
    }

    public function editbiayapju($id_biaya, $id_kategori, $id_transaksi, $jenis_biaya, $id_pjum)
    {
        if($this->request->getMethod() == 'post') {
            $nik = session()->get('akun_nik');
            $timestamp = date('Y-m-d H:i:s');
            $biaya = $this->request->getVar('biaya');
            $comma = ',';
            $number = preg_replace('/[^0-9\\-]+/', '', $biaya);
            if(strpos($biaya, $comma) !== false) {
                $string = $number / 100;
            } else {
                $string = $number;
            }
            $kirim = $this->m_id->where('id_transaksi', $id_transaksi)->select('kirim_pjum')->first();

            if ($kirim['kirim_pjum'] == 0) {
                $data = [
                    'id_biaya' => $id_biaya,
                    'biaya' => $string,
                ];
                $this->m_biaya->save($data);

                session()->setFlashdata('success', 'Biaya PJUM berhasil diubah');
                return redirect()->to('datapjum/'.$jenis_biaya.'/'.$id_transaksi.'/'.$id_pjum);
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

                session()->setFlashdata('success', 'Biaya PJUM berhasil diubah');
                return redirect()->to('datapjum/'.$jenis_biaya.'/'.$id_transaksi.'/'.$id_pjum);
            }
        }
    }

    public function editbiayapu($id_pum, $id_transaksi, $jenis_biaya, $id_pjum)
    {
        if($this->request->getMethod() == 'post') {
            $nik = session()->get('akun_nik');
            $timestamp = date('Y-m-d H:i:s');
            $biaya = $this->request->getVar('biaya');
            $comma = ',';
            $number = preg_replace('/[^0-9\\-]+/', '', $biaya);
            if(strpos($biaya, $comma) !== false) {
                $string = $number / 100;
            } else {
                $string = $number;
            }

            $kirim = $this->m_id->where('id_transaksi', $id_transaksi)->select('kirim_pjum')->first();

            if ($kirim['kirim_pjum'] == 0) {
                $data = [
                    'id_pum' => $id_pum,
                    'pum' => $string,
                ];
                $this->m_pum->save($data);

                session()->setFlashdata('success', 'PUM berhasil diubah');
                return redirect()->to('datapjum/'.$jenis_biaya.'/'.$id_transaksi.'/'.$id_pjum);
            } else {
                $data = [
                    'id_pum' => $id_pum,
                    'pum' => $string,
                    'edited_at' => $timestamp,
                    'edited_by' => $nik,
                ];
                $this->m_pum->save($data);

                session()->setFlashdata('success', 'PUM berhasil diubah');
                return redirect()->to('datapjum/'.$jenis_biaya.'/'.$id_transaksi.'/'.$id_pjum);
            }
        }
    }

    public function editbiayasis($id_pum, $id_transaksi, $jenis_biaya, $id_pjum)
    {
        if($this->request->getMethod() == 'post') {
            $nik = session()->get('akun_nik');
            $timestamp = date('Y-m-d H:i:s');
            $biaya = $this->request->getVar('biaya');
            $comma = ',';
            $number = preg_replace('/[^0-9\\-]+/', '', $biaya);
            if(strpos($biaya, $comma) !== false) {
                $string = $number / 100;
            } else {
                $string = $number;
            }
            $kirim = $this->m_id->where('id_transaksi', $id_transaksi)->select('kirim_pjum')->first();

            if ($kirim['kirim_pjum'] == 0) {
                $data = [
                    'id_pum' => $id_pum,
                    'uang_kembali' => $string,
                ];
                $this->m_pum->save($data);

                session()->setFlashdata('success', 'Sisa Uang Dikembalikan berhasil diubah');
                return redirect()->to('datapjum/'.$jenis_biaya.'/'.$id_transaksi.'/'.$id_pjum);
            } else {
                $data = [
                    'id_pum' => $id_pum,
                    'uang_kembali' => $string,
                    'edited_at' => $timestamp,
                    'edited_by' => $nik,
                ];
                $this->m_pum->save($data);

                session()->setFlashdata('success', 'Sisa Uang Dikembalikan berhasil diubah');
                return redirect()->to('datapjum/'.$jenis_biaya.'/'.$id_transaksi.'/'.$id_pjum);
            }
        }
    }

    public function kurspj($id_transaksi, $jenis_biaya, $id_pjum)
    {
        $role = session()->get('akun_role');
        if($role != 'treasury') {
            return redirect()-> to("transaksi");
        }
        $kurs = $this->request->getVar('kurs');
        $string = preg_replace('~[.,](?=\d{2}\b)|\p{Sc}~u', '#', $kurs);
        $string = strtr(rtrim($string, '#'), ['#' => '.', '.' => '', '.' => '']);
        $data = [
            'id_transaksi' => $id_transaksi,
            'id_pjum' => $id_pjum,
            'id_pb' => null,
            'id_valas' => $this->request->getVar('id_valas'),
            'kode_valas' => $this->request->getVar('kode_valas'),
            'tanggal' => $this->request->getVar('tanggal'),
            'kurs' => $string,
        ];
        $this->m_kurs->insert($data);
        session()->setFlashdata('success', 'Kurs berhasil ditambahkan');
        return redirect()->to('datapjum/'.$jenis_biaya.'/'.$id_transaksi.'/'.$id_pjum);
    }

    public function editkurspj($id_transaksi, $jenis_biaya, $id_pjum, $id_kurs)
    {
        $role = session()->get('akun_role');
        if($role != 'treasury') {
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
        return redirect()->to('datapjum/'.$jenis_biaya.'/'.$id_transaksi.'/'.$id_pjum);
    }

    // cURL GET request
    public function getrequest()
    {
        $matauang = 'CNH';
        $lm = '2023-10-24';
        $tanggal = '2023-10-24';

        $apiURL = 'https://www.bi.go.id/biwebservice/wskursbi.asmx/getSubKursLokal3?mts=' . $matauang . '&startdate=' . $lm . '&enddate=' . $tanggal;

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

        foreach ($tables as $key => $value) {
            $kurs_beli = (string)$value->children()->beli_subkurslokal;
            $kurs_jual = (string)$value->children()->jual_subkurslokal;
            $kurs_tengah = ($kurs_beli + $kurs_jual)/2;
            d($kurs_jual);
            d($kurs_beli);
            d((string)$kurs_tengah);
            d((string)round($kurs_tengah, 2));
        }

        return $tables;

        // $array = json_decode(file_get_contents("https://raw.githubusercontent.com/guangrei/APIHariLibur_V2/main/calendar.json"), true);
        // $value = "2023-05-01";

        // if(isset($array[$value]) && $array[$value]["holiday"]) {
        //     echo"tanggal merah\n";
        //     print_r($array[$value]);
        // } elseif(date("D", strtotime($value)) === "Sun") {
        //     echo"tanggal merah hari minggu";
        // } else {
        //     echo"bukan tanggal merah";
        // }

        // //testing
        // $hari_ini = date("Y-m-d");

        // echo"<b>Check untuk hari ini (".date("d-m-Y", strtotime($hari_ini)).")</b><br>";
    }
}
