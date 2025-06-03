<?php

namespace App\Models;

use CodeIgniter\Model;

class ValasModel extends Model
{
    protected $table = "valas";
    protected $primaryKey = "id_valas";
    protected $allowedFields =['kode_valas', 'nama_valas', 'nama_negara', 'simbol'];

    public function insertValas($data)
    {
        helper("global_fungsi_helper");
        $builder = $this->table($this->table);

        if(isset($data['id_transaksi'])) { //kalo udh punya post_id brarti masuk dalam proses edit
            $aksi = $builder->save($data);
            $id = $data['id_transaksi'];
        } else {
            $aksi = $builder->save($data);
            $id= $builder->getInsertID();
        }

        if($aksi) {
            return $id;//kalo dalam proses penambahan atau edit sukses kita keluarkan id nya
        } else {
            return false; //kalo gagal brarti false
        }
    }

    public function getData($parameter)
    {
        $builder = $this->table /*builder*/ ($this->table /*Model*/);
        $builder->where('id_valas=', $parameter);
        $query = $builder->get();
        return $query->getRowArray();
    }

    public function getDataAll()
    {
        $builder = $this->table /*builder*/ ($this->table /*Model*/);
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function simbol(){
        $builder = $this->table($this->table);
        $builder->select('simbol');
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function getDataKode($kode_valas)
    {
        $builder = $this->table /*builder*/ ($this->table /*Model*/);

        $builder->where('kode_valas', $kode_valas);

        $query = $builder->get();
        return $query->getRowArray();
    }

    public function getDataValas($kode_valas)
    {
        $builder = $this->table /*builder*/ ($this->table /*Model*/);

        $builder->where('kode_valas', $kode_valas);

        $query = $builder->get();
        return $query->getResultArray();
    }
}   