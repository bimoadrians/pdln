<?php

namespace App\Models;

use CodeIgniter\Model;

class TransaksiModel extends Model
{
    protected $table = "transaksi";
    protected $primaryKey = "id_transaksi";
    protected $allowedFields = ['submit_pjum', 'submit_pb', 'kirim_pjum', 'kirim_pb', 'nik', 'role', 'strorg', 'strorgnm', 'jumlah_personil', 'kota', 'tanggal_berangkat', 'tanggal_pulang', 'created_by', 'edited_at', 'edited_by', 'login', 'login_by'];//data mana yang boleh diisi manual

    public function getData($parameter)
    {
        $builder = $this->table /*builder*/ ($this->table /*Model*/);
        $builder->Where('id_transaksi=', $parameter);
        $query = $builder->get();
        return $query->getRowArray();
    }
    
    public function searchId($id = null){
        $builder = $this->table($this->table);

        // katakunci = Hello World (pemisahnya adalah hello world)
        $arr_id = explode(" ", $id);

        // query = *select * from posts where post_type='article' and (post_title like '%Hello%' or post_description like '%hello%'); bentuk kurung adalah group start
        $builder->groupStart();
        for($x=0; $x<count($arr_id); $x++) {
            $builder->orLike('negara_tujuan', $arr_id[$x]);
            $builder->orLike('id_transaksi', $arr_id[$x]);
        }
        $builder->groupEnd();
        $builder->orderBy('created_at', 'asc');//descending
    }

    public function insertTransaksi($data)
    {
        helper("global_fungsi_helper");
        $builder = $this->table($this->table);

        foreach ($data as $key => $value) {
            # code...
            $data[$key] = purify($value);
        }

        if(isset($data['id_transaksi'])) { //kalo udh punya post_id brarti masuk dalam proses edit
            $aksi = $builder->save($data);
            $id = $data['id_transaksi'];
        } else {
            $aksi = $builder->insert($data);
            $id= $builder->getInsertID();
        }

        if($aksi) {
            return $id;//kalo dalam proses penambahan atau edit sukses kita keluarkan id nya
        } else {
            return false; //kalo gagal brarti false
        }
    }

    public function kota($id_transaksi){
        $builder = $this->table($this->table);

        $builder->select('kota');

        $builder->Where('id_transaksi', $id_transaksi);
        
        $builder->orderBy('kota', 'asc');
        $builder->groupBy('kota');
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function tanggal($tanggal_berangkat, $tanggal_pulang, $strorg){
        $builder = $this->table($this->table);

        $builder->select('id_transaksi');

        $builder->Where('tanggal_berangkat >=', $tanggal_berangkat);
        $builder->Where('tanggal_pulang <=', $tanggal_pulang);
        $builder->Where('strorg', $strorg);
        $builder->Where('submit_pjum', 4);
        $builder->Where('submit_pb >=', 3);

        $query = $builder->findAll();
        return $query;
    }

    public function tanggalsemua($tanggal_berangkat, $tanggal_pulang, $strorg){
        $builder = $this->table($this->table);

        $builder->select('id_transaksi');

        $builder->Where('tanggal_berangkat >=', $tanggal_berangkat);
        $builder->Where('tanggal_pulang <=', $tanggal_pulang);
        $builder->Where('SUBSTRING(strorg, 1, 4)', $strorg);
        $builder->Where('submit_pjum', 4);
        $builder->Where('submit_pb', 4);

        $query = $builder->findAll();
        return $query;
    }

    public function listId($id_transaksi, $nik)
    {
        $builder = $this->table($this->table);
        
        $builder->where('id_transaksi', $id_transaksi);
        $builder->where('nik', $nik);

        return $builder;
    }

    public function listIdTransaksi($id_transaksi)
    {
        $builder = $this->table($this->table);
        
        $builder->where('id_transaksi', $id_transaksi);

        return $builder;
    }

    public function listAdminId($strorg)
    {
        $builder = $this->table($this->table);
        $builder->where('SUBSTRING(strorg, 1, 4)', $strorg);

        // SELECT SUBSTRING(strorg, 1, 4) FROM bm06;
        // SELECT * from bm06 where SUBSTRING(strorg, 1, 4);
        // SELECT * from bm06 where SUBSTRING(strorg, 1, 4) IN(1120);

        return $builder;
    }
    
    public function listNikId($strorg, $nik)
    {
        $builder = $this->table($this->table);

        $builder->where('strorg', $strorg);
        $builder->where('nik', $nik);

        return $builder;
    }

    public function listTreasury(){
        $builder = $this->table($this->table);

        $builder->whereIn('submit_pjum', ['1']);
        $builder->whereIn('submit_pb', ['0']);
        $builder->whereIn('kirim_pjum', ['1']);
        $builder->whereIn('kirim_pb', ['0']);

        $builder->orwhereIn('submit_pjum', ['0']);
        $builder->whereIn('submit_pb', ['1']);
        $builder->whereIn('kirim_pjum', ['0']);
        $builder->whereIn('kirim_pb', ['1']);

        $builder->orwhereIn('submit_pjum', ['0']);
        $builder->whereIn('submit_pb', ['1']);
        $builder->whereIn('kirim_pjum', ['1']);
        $builder->whereIn('kirim_pb', ['1']);

        $builder->orwhereIn('submit_pjum', ['1']);
        $builder->whereIn('submit_pb', ['0']);
        $builder->whereIn('kirim_pjum', ['1']);
        $builder->whereIn('kirim_pb', ['1']);

        $builder->orwhereIn('submit_pjum', ['1']);
        $builder->whereIn('submit_pb', ['1']);
        $builder->whereIn('kirim_pjum', ['1']);
        $builder->whereIn('kirim_pb', ['1']);

        $builder->orwhereIn('submit_pjum', ['1']);
        $builder->whereIn('submit_pb', ['2']);
        $builder->whereIn('kirim_pjum', ['1']);
        $builder->whereIn('kirim_pb', ['1']);

        $builder->orwhereIn('submit_pjum', ['1']);
        $builder->whereIn('submit_pb', ['3']);
        $builder->whereIn('kirim_pjum', ['1']);
        $builder->whereIn('kirim_pb', ['1']);

        $builder->orwhereIn('submit_pjum', ['1']);
        $builder->whereIn('submit_pb', ['4']);
        $builder->whereIn('kirim_pjum', ['1']);
        $builder->whereIn('kirim_pb', ['1']);

        $builder->orwhereIn('submit_pjum', ['4']);
        $builder->whereIn('submit_pb', ['1']);
        $builder->whereIn('kirim_pjum', ['1']);
        $builder->whereIn('kirim_pb', ['1']);
        return $builder;
    }

    public function listGS(){
        $builder = $this->table($this->table);
        $builder->orwhere('submit_pjum >', 1);
        $builder->orwhere('submit_pb >', 1);
        $builder->orderBy('id_transaksi', 'asc');
        return $builder;
    }

    public function getTreasury($id_transaksi){
        $builder = $this->table($this->table);
        
        $builder->where('id_transaksi', $id_transaksi);
        $builder->orderBy('id_transaksi', 'asc');
        
        $query = $builder->get();
        return $query->getRowArray();
    }

    public function getTreasuryDashboard($id_transaksi){
        $builder = $this->table($this->table);

        $builder->whereIn('submit_pjum', ['1']);
        $builder->whereIn('submit_pb', ['0']);
        $builder->whereIn('kirim_pjum', ['1']);
        $builder->whereIn('kirim_pb', ['0']);
        $builder->where('id_transaksi', $id_transaksi);

        $builder->orwhereIn('submit_pjum', ['0']);
        $builder->whereIn('submit_pb', ['1']);
        $builder->whereIn('kirim_pjum', ['0']);
        $builder->whereIn('kirim_pb', ['1']);
        $builder->where('id_transaksi', $id_transaksi);

        $builder->orwhereIn('submit_pjum', ['0']);
        $builder->whereIn('submit_pb', ['1']);
        $builder->whereIn('kirim_pjum', ['1']);
        $builder->whereIn('kirim_pb', ['1']);
        $builder->where('id_transaksi', $id_transaksi);

        $builder->orwhereIn('submit_pjum', ['1']);
        $builder->whereIn('submit_pb', ['0']);
        $builder->whereIn('kirim_pjum', ['1']);
        $builder->whereIn('kirim_pb', ['1']);
        $builder->where('id_transaksi', $id_transaksi);

        $builder->orwhereIn('submit_pjum', ['1']);
        $builder->whereIn('submit_pb', ['1']);
        $builder->whereIn('kirim_pjum', ['1']);
        $builder->whereIn('kirim_pb', ['1']);
        $builder->where('id_transaksi', $id_transaksi);

        $builder->orwhereIn('submit_pjum', ['1']);
        $builder->whereIn('submit_pb', ['2']);
        $builder->whereIn('kirim_pjum', ['1']);
        $builder->whereIn('kirim_pb', ['1']);
        $builder->where('id_transaksi', $id_transaksi);

        $builder->orwhereIn('submit_pjum', ['1']);
        $builder->whereIn('submit_pb', ['3']);
        $builder->whereIn('kirim_pjum', ['1']);
        $builder->whereIn('kirim_pb', ['1']);
        $builder->where('id_transaksi', $id_transaksi);

        $builder->orwhereIn('submit_pjum', ['1']);
        $builder->whereIn('submit_pb', ['4']);
        $builder->whereIn('kirim_pjum', ['1']);
        $builder->whereIn('kirim_pb', ['1']);
        $builder->where('id_transaksi', $id_transaksi);

        $builder->orwhereIn('submit_pjum', ['4']);
        $builder->whereIn('submit_pb', ['1']);
        $builder->whereIn('kirim_pjum', ['1']);
        $builder->whereIn('kirim_pb', ['1']);
        $builder->where('id_transaksi', $id_transaksi);
        
        $query = $builder->get();
        return $query->getRowArray();
    }

    public function getTreasuryDash($id_transaksi){
        $builder = $this->table($this->table);
        
        $builder->where('id_transaksi', $id_transaksi);
        $builder->orwhere('submit_pjum', 1);
        $builder->orwhere('submit_pb', 0);
        $builder->orderBy('id_transaksi', 'asc');
        
        $query = $builder->get();
        return $query->getRowArray();
    }

    public function getGS($id_transaksi){
        $builder = $this->table($this->table);
        
        $builder->where('id_transaksi', $id_transaksi);
        $builder->orderBy('id_transaksi', 'asc');
        
        $query = $builder->get();
        return $query->getRowArray();
    }

    public function getGSDashboard($id_transaksi){
        $builder = $this->table($this->table);
        
        $builder->where('id_transaksi', $id_transaksi);
        $builder->orwhere('submit_pjum', 2);
        $builder->orwhere('submit_pb', 2);
        $builder->orderBy('id_transaksi', 'asc');
        
        $query = $builder->get();
        return $query->getRowArray();
    }

    public function getPostId($id_transaksi, $strorg)
    {
        $builder = $this->table($this->table);

        $builder -> where('id_transaksi', $id_transaksi);
        $builder-> where('SUBSTRING(strorg, 1, 4)', $strorg);

        $query = $builder->get();
        return $query->getRowArray();
    }

    public function getAdminId()
    {
        $builder = $this->table($this->table);
        $query = $builder->get();
        return $query->getRowArray();
    }

    public function getNikId($nik)
    {
        $builder = $this->table($this->table);
        $builder -> where('nik', $nik);
        $query = $builder->get();
        return $query->getRowArray();
    }

    public function getId($id_transaksi, $nik)
    {
        $builder = $this->table($this->table);

        $builder -> where('id_transaksi', $id_transaksi);
        $builder -> where('nik', $nik);
        $query = $builder->get();
        return $query->getRowArray();
    }

    public function getDataAll()
    {
        $builder = $this->table($this->table);
        $query = $builder->get();
        return $query->getResultArray();
    }

    function deletePostId($id_transaksi){
        $builder= $this->table($this->table);
        $builder->where('id_transaksi', $id_transaksi);
        if($builder->delete()){
            return true;
        } else {
            return false;
        }
    }
}
