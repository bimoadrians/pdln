<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class Akun extends Seeder
{
    public function run()
    {
        //
        $data = [
            'id_jabatan' => '4',
            'id_bagian' => '1',
            'nik' => '09002',
            'inisial' => 'BAS',
            'password' => password_hash('123123', PASSWORD_DEFAULT),
            'nama_lengkap' => 'Bimo Adrian Septianto',
            'email' => '09002@intra.net',
            'role' => 'user',
        ];
        $this->db->table('akun')->insert($data);
        //php spark db:seed akun (insert data)
    }
}
