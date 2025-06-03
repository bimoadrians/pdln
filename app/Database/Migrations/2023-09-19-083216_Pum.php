<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Pum extends Migration
{
    public function up()
    {
        //
        $this->forge->addField([
            'id_pum'=>[
                'type' => 'INT',
                'constraint' => 10,
                'auto_increment' => true
            ],
            'pum' => [
                'type' => 'VARCHAR',
                'constraint' => 100
            ],
            'id_biaya'=>[
                'type' => 'INT',
                'constraint' => 10,
            ],
            'id_transaksi'=>[
                'type' => 'INT',
                'constraint' => 10,
            ],
            'id_valas'=>[
                'type' => 'INT',
                'constraint' => 10,
            ],
            'kode_valas'=>[
                'type' => 'VARCHAR',
                'constraint' => 10,
            ],
            'simbol'=>[
                'type' => 'VARCHAR',
                'constraint' => 10,
            ],
        ]);

        $this->forge->addKey('id_pum', true);
        $this->forge->createTable('pum');
    }

    public function down()
    {
        //
        $this->forge->dropTable('pum');
    }
}
