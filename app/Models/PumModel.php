<?php

namespace App\Models;

use CodeIgniter\Model;

class PumModel extends Model
{
    protected $table = "pum";
    protected $primaryKey = "id_pum";
    protected $allowedFields = ['id_pjum', 'kolom', 'pum', 'uang_kembali', 'id_biaya', 'id_transaksi', 'id_valas', 'kode_valas', 'simbol', 'created_by', 'edited_at', 'edited_by'];

    // SELECT * from biaya where baris IN(6) && kolom IN(7);

    public function alldata(){
        $builder = $this->table($this->table);

        $builder->orderBy('id_biaya', 'asc');
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function alldataId($id_transaksi, $id_pjum){
        $builder = $this->table($this->table);

        $builder->Where('id_transaksi', $id_transaksi);
        $builder->Where('id_pjum', $id_pjum);

        $builder->orderBy('id_pum', 'asc');
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function alldataIdt($id_transaksi){
        $builder = $this->table($this->table);

        $builder->Where('id_transaksi', $id_transaksi);

        $builder->orderBy('id_pum', 'asc');
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function pum($id_transaksi)
    {
        $builder = $this->table /*builder*/ ($this->table /*Model*/);
        $builder->where('id_transaksi', $id_transaksi);
        $builder->orderBy('kolom', 'asc');
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function valas($id_transaksi){
        $builder = $this->table($this->table);

        $builder->select('id_pjum, id_valas, kode_valas');

        $builder->Where('id_transaksi', $id_transaksi);
        
        $builder->groupBy(['id_pjum']);
        $builder->orderBy('id_pjum', 'asc');
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function add($data){
        $builder = $this->table($this->table);

        $builder->distinct();

        if(isset($data['id_biaya'])) { //kalo udh punya post_id brarti masuk dalam proses edit
            $aksi = $builder->save($data);
            $id= $data['id_biaya'];
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

    public function cekpum($id_transaksi, $kolom){
        $builder = $this->table($this->table);

        $builder->Where('id_transaksi', $id_transaksi);
        $builder->Where('kolom', $kolom);

        $query = $builder->get();
        return $query->getRowArray();
    }

    public function sisa($id_transaksi, $id_pjum){
        $builder = $this->table($this->table);
        $builder->select('pum, uang_kembali, simbol, id_valas, id_pum, id_pjum');
        $builder->where('id_transaksi', $id_transaksi);
        $builder->where('id_pjum', $id_pjum);
        $builder->groupBy(['id_valas', 'id_transaksi']);
        $builder->orderBy('id_pum', 'asc');
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function getData($parameter)
    {
        $builder = $this->table /*builder*/ ($this->table /*Model*/);
        $builder->where('id_biaya=', $parameter);
        $builder->where('id_transaksi=', $parameter);
        $builder->orwhere('jenis_biaya=', $parameter);
        $builder->orwhere('kode_valas=', $parameter);
        $query = $builder->get();
        return $query->getRowArray();
    }

    public function getDataValas($id_transaksi)
    {
        $builder = $this->table /*builder*/ ($this->table /*Model*/);
        $builder->where('id_transaksi', $id_transaksi);
        $query = $builder->get();
        return $query->getResultArray();
    }

    function deletePum($id_transaksi){
        $builder= $this->table($this->table);
        $builder -> where('id_transaksi', $id_transaksi);
        
        if($builder->delete()){
            return true;
        } else {
            return false;
        }
    }
}   