<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Bm06 extends Migration
{
    public function up()
    {
        //
        $this->forge->addField([
            'id_bm06'=>[
                'type' => 'INT',
                'constraint' => 10,
                'auto_increment' => true
            ],
            'strorg' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'unique' => true
            ],
            'strorgnm' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'unique' => true
            ],
            'tglsls' => [
                'type' => 'VARCHAR',
                'constraint' => 10
            ],
            'created_at timestamp default now()'
        ]);
        $this->forge->addKey('id_bm06', true);
        $this->forge->createTable('bm06', true);
        // php spark migrate (Menambahkan tabel ke database)
        // php spark make:seeder akun (Menambahkan seeder baru bernama akun)
    }

    /*Melakukan drop tabel yang dipunya*/
    public function down()
    {
        //
        $this->forge->dropTable('bm06');
    }
}
