<?php

namespace App\Models;

use CodeIgniter\Model;

class LogEmailAllModel extends Model
{
    protected $table = "log_email_all";
    protected $primaryKey = "id_log_email_all";
    protected $allowedFields = ['id_transaksi', 'submit_pjum', 'submit_pb', 'title', 'nik', 'waktu_kirim'];
}