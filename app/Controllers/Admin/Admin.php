<?php
namespace App\Controllers\Admin;

date_default_timezone_set("Asia/Jakarta");

use App\Controllers\BaseController;
use App\Models\TransaksiModel;
use App\Models\Am21Model;
use App\Models\Am21bModel;
use App\Models\Bm06Model;

class Admin extends BaseController
{
    public function __construct()
    {
        $this->m_id = new TransaksiModel();
        $this->m_admin = new Am21bModel();
        $this->m_am21 = new Am21Model();
        $this->m_bm06 = new Bm06Model();

        $this->validation = \Config\Services::validation();
        helper("cookie");//remember password, password disimpan di cookie
        helper("global_fungsi_helper");//kirim email di bagian APP/Helper
        helper('url');
    }

    public function userguide()
    {
        echo view("admin/v_userguide");
    }

    public function user()
    {
        return $this->response->download('./dokuserguide/User Guide Program Perjalanan Dinas Luar Negeri (User Bagian).pdf', null);
    }

    public function treasury()
    {
        return $this->response->download('./dokuserguide/User Guide Program Perjalanan Dinas Luar Negeri (Treasury).pdf', null);
    }

    public function gs()
    {
        return $this->response->download('./dokuserguide/User Guide Program Perjalanan Dinas Luar Negeri (General Service).pdf', null);
    }

    public function checkAuth($username,$password)
    {
        //use LDAP to check email & password
        $ldapserver = 'ipa.konimex.com';
        $ldapuser = 'cn=directory manager';
        $ldappass = 'd0ra3m0n';
        $ldaptree  = "cn=users,cn=accounts,dc=konimex,dc=com";
        $ldapfilter = "mail=".$username;
        $ldapattr = array("uid");
        $ldapconn = ldap_connect($ldapserver) or die("Could not connect to LDAP server.");
        if ($ldapconn) {
            $ldapbind = ldap_bind($ldapconn, $ldapuser, $ldappass) or die ("Error trying to bind: ".ldap_error($ldapconn));
            if ($ldapbind) {
                $result = ldap_search($ldapconn, $ldaptree, $ldapfilter, $ldapattr) or die ("Error in search query: ".ldap_error($ldapconn));
                $data = ldap_get_entries($ldapconn, $result);
                if(!empty($data[0]["uid"][0])){ // kasih if empty
                    $uiduser = $data[0]["uid"][0];
                    $ldapuserlogin = 'uid='.$uiduser.",".$ldaptree;
                    $checkpass = @ldap_bind($ldapconn, $ldapuserlogin, $password);
                    if ($checkpass)
                        return true;
                    else
                        return false;
                } else
                    return false;
            } else
                return false;
        }
        else
            return false;
    }
    
    public function login()
    {
        //https://onlinewebtutorblog-com.translate.goog/codeigniter-4-multi-auth-user-role-wise-login/?_x_tr_sl=en&_x_tr_tl=id&_x_tr_hl=id&_x_tr_pto=tc (Login multilevel)
        // session()->destroy(); //hapus cookie
        // exit();

        $data = [];
        
        if(get_cookie('cookie_email') && get_cookie('cookie_password')) {
            $noemailint = get_cookie('cookie_email');
            $password = get_cookie('cookie_password');
            $pass = '$2y$10$6T5PW/86TpupKtc3IcjUsOiD9GhoRLMAc0tmIS.1kfK2nNj/rqo0q';
            $dataAkun = $this->m_admin->getData($noemailint);
            
            if($password != $pass) { //$dataAkun['password']
                $arr[] = "Akun yang dimasukkan tidak sesuai";
                session()->setFlashdata('email', $noemailint);
                session()->setFlashdata('warning', $arr);

                delete_cookie('Cookie_email');//hapus cookie kalau tidak sesuai
                delete_cookie('Cookie_password');
                return redirect()->to("");
            }
            $akun = [
                'akun_nik'=> $dataAkun['nik'],
                'akun_role' => $dataAkun['role'],
                'akun_email' => $noemailint,
            ];
            session()->set($akun);
            return redirect()->to("sukses");
        }

        if($this->request->getMethod()=='post') {
            $rules = [
                'email'=>[
                    'rules'=>'required',
                    'errors'=>[
                        'required'=>'Email tidak boleh kosong'
                    ]
                ],
                'password'=>[
                    'rules'=> 'required',
                    'errors'=>[
                        'required'=>'Password tidak boleh kosong'
                    ]
                ]
            ];
            if(!$this->validate($rules)) {
                session()->setFlashdata("warning", $this->validation->getErrors());
                //return redirect()->to("admin/admin/login");
                return redirect()->to("");
            } /*else {
                session()->setFlashdata("success", "Anda berhasil masuk");
                return redirect()->to("admin/admin/login");
            }*/
            $noemailint = $this->request->getVar('email');
            $password = $this->request->getVar('password');//dari isian pengguna "123123";
            $remember_me = $this->request->getVar('remember_me');
            
            $dataAkun = $this->m_admin->getData($noemailint);//dari database

            if($noemailint == 'super@user' && password_verify($password, PASS_SUPER)) {
                $akun = [
                    'akun_email'=>'superuser',
                ];
                session()->set($akun);
                return redirect()->to("superuser");
            } else if(empty($dataAkun)) {
                $arr[]="Akun belum terdaftar.";
                session()->setFlashdata('warning', $arr);
                return redirect()->to("");
            } else {
                //lakukan pengecekan
                $check = $this->checkAuth($noemailint,$password);
                if($check){
                    $akun = [
                        'akun_nik'=> $dataAkun['nik'],
                        'akun_role' => $dataAkun['role'],
                        'akun_email'=>$dataAkun['noemailint'],
                    ];
                    session()->set($akun);
                    return redirect()->to("sukses")->withCookies();//cookies nya ikut
                } else {
                    $arr[]="Email atau Password salah.";
                    session()->setFlashdata('warning', $arr);
                    return redirect()->to("");
                }
            }
            
            // $pass = '$2y$10$tUBXtCiJLbiDhwARl8lxxOH.ImEORfdqizGCGpl8ZCcb0pgb7kCPu';
            // $dataAkun = $this->m_admin->getData($noemailint);//dari database
            // if($noemailint == 'superuser' && password_verify($password, PASS_SUPER)) {
            //     $akun = [
            //         'akun_email'=>'superuser',
            //     ];
            //     session()->set($akun);
            //     return redirect()->to("superuser");
            // } else if(empty($dataAkun)) {
            //     $arr[]="Email atau Password salah.";
            //     // session()->setFlashdata('email', $email);
            //     session()->setFlashdata('warning', $arr);
            // } else if(!password_verify($password, $pass)) { //$pass diganti $dataAkun['password']
            //     $arr[]="Email atau Password salah.";
            //     // session()->setFlashdata('email', $email);
            //     session()->setFlashdata('warning', $arr);
            //     //return redirect()->to("admin/admin/login");
            //     return redirect()->to("");
            // } else {
            //     $akun = [
            //         'akun_nik'=> $dataAkun['nik'],
            //         'akun_role' => $dataAkun['role'],
            //         'akun_email'=>$dataAkun['noemailint'],
            //     ];
            //     session()->set($akun);
                
            //     //return redirect()->to("admin/admin/sukses");

            //     return redirect()->to("sukses")->withCookies();//cookies nya ikut
            // }

            // if($remember_me == '1') {
            //     set_cookie("cookie_email", $noemailint, 3600*24*30);//bertahan 1 bulan
            //     set_cookie("cookie_password", $password, 3600*24*30);//$dataAkun['password']//bertahan 1 bulan
            // }
        }
        // print_r(session()->get());
        echo view("admin/v_login", $data);
        // echo password_hash("konimex", PASSWORD_DEFAULT);
    }

    public function superuser()
    {
        $data[] = '';

        if(session()->get('akun_email') == 'superuser'){

        } else {
            return redirect()->to('transaksi');
        }

        if($this->request->getMethod()=='post') {
            $oke = $this->request->getVar('oke');
            $dataAkun = $this->m_admin->getData($oke);
            if(empty($dataAkun)){
                session()->setFlashdata('warning', ['Tidak dapat login']);
                return redirect()->to('superuser');
            } else {
                $akun = [
                    'akun_nik'=> $dataAkun['nik'],
                    'akun_role' => $dataAkun['role'],
                    'akun_email'=>$dataAkun['noemailint'],
                ];
                session()->set($akun);
                return redirect()->to("sukses")->withCookies();//cookies nya ikut
            }
        }
        echo view("admin/v_superuser", $data);
    }

    public function sukses()
    {
        $role= session()->get('akun_role');
        $nik = session()->get('akun_nik');
        $am21 = $this->m_am21->getData($nik);

        if (empty($am21)) {
            return redirect()->to('logout');
        }

        $akun =[
            'niknm' => $am21['niknm'],
            'strorg' => $am21['strorg'],
            'tglbk' => $am21['tglbk'],
        ];
        session()->set($akun);

        $timestamp = date('Y-m-d H:i:s');
        $time = (strtotime($timestamp));

        $ses_time = [
            'login_at' => $time,
        ];
        session()->set($ses_time);

        $strorg = session()->get('strorg');
        $niknm = session()->get('niknm');
        $login_at = session()->get('login_at');

        $url_pdln = session()->get('url_pdln');

        $string = preg_replace("/[^0-9]/","", $url_pdln);

        if(strlen($string) > 1){
            $id_transaksi = substr($string, 3);
        } else {
            $id_transaksi = $string;
        }

        if(!session()->get('url_pdln')){
            return redirect()->to('transaksi');
        } else if(session()->get('url_pdln') && empty($id_transaksi)){
            if (session()->get('url_pdln') == 'http://localhost/pdln/tambahdataid') {
                return redirect()->to('tambahdataid');
            } else if (session()->get('url_pdln') == 'https://konimex.com:447/pdln/tambahdataid') {
                return redirect()->to('tambahdataid');
            } else {
                return redirect()->to('transaksi');
            }
        } else if(session()->get('url_pdln') && !empty($id_transaksi)){
            $login_by = $this->m_id->where('id_transaksi', $id_transaksi)->select('login_by')->first();
            if ($role == 'admin' && empty($login_by['login_by']) || $role == 'user' && empty($login_by['login_by'])) {
                $data = [
                    'id_transaksi' => $id_transaksi,
                    'login' => 1,
                    'login_by' => $niknm,
                ];
                $this->m_id->save($data);
            } else if ($role == 'treasury' || $role == 'gs'){
                
            } else {
                session()->setFlashdata('warning', ['Id transaksi sedang diedit, harap menunggu beberapa saat lagi']);
                return redirect()->to('transaksi');
            }

            return redirect()->to(session()->get('url_pdln'));
        }

        // d(session()->get());
        // echo "ISIAN COOKIE email " .get_cookie("cookie_email"). " DAN PASSWORD ".get_cookie("cookie_password");
    }

    public function logout()
    {
        $nik = session()->get('akun_nik');
        $niknm = session()->get('niknm');
        $strorg = session()->get('strorg');
        $id_transaksi = $this->m_id->where('SUBSTRING(strorg, 1, 4)', substr($strorg, 0, 4))->select('id_transaksi, login_by')->findAll();

        foreach ($id_transaksi as $key => $value) {
            if ($value['login_by'] == $niknm) {
                $data = [
                    'id_transaksi' => $value['id_transaksi'],
                    'login' => 0,
                    'login_by' => null,
                ];
                $this->m_id->save($data);
                if(session()->get('akun_email') != '') {
                    $data = [
                        'id_transaksi' => $value['id_transaksi'],
                        'login' => 0,
                        'login_by' => null,
                    ];
                    $this->m_id->save($data);
                } else {

                }
            } else if (empty($value['login_by'])){
                
            }
        }

        delete_cookie("cookie_email");
        delete_cookie("cookie_password");
        session()->destroy();
        if(session()->get('akun_email') != '') {
            session()->setFlashdata("success", "Anda telah keluar");
        }
        echo view("admin/v_login");
    }

    public function lupapassword()
    {
        $arr = [];
        if($this->request->getMethod()== 'post') {
            $email = $this->request->getVar('email');
            if($email=='') {
                $arr[]="Silahkan masukkan email atau email Anda";
            }
            if(empty($arr)) {
                $data=$this->m_admin->getData($email);
                if(empty($data)) {
                    $arr[] = "Akun yang kamu masukkan tidak terdaftar";
                }
            }
            if(empty($arr)) {
                $email=$data['email'];
                $token=md5(date('ymdhis'));//tahun bulan hari jam menit detik
                
                $link=site_url("resetpassword/?email=$email&token=$token");
                $attachment="";
                $to=$email;
                $title="Reset Password";
                // $message="Berikut ini link untuk reset password Anda $link";
                // $message.=" Silahkan klik link berikut ini $link";//.= maksudnya dibaris yang sama <br>
                $message= nl2br("Berikut ini link untuk reset password Anda: \n\n$link");

                kirim_email($attachment, $to, $title, $message);
                // exit();

                $dataUpdate=[
                    'email'=>$email,
                    'token'=>$token,
                ];
                $this->m_admin->updateData($dataUpdate);
                session()->setFlashdata("success", "Email recovery telah dikirim ke email anda");
            }
            if($arr) {
                session()->setFlashdata("email", $email);
                session()->setFlashdata("warning", $arr);
            }
            return redirect()->to("");
        }
        echo view("admin/v_lupapassword");
    }

    public function resetpassword()
    {
        $arr=[];
        $email =$this->request->getVar('email');        
        $token = $this->request->getVar('token');
        if($email != '' && $token != '') {
            $dataAkun = $this->m_admin->getData($email);//cek di tabel admin cocokan dengan url
            if(empty($dataAkun)) {
                $arr[]="Token tidak valid";
            } elseif($dataAkun['token'] != $token) {
                $arr[]="Token tidak valid";
            }
        } else {
            $arr[]="Parameter yang dikirimkan tidak valid";
        }
        if($arr) {
            session()->setFlashdata("warning", $arr);
        }

        if($this->request->getMethod()=='post') {
            $rules = [
                'password' =>[
                    'rules' => 'required|min_length[8]',
                    'errors' => [
                        'required' => 'Password harus diisi!',
                        'min_length' => 'Password harus berisi minimal 8 karakter'
                    ]
                ],
                'konfirmasi_password' => [
                    'rules' => 'required|min_length[8]|matches[password]',
                    'errors' => [
                        'required' => 'Konfirmasi Password harus diisi',
                        'min_length' => 'Konfirmasi Password harus berisi minimal 8 karakter',
                        'matches' => 'Konfirmasi password harus sama dengan password yang diisikan'
                    ]
                ]
            ];
            //tanda ! maksudnya kalo proses validate tidak sesuai lalu akan dikeluarkan warningnya
            if(!$this->validate($rules)) {
                session()->setFlashdata('warning', $this->validation->getErrors());
            } else {
                $dataUpdate=[
                    'email'=>$email,
                    'password'=>password_hash($this->request->getVar('password'), PASSWORD_DEFAULT),
                    'token'=>null
                ];
                $this->m_admin->updateData($dataUpdate);
                session()->setFlashdata('success', 'Password berhasil direset, silahkan login kembali');
                return redirect()->to("");
            }
        }
        echo view("admin/v_resetpassword");
    }
}