<?php

namespace App\Models;

use CodeIgniter\Model;

class Bm06Model extends Model
{
    protected $table = "bm06";
    protected $primaryKey = "id_bm06";
    protected $allowedFields = ['strorg', 'strorgnm', 'tglsls'];

    public function getData($parameter)
    {
        $builder = $this->table /*builder*/ ($this->table /*Model*/);

        $builder->select('strorgnm, tglsls');
        $builder->where('strorg=', $parameter);
        $query = $builder->get();
        return $query->getRowArray();
    }

    public function getDataAll()
    {
        $builder = $this->table /*builder*/ ($this->table /*Model*/);
        $builder->orderBy('strorgnm', 'asc');
        $query = $builder->findAll();
        return $query;
    }

    public function getDataNik($nik)
    {
        $builder = $this->table /*builder*/ ($this->table /*Model*/);
        $builder -> where('nik', $nik);
        $query = $builder->get();
        return $query->getRowArray();
    }

    public function selectData(){
        $builder = $this->table($this->table);
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

    public function bagian($strorg)
    {
        $builder = $this->table($this->table);

        $builder->select('strorgnm');

        $builder->Where('SUBSTRING(strorg, 1, 4)', $strorg);
        $builder->orderBy('strorg', 'asc');

        $query = $builder->get();
        return $query->getResultArray();
    }
}
