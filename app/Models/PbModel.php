<?php

namespace App\Models;

use CodeIgniter\Model;

class PbModel extends Model
{
    protected $table = "pb";
    protected $primaryKey = "id_pb";
    protected $allowedFields = ['id_transaksi', 'kolom', 'nomor', 'id_valas', 'kode_valas', 'tanggal', 'created_by', 'edited_at', 'edited_by'];

    // SELECT * from biaya where baris IN(6) && kolom IN(7);

    public function nomor($id_transaksi){
        $builder = $this->table($this->table);

        $builder->Where('id_transaksi', $id_transaksi);
        
        $builder->orderBy('id_pb', 'asc');
        $builder->groupBy(['id_pb']);
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function getData($id_transaksi, $id_pb){
        $builder = $this->table($this->table);

        $builder->Where('id_transaksi', $id_transaksi);
        $builder->Where('id_pb', $id_pb);
        
        $builder->orderBy('id_pb', 'asc');
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function getPostId($id_transaksi, $id_pb)
    {
        $builder = $this->table($this->table);

        $builder -> where('id_transaksi', $id_transaksi);
        $builder -> Where('id_pb', $id_pb);

        $builder->orderBy('id_pb', 'asc');
        $query = $builder->get();
        return $query->getRowArray();
    }

    public function ceknomor($id_transaksi, $kolom)
    {
        $builder = $this->table($this->table);

        $builder -> where('id_transaksi', $id_transaksi);
        $builder -> where('kolom', $kolom);

        $builder->orderBy('id_pb', 'asc');
        $query = $builder->get();
        return $query->getRowArray();
    }

    public function cekcreated($id_transaksi, $kolom)
    {
        $builder = $this->table($this->table);

        $builder -> where('id_transaksi', $id_transaksi);
        $builder -> where('kolom', $kolom);
        $builder -> where('created_by', '05080');

        $builder->orderBy('id_pb', 'asc');
        $query = $builder->get();
        return $query->getRowArray();
    }

    public function ceknopb($id_transaksi, $kolom)
    {
        $builder = $this->table($this->table);

        $builder -> where('id_transaksi', $id_transaksi);
        $builder -> where('kolom', $kolom);

        $builder->orderBy('id_pb', 'asc');
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function ceknomorpb($id_transaksi, $nomor)
    {
        $builder = $this->table($this->table);

        $builder -> where('id_transaksi', $id_transaksi);
        $builder -> where('nomor', $nomor);

        $builder->orderBy('id_pb', 'asc');
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function ceknomorpbvalas($id_transaksi, $nomor, $id_valas)
    {
        $builder = $this->table($this->table);

        $builder -> where('id_transaksi', $id_transaksi);
        $builder -> where('nomor', $nomor);
        $builder -> where('id_valas', $id_valas);

        $builder->orderBy('id_pb', 'asc');
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function pb($id_transaksi){
        $builder = $this->table($this->table);
        $builder->select('nomor');
        $builder->where('id_transaksi', $id_transaksi);
        $builder->groupBy(['nomor', 'id_transaksi']);
        $builder->orderBy('id_pb', 'asc');
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function pb1($id_transaksi)
    {
        $builder = $this->table /*builder*/ ($this->table /*Model*/);
        $builder->where('id_transaksi', $id_transaksi);
        $builder->orderBy('kolom', 'asc');
        $query = $builder->get();
        return $query->getResultArray();
    }

    function deletePostId($id_pb){
        $builder= $this->table($this->table);
        $builder->where('id_pb', $id_pb);
        if($builder->delete()){
            return true;
        } else {
            return false;
        }
    }

    function deletePb($id_transaksi){
        $builder= $this->table($this->table);
        $builder -> where('id_transaksi', $id_transaksi);
        
        if($builder->delete()){
            return true;
        } else {
            return false;
        }
    }
}   