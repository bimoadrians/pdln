<?php

namespace App\Models;

use CodeIgniter\Model;

class PjumModel extends Model
{
    protected $table = "pjum";
    protected $primaryKey = "id_pjum";
    protected $allowedFields = ['id_transaksi', 'kolom', 'id_valas', 'kode_valas', 'nomor', 'tanggal', 'created_by', 'edited_at', 'edited_by'];

    // SELECT * from biaya where baris IN(6) && kolom IN(7);

    public function nomor($id_transaksi){
        $builder = $this->table($this->table);

        $builder->select('id_pjum, nomor, tanggal');

        $builder->Where('id_transaksi', $id_transaksi);
        
        $builder->orderBy('id_pjum', 'asc');
        $builder->groupBy(['id_pjum']);
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function getData($id_transaksi, $id_pjum){
        $builder = $this->table($this->table);

        $builder->Where('id_transaksi', $id_transaksi);
        $builder->Where('id_pjum', $id_pjum);
        
        $builder->orderBy('id_pjum', 'asc');
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function getPostId($id_transaksi, $id_pjum)
    {
        $builder = $this->table($this->table);

        $builder -> where('id_transaksi', $id_transaksi);
        $builder -> Where('id_pjum', $id_pjum);

        $builder->orderBy('id_pjum', 'asc');
        $query = $builder->get();
        return $query->getRowArray();
    }

    public function ceknomor($id_transaksi, $kolom)
    {
        $builder = $this->table($this->table);

        $builder -> where('id_transaksi', $id_transaksi);
        $builder -> where('kolom', $kolom);

        $builder->orderBy('id_pjum', 'asc');
        $query = $builder->get();
        return $query->getRowArray();
    }

    public function cekcreated($id_transaksi, $kolom)
    {
        $builder = $this->table($this->table);

        $builder -> where('id_transaksi', $id_transaksi);
        $builder -> where('kolom', $kolom);
        $builder -> where('created_by', '05080');

        $builder->orderBy('id_pjum', 'asc');
        $query = $builder->get();
        return $query->getRowArray();
    }

    public function ceknopjum($id_transaksi, $kolom)
    {
        $builder = $this->table($this->table);

        $builder -> where('id_transaksi', $id_transaksi);
        $builder -> where('kolom', $kolom);

        $builder->orderBy('id_pjum', 'asc');
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function ceknomorpjum($id_transaksi, $nomor)
    {
        $builder = $this->table($this->table);

        $builder -> where('id_transaksi', $id_transaksi);
        $builder -> where('nomor', $nomor);

        $builder->orderBy('id_pjum', 'asc');
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function ceknomorpjumvalas($id_transaksi, $nomor, $id_valas)
    {
        $builder = $this->table($this->table);

        $builder -> where('id_transaksi', $id_transaksi);
        $builder -> where('nomor', $nomor);
        $builder -> where('id_valas', $id_valas);

        $builder->orderBy('id_pjum', 'asc');
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function pjum($id_transaksi){
        $builder = $this->table($this->table);
        $builder->select('nomor');
        $builder->where('id_transaksi', $id_transaksi);
        $builder->groupBy(['nomor', 'id_transaksi']);
        $builder->orderBy('id_pjum', 'asc');
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function pjum1($id_transaksi)
    {
        $builder = $this->table /*builder*/ ($this->table /*Model*/);
        $builder->where('id_transaksi', $id_transaksi);
        $builder->orderBy('kolom', 'asc');
        $query = $builder->get();
        return $query->getResultArray();
    }

    function deletePostId($id_pjum){
        $builder= $this->table($this->table);
        $builder->where('id_pjum', $id_pjum);
        if($builder->delete()){
            return true;
        } else {
            return false;
        }
    }

    function deletePjum($id_transaksi){
        $builder= $this->table($this->table);
        $builder -> where('id_transaksi', $id_transaksi);
        
        if($builder->delete()){
            return true;
        } else {
            return false;
        }
    }
}   