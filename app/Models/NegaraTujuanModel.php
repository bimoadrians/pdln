<?php

namespace App\Models;

use CodeIgniter\Model;

class NegaraTujuanModel extends Model
{
    protected $table = "negaratujuan";
    protected $primaryKey = "id_negara_tujuan";
    protected $allowedFields = ['id_transaksi', 'negara_tujuan'];

    public function insertNegara($data)
    {
        helper("global_fungsi_helper");
        $builder = $this->table($this->table);

        if(isset($data['id_transaksi'])) { //kalo udh punya post_id brarti masuk dalam proses edit
            $aksi = $builder->updateBatch($data);
            $id = $data['id_transaksi'];
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
        $builder->select('negara_tujuan');
        
        $builder->where('id_transaksi', $id_transaksi);

        $query = $builder->get();
        return $query->getResultArray();
    }

    public function negaratujuan($id_transaksi){
        $builder = $this->table($this->table);
        $builder->select('negara_tujuan');
        $builder->where('id_transaksi', $id_transaksi);
        $builder->groupBy(['negara_tujuan', 'id_transaksi']);
        $builder->orderBy('id_negara_tujuan', 'asc');
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
