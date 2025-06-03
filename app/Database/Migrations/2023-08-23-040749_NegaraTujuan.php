<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class NegaraTujuan extends Migration
{
    public function up()
    {
        //
        $this->forge->addField([
            'id_negara_tujuan'=>[
                'type' => 'INT',
                'constraint' => 10,
                'auto_increment' => true
            ],
            'id_transaksi'=>[
                'type' => 'INT',
                'constraint' => 10,
            ],
            'negara_tujuan' => [
                'type' => 'VARCHAR',
                'constraint' => 100
            ],
        ]);

        $this->forge->addKey('id_negara_tujuan', true);
        $this->forge->createTable('negaratujuan');
    }

    public function down()
    {
        //
        $this->forge->dropTable('negaratujuan');
    }
}
