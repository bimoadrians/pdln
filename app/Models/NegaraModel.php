<?php

namespace App\Models;

use CodeIgniter\Model;

class NegaraModel extends Model
{
    protected $table = "negara";
    protected $primaryKey = "id_negara";
    protected $allowedFields =['negara_tujuan'];

    public function getData($parameter)
    {
        $builder = $this->table /*builder*/ ($this->table /*Model*/);
        $builder->where('negara_tujuan=', $parameter);
        $query = $builder->get();
        return $query->getRowArray();
    }

    public function getDataAll()
    {
        $builder = $this->table /*builder*/ ($this->table /*Model*/);

        $builder->select('negara_tujuan');

        $query = $builder->get();
        return $query->getResultArray();
    }
}   