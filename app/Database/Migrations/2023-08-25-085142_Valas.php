<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Valas extends Migration
{
    public function up()
    {
        //
        $this->forge->addField([
            'id_valas'=>[
                'type' => 'INT',
                'constraint' => 10,
                'auto_increment' => true
            ],
            'kode_valas' => [
                'type' => 'VARCHAR',
                'constraint' => 10
            ],
            'nama_valas' => [
                'type' => 'VARCHAR',
                'constraint' => 100
            ],
            'nama_negara' => [
                'type' => 'VARCHAR',
                'constraint' => 100
            ],
            'simbol' => [
                'type' => 'VARCHAR',
                'constraint' => 10
            ],
        ]);

        $this->forge->addKey('id_valas', true);
        $this->forge->createTable('valas');
    }

    public function down()
    {
        //
        $this->forge->dropTable('valas');
    }
}
