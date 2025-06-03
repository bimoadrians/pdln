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

class log_pdln_t extends BaseController
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

    public function log_pdln_t()
    {
        $jam = date('H:i');
        if ($jam == '04:15') {
            $sql_cek_pjum = $this->m_id->where('submit_pjum !=', 4)->select('id_transaksi, strorgnm')->findAll();
            foreach ($sql_cek_pjum as $scp => $cek_pjum) {
                $timestamp = date('Y-m-d H:i:s');

                //Kirim ke User
                $sql_log_email_pjum_user = $this->m_log_email->where('id_transaksi', $cek_pjum['id_transaksi'])->where('submit_pjum', 0)->where('kirim_pjum', 1)->join('am21', 'am21.nik = log_email.nik', 'left')->join('am21b', 'am21b.nik = log_email.nik', 'left')->select('id_transaksi, log_email.nik, niknm, noemailint, waktu_kirim')->findAll();
                foreach ($sql_log_email_pjum_user as $slepu => $email_pjum_user) {
                    $timestamp = date('Y-m-d H:i:s');
                    $hari_pjum = strtotime($timestamp) - strtotime($email_pjum_user['waktu_kirim']);
                    if ($hari_pjum > 86400) {
                        $data_user_pjum[] = [
                            'id_transaksi_pjum' => $email_pjum_user['id_transaksi'],
                            'nik_pjum' => $email_pjum_user['nik'],
                            'strorgnm_pjum' => $cek_pjum['strorgnm'],
                            'niknm_pjum' => $email_pjum_user['niknm'],
                            'email_pjum' => $email_pjum_user['noemailint'],
                        ];
                    } else {
                        
                    }
                }

                //Kirim ke Treasury
                $sql_log_email_pjum_treasury = $this->m_log_email->where('id_transaksi', $cek_pjum['id_transaksi'])->where('submit_pjum', 1)->where('kirim_pjum', 1)->join('am21', 'am21.nik = log_email.nik', 'left')->select('id_transaksi, log_email.nik, niknm, waktu_kirim')->findAll();
                foreach ($sql_log_email_pjum_treasury as $slept => $email_pjum_treasury) {
                    $timestamp = date('Y-m-d H:i:s');
                    $hari_pjum = strtotime($timestamp) - strtotime($email_pjum_treasury['waktu_kirim']);
                    if ($hari_pjum > 86400) {
                        $data_treasury_pjum[] = [
                            'id_transaksi_pjum' => $email_pjum_treasury['id_transaksi'],
                            'nik_pjum' => $email_pjum_treasury['nik'],
                            'strorgnm_pjum' => $cek_pjum['strorgnm'],
                            'niknm_pjum' => $email_pjum_treasury['niknm'],
                        ];
                    } else {
                        
                    }
                }

                //Kirim ke GS
                // $sql_log_email_pjum_gs = $this->m_log_email->where('id_transaksi', $cek_pjum['id_transaksi'])->where('submit_pjum >', 1)->where('submit_pjum !=', 4)->where('kirim_pjum', 1)->join('am21', 'am21.nik = log_email.nik', 'left')->select('id_transaksi, log_email.nik, niknm, waktu_kirim')->findAll();
                // foreach ($sql_log_email_pjum_gs as $slepg => $email_pjum_gs) {
                //     $timestamp = date('Y-m-d H:i:s');
                //     $hari_pjum = strtotime($timestamp) - strtotime($email_pjum_gs['waktu_kirim']);
                //     if ($hari_pjum > 86400) {
                //         $data_gs_pjum[] = [
                //             'id_transaksi_pjum' => $email_pjum_gs['id_transaksi'],
                //             'nik_pjum' => $email_pjum_gs['nik'],
                //             'strorgnm_pjum' => $cek_pjum['strorgnm'],
                //             'niknm_pjum' => $email_pjum_gs['niknm'],
                //         ];
                //     } else {
                        
                //     }
                // }
            }

            $sql_cek_pb = $this->m_id->where('submit_pb !=', 4)->select('id_transaksi, strorgnm')->findAll();
            foreach ($sql_cek_pb as $scp => $cek_pb) {
                $timestamp = date('Y-m-d H:i:s');

                //Kirim ke User
                $sql_log_email_pb_user = $this->m_log_email->where('id_transaksi', $cek_pb['id_transaksi'])->where('submit_pb', 0)->where('kirim_pb', 1)->join('am21', 'am21.nik = log_email.nik', 'left')->join('am21b', 'am21b.nik = log_email.nik', 'left')->select('id_transaksi, log_email.nik, niknm, noemailint, waktu_kirim')->findAll();
                foreach ($sql_log_email_pb_user as $slepbu => $email_pb_user) {
                    $timestamp = date('Y-m-d H:i:s');
                    $hari_pb = strtotime($timestamp) - strtotime($email_pb_user['waktu_kirim']);
                    if ($hari_pb > 86400) {
                        $data_user_pb[] = [
                            'id_transaksi_pb' => $email_pb_user['id_transaksi'],
                            'nik_pb' => $email_pb_user['nik'],
                            'strorgnm_pb' => $cek_pb['strorgnm'],
                            'niknm_pb' => $email_pb_user['niknm'],
                            'email_pb' => $email_pb_user['noemailint'],
                        ];
                    } else {
                        
                    }
                }

                //Kirim ke Treasury
                $sql_log_email_pb_treasury = $this->m_log_email->where('id_transaksi', $cek_pb['id_transaksi'])->where('submit_pb', 1)->where('kirim_pb', 1)->join('am21', 'am21.nik = log_email.nik', 'left')->select('id_transaksi, log_email.nik, niknm, waktu_kirim')->findAll();
                foreach ($sql_log_email_pb_treasury as $slepbt => $email_pb_treasury) {
                    $timestamp = date('Y-m-d H:i:s');
                    $hari_pb = strtotime($timestamp) - strtotime($email_pb_treasury['waktu_kirim']);
                    if ($hari_pb > 86400) {
                        $data_treasury_pb[] = [
                            'id_transaksi_pb' => $email_pb_treasury['id_transaksi'],
                            'nik_pb' => $email_pb_treasury['nik'],
                            'strorgnm_pb' => $cek_pb['strorgnm'],
                            'niknm_pb' => $email_pb_treasury['niknm'],
                        ];
                    } else {
                        
                    }
                }

                //Kirim ke GS Cek Data PB
                $sql_log_email_pb_gs = $this->m_log_email->where('id_transaksi', $cek_pb['id_transaksi'])->where('submit_pb', 2)->where('kirim_pb', 1)->join('am21', 'am21.nik = log_email.nik', 'left')->select('id_transaksi, log_email.nik, niknm, waktu_kirim')->findAll();
                foreach ($sql_log_email_pb_gs as $slepbg => $email_pb_gs) {
                    $timestamp = date('Y-m-d H:i:s');
                    $hari_pb = strtotime($timestamp) - strtotime($email_pb_gs['waktu_kirim']);
                    if ($hari_pb > 86400) {
                        $data_gs_pb[] = [
                            'id_transaksi_pb' => $email_pb_gs['id_transaksi'],
                            'nik_pb' => $email_pb_gs['nik'],
                            'strorgnm_pb' => $cek_pb['strorgnm'],
                            'niknm_pb' => $email_pb_gs['niknm'],
                        ];
                    } else {
                        
                    }
                }

                //Kirim ke GS Cek Data Support
                $sql_log_email_pb_gs_sup = $this->m_log_email->where('id_transaksi', $cek_pb['id_transaksi'])->where('submit_pb', 3)->where('kirim_pb', 1)->join('am21', 'am21.nik = log_email.nik', 'left')->select('id_transaksi, log_email.nik, niknm, waktu_kirim')->findAll();
                foreach ($sql_log_email_pb_gs_sup as $slepbgs => $email_pb_gs_sup) {
                    $timestamp = date('Y-m-d H:i:s');
                    $hari_pb = strtotime($timestamp) - strtotime($email_pb_gs_sup['waktu_kirim']);
                    if ($hari_pb > 86400) {
                        $data_gs_pb_sup[] = [
                            'id_transaksi_pb' => $email_pb_gs_sup['id_transaksi'],
                            'nik_pb' => $email_pb_gs_sup['nik'],
                            'strorgnm_pb' => $cek_pb['strorgnm'],
                            'niknm_pb' => $email_pb_gs_sup['niknm'],
                        ];
                    } else {
                        
                    }
                }
            }

            if (empty($data_user_pjum)) {
                
            } else {
                foreach ($data_user_pjum as $dupj => $dat_upj) {
                    $mail = new PHPMailer(true);

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
                        $mail->addAddress($dat_upj['email_pjum']);//Add a recipient (User Bagian)
                        $mail->addBCC('09002@intra.net');//Add a recipient (BAS) 09002@intra.net

                        //Content
                        $link = 'https://konimex.com:446/pdln/listpjum/pjum/'.$dat_upj['id_transaksi_pjum'];
                        $mail->Subject = '(Local) Reminder Data PJUM Telah Diperiksa Bagian Treasury';
                        $mail->Body    = "Dengan hormat,\n\n".
                        "Bagian Treasury telah selesai melakukan pengecekan data PJUM, untuk melihat revisi data PJUM silahkan klik link: $link\n\n".
                        "Terima kasih.";

                        $mail->send();
                        echo "Message has been sent";

                        $log_email_all = [
                            'id_transaksi' => $dat_upj['id_transaksi_pjum'],
                            'title' => 'Reminder Revisi Data PJUM '.$dat_upj['nik_pjum'],
                            'nik' => $dat_upj['nik_pjum'],
                            'submit_pjum' => 0,
                            'waktu_kirim' => $timestamp,
                        ];
                        $this->m_log_email_all->insert($log_email_all);
                    } catch (Exception $e) {
                        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                    }
                }
            }

            if (empty($data_treasury_pjum)) {
                
            } else {
                foreach ($data_treasury_pjum as $dtpj => $dat_tpj) {
                    $mail = new PHPMailer(true);
                    
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
                        $link = 'https://konimex.com:446/pdln/listpjum/pjum/'.$dat_tpj['id_transaksi_pjum'];
                        $mail->Subject = 'Reminder Data PJUM Siap Periksa';
                        $mail->Body    = "Dengan hormat,\n\n".
                        "User bagian (".$dat_upj['niknm_pjum']."/".$dat_upj['nik_pjum']."/".$dat_upj['strorgnm_pjum'].") telah selesai mengisi data PJUM, silahkan periksa data PJUM dengan cara klik link: $link\n\n".
                        "Terima kasih.";

                        $mail->send();
                        echo "Message has been sent";

                        $log_email_all = [
                            'id_transaksi' => $dat_tpj['id_transaksi_pjum'],
                            'title' => 'Reminder Data PJUM '.$dat_tpj['nik_pjum'].' Siap Periksa',
                            'nik' => $dat_tpj['nik_pjum'],
                            'submit_pjum' => 1,
                            'waktu_kirim' => $timestamp,
                        ];
                        $this->m_log_email_all->insert($log_email_all);
                    } catch (Exception $e) {
                        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                    }
                }
            }

            // if (empty($data_gs_pjum)) {
                
            // } else {
            //     foreach ($data_gs_pjum as $dgpj => $dat_gpj) {
            //         $mail = new PHPMailer(true);

            //         try {
            //             //Server settings
            //             $mail->SMTPDebug = SMTP::DEBUG_SERVER;
            //             $mail->isSMTP();
            //             $mail->Host       = 'mail.konimex.com';
            //             $mail->SMTPAuth   = true;
            //             $mail->Username   = PDLN_EMAIL;
            //             $mail->Password   = PDLN_PASS;
            //             $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            //             $mail->Port       = 587;

            //             //Recipients
            //             $mail->setFrom('noreply@konimex.com');
            //             $mail->addAddress('gsta@konimex.com');//Add a recipient (GSTA) gsta@konimex.com
            //             $mail->addCC('eriwati@konimex.com');//Add a recipient (GS Manager) eriwati@konimex.com
            //             $mail->addCC('djiangming@konimex.com');//Add a recipient (GS Officer) djiangming@konimex.com
            //             $mail->addBCC('09002@intra.net');//Add a recipient (BAS) 09002@intra.net

            //             //Content
            //             $link = 'https://konimex.com:446/pdln/listpjum/pjum/'.$dat_gpj['id_transaksi_pjum'];
            //             $mail->Subject = 'Reminder Data PJUM Telah Divalidasi Bagian Treasury';
            //             $mail->Body    = "Dengan hormat,\n\n".
            //             "Bagian Treasury telah melakukan validasi data PJUM User bagian (".$dat_gpj['niknm_pjum']."/".$dat_gpj['nik_pjum']."/".$dat_gpj['strorgnm_pjum']."), untuk melihat data PJUM silahkan klik link: $link\n\n".
            //             "Terima kasih.";

            //             $mail->send();
            //             echo "Message has been sent";

            //             $log_email_all = [
            //                 'id_transaksi' => $dat_gpj['id_transaksi_pjum'],
            //                 'title' => 'Reminder Data PJUM '.$dat_gpj['nik_pjum'].' Telah Divalidasi',
            //                 'nik' => $dat_gpj['nik_pjum'],
            //                 'submit_pjum' => 2,
            //                 'waktu_kirim' => $timestamp,
            //             ];
            //             $this->m_log_email_all->insert($log_email_all);
            //         } catch (Exception $e) {
            //             echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            //         }
            //     }
            // }

            if (empty($data_user_pb)) {
                
            } else {
                foreach ($data_user_pb as $dupb => $dat_upb) {
                    $mail = new PHPMailer(true);

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
                        $mail->addAddress($dat_upb['email_pb']);//Add a recipient (User Bagian)
                        $mail->addBCC('09002@intra.net');//Add a recipient (BAS) 09002@intra.net

                        //Content
                        $link = 'https://konimex.com:446/pdln/listpb/pb/'.$dat_upb['id_transaksi_pb'];
                        $mail->Subject = '(Local) Reminder Data PB Telah Diperiksa Bagian Treasury';
                        $mail->Body    = "Dengan hormat,\n\n".
                        "Bagian Treasury telah selesai melakukan pengecekan data PB, untuk melihat revisi data PB silahkan klik link: $link\n\n".
                        "Terima kasih.";

                        $mail->send();
                        echo "Message has been sent";

                        $log_email_all = [
                            'id_transaksi' => $dat_upb['id_transaksi_pb'],
                            'title' => 'Reminder Revisi Data PB '.$dat_upb['nik_pb'],
                            'nik' => $dat_upb['nik_pb'],
                            'submit_pb' => 0,
                            'waktu_kirim' => $timestamp,
                        ];
                        $this->m_log_email_all->insert($log_email_all);
                    } catch (Exception $e) {
                        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                    }
                }
            }

            if (empty($data_treasury_pb)) {
                
            } else {
                foreach ($data_treasury_pb as $dtpb => $dat_tpb) {
                    $mail = new PHPMailer(true);

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
                        $link = 'https://konimex.com:446/pdln/listpb/pb/'.$dat_tpb['id_transaksi_pb'];
                        $mail->Subject = 'Reminder Data PB Siap Periksa';
                        $mail->Body    = "Dengan hormat,\n\n".
                        "User bagian (".$dat_tpb['niknm_pb']."/".$dat_tpb['nik_pb']."/".$dat_tpb['strorgnm_pb'].") telah selesai mengisi data PB, silahkan periksa data PB dengan cara klik link: $link\n\n".
                        "Terima kasih.";

                        $mail->send();
                        echo "Message has been sent";

                        $log_email_all = [
                            'id_transaksi' => $dat_tpb['id_transaksi_pb'],
                            'title' => 'Reminder Data PB '.$dat_tpb['nik_pb'].' Siap Periksa',
                            'nik' => $dat_tpb['nik_pb'],
                            'submit_pb' => 1,
                            'waktu_kirim' => $timestamp,
                        ];
                        $this->m_log_email_all->insert($log_email_all);
                    } catch (Exception $e) {
                        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                    }
                }
            }

            if (empty($data_gs_pb)) {
                
            } else {
                foreach ($data_gs_pb as $dgpb => $dat_gpb) {
                    $mail = new PHPMailer(true);

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
                        $link = 'https://konimex.com:446/pdln/listpb/pb/'.$dat_gpb['id_transaksi_pb'];
                        $mail->Subject = 'Reminder Data PB Telah Divalidasi Bagian Treasury';
                        $mail->Body    = "Dengan hormat,\n\n".
                        "Bagian Treasury telah melakukan validasi data PB User bagian (".$dat_gpb['niknm_pb']."/".$dat_gpb['nik_pb']."/".$dat_gpb['strorgnm_pb']."), untuk melihat data PB silahkan klik link: $link\n\n".
                        "Terima kasih.";

                        $mail->send();
                        echo "Message has been sent";

                        $log_email_all = [
                            'id_transaksi' => $dat_gpb['id_transaksi_pb'],
                            'title' => 'Reminder Data PB '.$dat_gpb['nik_pb'].' Telah Divalidasi',
                            'nik' => $dat_gpb['nik_pb'],
                            'submit_pb' => 2,
                            'waktu_kirim' => $timestamp,
                        ];
                        $this->m_log_email_all->insert($log_email_all);
                    } catch (Exception $e) {
                        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                    }
                }
            }

            if (empty($data_gs_pb_sup)) {
                
            } else {
                foreach ($data_gs_pb_sup as $dgpbs => $dat_gpbs) {
                    $mail = new PHPMailer(true);

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
                        $link = 'https://konimex.com:446/pdln/listpb/pb/'.$dat_gpbs['id_transaksi_pb'];
                        $mail->Subject = 'Reminder Pengisian Biaya Support Data PB';
                        $mail->Body    = "Dengan hormat,\n\n".
                        "Silahkan isikan biaya support data PB User bagian (".$dat_gpbs['niknm_pb']."/".$dat_gpbs['nik_pb']."/".$dat_gpbs['strorgnm_pb']."), untuk melihat data PB silahkan klik link: $link\n\n".
                        "Terima kasih.";

                        $mail->send();
                        echo "Message has been sent";

                        $log_email_all = [
                            'id_transaksi' => $dat_gpbs['id_transaksi_pb'],
                            'title' => 'Reminder Pengisian Biaya Support Data PB '.$dat_gpbs['nik_pb'],
                            'nik' => $dat_gpbs['nik_pb'],
                            'submit_pb' => 2,
                            'waktu_kirim' => $timestamp,
                        ];
                        $this->m_log_email_all->insert($log_email_all);
                    } catch (Exception $e) {
                        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                    }
                }
            }
        } else {
            d($jam);
        }
    }
}