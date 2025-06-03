<?php

namespace App\Models;

use CodeIgniter\Model;

class LogEmailModel extends Model
{
    protected $table = "log_email";
    protected $primaryKey = "id_log_email";
    protected $allowedFields = ['id_transaksi', 'submit_pjum', 'submit_pb', 'kirim_pjum', 'kirim_pb', 'title', 'nik', 'waktu_kirim'];
}