<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Am21 extends Migration
{
    public function up()
    {
        //
        $this->forge->addField([
            'id_am21'=>[
                'type' => 'INT',
                'constraint' => 10,
                'auto_increment' => true
            ],
            'nik' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'unique' => true
            ],
            'niknm' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'unique' => true
            ],
            'tglbk' => [
                'type' => 'VARCHAR',
                'constraint' => 10
            ],
            'strorg' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
            ],
            'created_at timestamp default now()'
        ]);
        $this->forge->addKey('id_am21', true);
        $this->forge->createTable('am21', true);
        // php spark migrate (Menambahkan tabel ke database)
        // php spark make:seeder akun (Menambahkan seeder baru bernama akun)
    }

    /*Melakukan drop tabel yang dipunya*/
    public function down()
    {
        //
        $this->forge->dropTable('am21');
    }
}
