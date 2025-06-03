<?php

namespace App\Models;

use CodeIgniter\Model;

class KursModel extends Model
{
    protected $table = "kurs";
    protected $primaryKey = "id_kurs";
    protected $allowedFields = ['id_transaksi', 'id_pjum', 'id_pb', 'id_valas', 'kode_valas', 'tanggal', 'kurs'];

    // SELECT * from biaya where baris IN(6) && kolom IN(7);

    public function alldata(){
        $builder = $this->table($this->table);

        $builder->orderBy('id_kurs', 'asc');
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function getkurspjum($id_pjum)
    {
        $builder = $this->table /*builder*/ ($this->table /*Model*/);

        $builder->select('id_pjum, id_kurs, kurs, tanggal');

        $builder->where('id_pjum', $id_pjum);
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function getkurspb($id_pb)
    {
        $builder = $this->table /*builder*/ ($this->table /*Model*/);

        $builder->select('id_pb, id_kurs, kurs, tanggal');

        $builder->where('id_pb', $id_pb);
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function cekkurspjum($id_pjum, $tanggal)
    {
        $builder = $this->table /*builder*/ ($this->table /*Model*/);
        $builder->where('id_pjum', $id_pjum);
        $builder->where('tanggal', $tanggal);
        $builder->where('kode_valas', $kode_valas);
        $query = $builder->get();
        return $query->getResultArray();
    }

    function deleteKurs($id_kurs){
        $builder= $this->table($this->table);
        $builder -> where('id_kurs', $id_kurs);
        
        if($builder->delete()){
            return true;
        } else {
            return false;
        }
    }

    function deletekurspjum($id_pjum){
        $builder= $this->table($this->table);
        $builder->where('id_pjum', $id_pjum);
        if($builder->delete()){
            return true;
        } else {
            return false;
        }
    }

    function deletekurspb($id_pb){
        $builder= $this->table($this->table);
        $builder->where('id_pb', $id_pb);
        if($builder->delete()){
            return true;
        } else {
            return false;
        }
    }
}   