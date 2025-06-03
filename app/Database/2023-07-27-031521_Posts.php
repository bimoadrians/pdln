<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Posts extends Migration
{
    public function up()
    {
        //
        $this->forge->addField([
            'post_id'=>[
                'type' => 'INT',
                'constraint' => 25,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'id_transaksi'=>[
                'type' => 'INT',
                'constraint' => 25,
            ],
            'nik'=>[
                'type' => 'CHAR',
                'constraint' => 25,
            ],
            'username' => [
                'type' => 'VARCHAR',
                'constraint' => 25,
            ],
            'inisial' => [
                'type' => 'VARCHAR',
                'constraint' => 25,
            ],
            //Judul
            'post_title' => [
                'type' => 'VARCHAR',
                'constraint' => 255
            ],
            // Judulnya Hello Word : Id 1
            // landingpage/detail/id/1
            // landingpage/detail/id/hello-word | hello-word ini ada seo dibuat uniqe
            'post_title_seo' => [
                'type' => 'VARCHAR',
                'constraint' => 255
            ],
            'post_status' => [
                'type' => 'ENUM',
                'constraint' => ['active', 'inactive'],
                'default' => 'active'
            ],
            'post_type' => [
                'type' => 'ENUM',//enum itu pilihan brarti pilihannya article atau page
                'constraint' => ['article', 'page'],
                'default' => 'article'
            ],
            'post_thumbnail' => [
                'type' => 'VARCHAR',
                'constraint' => 255
            ],
            'post_description' => [
                'type' => 'VARCHAR',
                'constraint' => 255
            ],
            //isi kontennya
            'post_content' => [
                'type' => 'LONGTEXT'
            ],
            'post_time timestamp default now()'
        ]);

        $this->forge->addForeignKey('username', 'akun', 'username');//menyambungkan ke username dengan merujuk tabel admin dengan kolom username
        $this->forge->addKey('post_id', true);
        $this->forge->createTable('posts');
    }

    public function down()
    {
        //
        $this->forge->dropTable('posts');
    }
}
