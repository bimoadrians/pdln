<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Transaksi extends Migration
{
    public function up()
    {
        //
        $this->forge->addField([
            'id_transaksi'=>[
                'type' => 'INT',
                'constraint' => 10,
                'auto_increment' => true
            ],
            'nik' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
            ],
            'role' => [
                'type' => 'ENUM',
                'constraint' => ['admin', 'user', 'treasury', 'gs'],
                'default' => 'user'
            ],
            'strorg' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
            ],
            'jumlah_personil' => [
                'type' => 'VARCHAR',
                'constraint' => 10
            ],
            'tanggal_berangkat' => [
                'type' => 'DATE',
            ],
            'tanggal_pulang' => [
                'type' => 'DATE',
            ],
            'created_at timestamp default now()'
        ]);

        $this->forge->addKey('id_transaksi', true);
        $this->forge->createTable('transaksi');
    }

    public function down()
    {
        //
        $this->forge->dropTable('transaksi');
    }
}
