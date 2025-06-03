<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Kota extends Migration
{
    public function up()
    {
        //
        $this->forge->addField([
            'id_kota'=>[
                'type' => 'INT',
                'constraint' => 10,
                'auto_increment' => true
            ],
            'kota' => [
                'type' => 'VARCHAR',
                'constraint' => 100
            ],
        ]);

        $this->forge->addKey('id_kota', true);
        $this->forge->createTable('kota');
    }

    public function down()
    {
        //
        $this->forge->dropTable('kota');
    }
}
