<?php
function kirim_email($attachment, $to, $title, $message)
{
    $email= \Config\Services::email();
    $email_pengirim=EMAIL_ALAMAT;//\App\Config\Constants
    $email_nama=EMAIL_NAMA;

    $config['userAgent'] = "CodeIgniter";
    $config['$protocol'] = "smtp";
    $config['mailPath'] = "/usr/sbin/sendmail";
    $config['SMTPHost'] = "smtp.googlemail.com";
    $config['SMTPUser'] = $email_pengirim;
    $config['SMTPPass'] = EMAIL_PASSWORD;
    $config['SMTPPort'] = 465;//465 587
    $config['SMTPTimeout'] = 60;//5
    $config['SMTPKeepAlive'] = false;
    $config['SMTPCrypto'] = "ssl";
    $config['wordWrap'] = true;
    $config['wrapChars'] = 76;
    $config['mailType'] = "html";
    $config['charset'] = "UTF-8";
    $config['validate'] = false;
    $config['priority'] = 3;
    $config['CRLF'] = "\r\n";
    $config['newline'] = "\r\n";
    $config['BCCBatchMode'] = "";
    $config[''] = false;
    $config['BCCBatchSize'] = 200;
    $config['DSN'] = false;

    $email->initialize($config);
    $email->setFrom($email_pengirim, $email_nama);
    $email->setTo($to);

    if($attachment) {
        $email->$attach($attachment);
    }

    $email->setSubject($title);
    $email->setMessage($message);

    if(!$email->send()) {
        $data=$email->printDebugger(['headers']);
        print_r($data);
        return false;
    } else {
        echo 'Email berhasil dikirim';
        return true;
    }
}

function email_pdln($attachment, $to, $title, $message)
{
    $email= \Config\Services::email();

    $config['userAgent'] = "CodeIgniter";
    $config['$protocol'] = "smtp";
    $config['mailPath'] = "/usr/sbin/sendmail";
    $config['SMTPHost'] = "mail.konimex.com";
    $config['SMTPUser'] = PDLN_EMAIL;
    $config['SMTPPass'] = PDLN_PASS;
    $config['SMTPPort'] = 587;//465 587
    $config['SMTPTimeout'] = 60;//5
    $config['SMTPKeepAlive'] = false;
    $config['SMTPCrypto'] = "ssl";
    $config['wordWrap'] = true;
    $config['wrapChars'] = 76;
    $config['mailType'] = "html";
    $config['charset'] = "UTF-8";
    $config['validate'] = false;
    $config['priority'] = 3;
    $config['CRLF'] = "\r\n";
    $config['newline'] = "\r\n";
    $config['BCCBatchMode'] = "";
    $config[''] = false;
    $config['BCCBatchSize'] = 200;
    $config['DSN'] = false;

    $email->initialize($config);
    $email->setFrom("noreply@konimex.com", "noreply");
    $email->setTo($to);

    if($attachment) {
        $email->$attach($attachment);
    }

    $email->setSubject($title);
    $email->setMessage($message);

    if(!$email->send()) {
        $data=$email->printDebugger(['headers']);
        print_r($data);
        return false;
    } else {
        echo 'Email berhasil dikirim';
        return true;
    }
}

function nomor($currentPage, $jumlahBaris)
{
    if(is_null($currentPage)) {
        $nomor = 1;
    } else {
        $nomor = 1 + ($jumlahBaris * ($currentPage - 1));
    }
    return $nomor;
}

function tanggal_indonesia($parameter)
{
    // 2023-07-15 12:06:08
    // tahun-bulan-hari waktu
    $split1 = explode(" ", $parameter);
    $parameter1 = $split1[0]; // diambil dari 2023-07-15

    $bulan = [
        '1' => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $hari =[
        '1' =>  'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'
    ];

    $num = date('N', strtotime($parameter1)); //tanggalnya di hari yang keberapa
    // 17 Juli dia berada di hari senin => 1

    $split2 = explode("-", $parameter1); // 2023, 07 , 11
    return $hari[$num].", ".$split2[2]." ".$bulan[(int)$split2[1]]." ".$split2[0];
}

function tanggal_indo($parameter)
{
    if ($parameter == null) {
        return false;
    } else {
        $bulan = [
            '1' => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        $hari =[
            '1' =>  'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'
        ];

        $num = date('N', strtotime($parameter)); //tanggalnya di hari yang keberapa
        // 17 Juli dia berada di hari senin => 1

        $split2 = explode("-", $parameter); // 2023, 07 , 11
        return $hari[$num].", ".$split2[2]." ".$bulan[(int)$split2[1]]." ".$split2[0];
    }
}

function tanggal_indo1($parameter)
{
    if ($parameter == null) {
        return false;
    } else {
        $bulan = [
            '1' => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        $hari =[
            '1' =>  'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'
        ];

        $num = date('N', strtotime($parameter)); //tanggalnya di hari yang keberapa
        // 17 Juli dia berada di hari senin => 1

        $split2 = explode("-", $parameter); // 2023, 07 , 11
        return $split2[2]." ".$bulan[(int)$split2[1]]." ".$split2[0];
    }
}

function purify($dirty_html)
{
    $config = HTMLPurifier_Config::createDefault();
    // $config -> set('URI.AllowedSchemes', array('data'=> true));
    $config->set('URI.AllowedSchemes', array('data' => true, 'http' => true, 'https' => true));
    $purifier = new HTMLPurifier($config);
    $clean_html = $purifier->purify($dirty_html);
    return $clean_html;
}
