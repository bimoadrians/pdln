<?php

namespace App\Models;

use CodeIgniter\Model;

class PersonilModel extends Model
{
    protected $table = "personil";
    protected $primaryKey = "id_personil";
    protected $allowedFields = ['id_transaksi', 'niknm', 'nik', 'strorgnm'];

    public function insertPersonil($data)
    {
        helper("global_fungsi_helper");
        $builder = $this->table($this->table);

        if(isset($data['id_personil'])) { //kalo udh punya post_id brarti masuk dalam proses edit
            $aksi = $builder->updateBatch($data);
            $id = $data['id_personil'];
        } else {
            $aksi = $builder->insertBatch($data);
            $id= $builder->getInsertID();
        }

        if($aksi) {
            return $id;//kalo dalam proses penambahan atau edit sukses kita keluarkan id nya
        } else {
            return false; //kalo gagal brarti false
        }
    }

    public function getDataAll()
    {
        $builder = $this->table($this->table);
        $query = $builder->get();
        return $query->getResultArray();
    }
    
    public function getDataAllId($id_transaksi)
    {
        $builder = $this->table($this->table);
        $builder -> select('niknm, nik, strorgnm');
        $builder -> where('id_transaksi', $id_transaksi);
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function personil($id_transaksi){
        $builder = $this->table($this->table);
        $builder->select('niknm, nik');
        $builder->where('id_transaksi', $id_transaksi);
        $builder->groupBy(['niknm', 'id_transaksi']);
        $builder->orderBy('id_personil', 'asc');
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function updateData($data)
    {
        $builder = $this->table($this->table);
        if($builder->save($data)) {
            return true;
        } else {
            return false;
        }
    }
}
