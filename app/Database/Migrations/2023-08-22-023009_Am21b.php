<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Am21b extends Migration
{
    public function up()
    {
        //
        $this->forge->addField([
            'id_am21b'=>[
                'type' => 'INT',
                'constraint' => 10,
                'auto_increment' => true
            ],
            'nik' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'unique' => true
            ],
            'noemailint' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'unique' => true
            ],
            'role' => [
                'type' => 'ENUM',
                'constraint' => ['admin', 'user', 'treasury', 'gs'],
                'default' => 'user'
            ],
            'created_at timestamp default now()'
        ]);
        $this->forge->addKey('id_am21b', true);
        $this->forge->createTable('am21b', true);
        // php spark migrate (Menambahkan tabel ke database)
        // php spark make:seeder akun (Menambahkan seeder baru bernama akun)
    }

    /*Melakukan drop tabel yang dipunya*/
    public function down()
    {
        //
        $this->forge->dropTable('am21b');
    }
}
