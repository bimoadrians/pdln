<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Biaya extends Migration
{
    public function up()
    {
        //
        $this->forge->addField([
            'id_biaya'=>[
                'type' => 'INT',
                'constraint' => 10,
                'auto_increment' => true
            ],
            'id_transaksi'=>[
                'type' => 'INT',
                'constraint' => 10,
            ],
            'kode_valas'=>[
                'type' => 'VARCHAR',
                'constraint' => 10,
            ],
            'jenis_biaya' => [
                'type' => 'ENUM',//enum itu pilihan brarti pilihannya article atau page
                'constraint' => ['pjum', 'pb'],
                'default' => 'pjum'
            ],
            'created_at timestamp default now()'
        ]);

        $this->forge->addKey('id_biaya', true);
        $this->forge->createTable('biaya');
    }

    public function down()
    {
        //
        $this->forge->dropTable('biaya');
    }
}
