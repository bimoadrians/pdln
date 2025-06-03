<?php

namespace App\Models;

use CodeIgniter\Model;

class Am21bModel extends Model
{
    protected $table = "am21b";
    protected $primaryKey = "noemailint";
    protected $allowedFields = ['nik', 'role'];

    public function getData($parameter)
    {
        $builder = $this->table /*builder*/ ($this->table /*Model*/);
        $builder->where('noemailint=', $parameter);
        $builder->orWhere('nik=', $parameter);
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
}
