<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Personil extends Migration
{
    public function up()
    {
        //
        $this->forge->addField([
            'id_personil'=>[
                'type' => 'INT',
                'constraint' => 10,
                'auto_increment' => true
            ],
            'id_transaksi'=>[
                'type' => 'INT',
                'constraint' => 10,
            ],
            'niknm' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'nik' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
            ],
            'strorgnm' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'created_at timestamp default now()'
        ]);

        $this->forge->addKey('id_personil', true);
        $this->forge->createTable('personil');
    }

    public function down()
    {
        //
        $this->forge->dropTable('personil');
    }
}
