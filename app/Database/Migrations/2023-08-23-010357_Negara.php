<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Negara extends Migration
{
    public function up()
    {
        //
        $this->forge->addField([
            'id_negara'=>[
                'type' => 'INT',
                'constraint' => 10,
                'auto_increment' => true
            ],
            'negara_tujuan' => [
                'type' => 'VARCHAR',
                'constraint' => 100
            ],
        ]);

        $this->forge->addKey('id_negara', true);
        $this->forge->createTable('negara');
    }

    public function down()
    {
        //
        $this->forge->dropTable('negara');
    }
}
