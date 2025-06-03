<?php

namespace App\Models;

use CodeIgniter\Model;

class Am21Model extends Model
{
    protected $table = "am21";
    protected $primaryKey = "id_am21";
    protected $allowedFields = ['nik', 'niknm', 'tglbk', 'strorg'];

    public function getData($parameter)
    {
        $builder = $this->table /*builder*/ ($this->table /*Model*/);
        $builder->Where('nik=', $parameter);
        $query = $builder->get();
        return $query->getRowArray();
    }

    public function getDataAll()
    {
        $builder = $this->table /*builder*/ ($this->table /*Model*/);
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

    public function nik($strorg)
    {
        $builder = $this->table($this->table);

        $builder->Where('SUBSTRING(strorg, 1, 4)', $strorg);
        $builder->orderBy('nik', 'asc');

        $query = $builder->get();
        return $query->getResultArray();
    }
}
