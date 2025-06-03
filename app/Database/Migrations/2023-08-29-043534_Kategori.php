<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Kategori extends Migration
{
    public function up()
    {
        //
        $this->forge->addField([
            'id_kategori'=>[
                'type' => 'INT',
                'constraint' => 10,
                'auto_increment' => true
            ],
            'id_biaya'=>[
                'type' => 'INT',
                'constraint' => 10,
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
                'type' => 'ENUM',
                'constraint' => ['pjum', 'pb'],
                'default' => 'pjum'
            ],
            'kategori'=>[
                'type' => 'VARCHAR',
                'constraint' => 25,
            ],
            'sub_kategori'=>[
                'type' => 'VARCHAR',
                'constraint' => 25,
            ],
            'nama_maskapai'=>[
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'nama_hotel'=>[
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'tanggal' => [
                'type' => 'DATE',
            ],
            'waktu' => [
                'type' => 'TIME',
            ],
            'biaya' => [
                'type' => 'VARCHAR',
                'constraint' => 25,
            ],
            'note' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'jenis_perjalanan' => [
                'type' => 'ENUM',
                'constraint' => ['Keberangkatan', 'Kepulangan'],
                'default' => 'Keberangkatan'
            ],
            'status_hotel' => [
                'type' => 'ENUM',
                'constraint' => ['Hotel Utama', 'Hotel Transit'],
                'default' => 'Hotel Utama'
            ],
            'created_at timestamp default now()'
        ]);

        $this->forge->addKey('id_kategori', true);
        $this->forge->createTable('kategori');
    }

    public function down()
    {
        //
        $this->forge->dropTable('kategori');
    }
}
