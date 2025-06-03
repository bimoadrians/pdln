<?php

namespace App\Models;

use CodeIgniter\Model;

class BiayaModel extends Model
{
    protected $table = "biaya";
    protected $primaryKey = "id_biaya";
    protected $allowedFields =['id_biaya', 'id_kategori', 'id_pum', 'id_pjum', 'id_pb', 'baris', 'kolom', 'kategori', 'id_transaksi', 'id_valas', 'kode_valas', 'simbol', 'jenis_biaya', 'biaya', 'tanggal', 'created_by', 'edited_at', 'edited_by'];

    // SELECT * from biaya where baris IN(6) && kolom IN(7);
    // SELECT * from kategori where status IN('dibelikan gs');
    
    public function insertBiaya($data)
    {
        helper("global_fungsi_helper");
        $builder = $this->table($this->table);

        if(isset($data['id_transaksi'])) { //kalo udh punya post_id brarti masuk dalam proses edit
            $aksi = $builder->save($data);
            $id = $data['id_transaksi'];
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

    public function alldata(){
        $builder = $this->table($this->table);

        $builder->orderBy('id_biaya', 'asc');
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function alldataId($id_transaksi, $jenis_biaya){
        $builder = $this->table($this->table);

        $builder->Where('id_transaksi', $id_transaksi);
        $builder->Where('jenis_biaya', $jenis_biaya);

        $builder->orderBy('id_biaya', 'asc');
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function alldatapjum($id_transaksi, $jenis_biaya, $id_pjum){
        $builder = $this->table($this->table);

        $builder->Where('id_transaksi', $id_transaksi);
        $builder->Where('jenis_biaya', $jenis_biaya);
        $builder->Where('id_pjum', $id_pjum);
        
        $builder->orderBy('baris', 'asc');
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function alldatapb($id_transaksi, $jenis_biaya, $id_pb){
        $builder = $this->table($this->table);

        $builder->Where('id_transaksi', $id_transaksi);
        $builder->Where('jenis_biaya', $jenis_biaya);
        $builder->Where('id_pb', $id_pb);
        
        $builder->orderBy('baris', 'asc');
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function alldatasupport($id_transaksi){
        $builder = $this->table($this->table);

        $builder->Where('id_transaksi', $id_transaksi);
        $jenis_biaya = ['Support'];
        $builder->whereIn('jenis_biaya', $jenis_biaya);
        
        $builder->orderBy('id_kategori', 'asc');
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function alldatasupportedit($id_transaksi, $id_biaya){
        $builder = $this->table($this->table);

        $builder->Where('id_transaksi', $id_transaksi);
        $builder->Where('id_biaya', $id_biaya);
        $jenis_biaya = ['Support'];
        $builder->whereIn('jenis_biaya', $jenis_biaya);
        
        $builder->orderBy('id_kategori', 'asc');
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function allbiayapjum($id_biaya){
        $builder = $this->table($this->table);

        $builder->select('id_biaya, simbol, biaya, id_valas, kode_valas');

        $builder->Where('id_biaya', $id_biaya);
        
        $builder->orderBy('baris', 'asc');
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function allbiayapb($id_biaya){
        $builder = $this->table($this->table);

        $builder->select('id_biaya, simbol, biaya, id_valas, kode_valas');

        $builder->Where('id_biaya', $id_biaya);
        
        $builder->orderBy('baris', 'asc');
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

    public function cekvaluta($id_transaksi, $baris, $kolom, $jenis_biaya){
        $builder = $this->table($this->table);

        $builder->Where('id_transaksi', $id_transaksi);
        $builder->Where('baris', $baris);
        $builder->Where('kolom', $kolom);
        $builder->Where('jenis_biaya', $jenis_biaya);

        $support = ['Support'];
        $builder->wherenotIn('jenis_biaya', $support);

        $query = $builder->get();
        return $query->getRowArray();
    }

    public function cekvaluta1($id_transaksi, $baris, $kolom){
        $builder = $this->table($this->table);

        $builder->Where('id_transaksi', $id_transaksi);
        $builder->Where('baris', $baris);
        $builder->Where('kolom', $kolom);

        $support = ['Support'];
        $builder->wherenotIn('jenis_biaya', $support);

        $query = $builder->get();
        return $query->getRowArray();
    }

    public function cekvalutapjum($id_transaksi, $baris, $kolom, $jenis_biaya, $id_pjum){
        $builder = $this->table($this->table);

        $builder->Where('id_transaksi', $id_transaksi);
        $builder->Where('baris', $baris);
        $builder->Where('kolom', $kolom);
        $builder->Where('jenis_biaya', $jenis_biaya);
        $builder->Where('id_pjum', $id_pjum);

        $query = $builder->get();
        return $query->getRowArray();
    }

    public function cekvalutapb($id_transaksi, $baris, $kolom, $jenis_biaya, $id_pb){
        $builder = $this->table($this->table);

        $builder->Where('id_transaksi', $id_transaksi);
        $builder->Where('baris', $baris);
        $builder->Where('kolom', $kolom);
        $builder->Where('jenis_biaya', $jenis_biaya);
        $builder->Where('id_pb', $id_pb);

        $query = $builder->get();
        return $query->getRowArray();
    }

    public function cekbiaya($id_biaya){
        $builder = $this->table($this->table);

        $builder->Where('id_biaya', $id_biaya);

        $query = $builder->get();
        return $query->getRowArray();
    }

    public function ceksupport($id_transaksi, $baris){
        $builder = $this->table($this->table);

        $builder->Where('id_transaksi', $id_transaksi);
        $builder->Where('baris', $baris);
        $jenis_biaya = ['Support'];
        $builder->whereIn('jenis_biaya', $jenis_biaya);

        $query = $builder->get();
        return $query->getRowArray();
    }

    public function cek($id_transaksi){
        $builder = $this->table($this->table);

        $builder->Where('id_transaksi', $id_transaksi);

        $query = $builder->get();
        return $query->getRowArray();
    }

    public function cekjenisbiaya($id_transaksi, $baris)
    {
        $builder = $this->table($this->table);

        $builder -> where('id_transaksi', $id_transaksi);
        $builder -> where('baris', $baris);

        $builder->orderBy('id_biaya', 'asc');
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function valuta($id_transaksi){
        $builder = $this->table($this->table);
        $builder->select('id_valas');
        $builder->where('id_transaksi', $id_transaksi);
        $jenis_biaya = ['Support'];
        $builder->wherenotIn('jenis_biaya', $jenis_biaya);
        $builder->groupBy(['id_valas', 'id_transaksi']);
        $builder->orderBy('id_transaksi', 'asc');
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function valas($id_transaksi, $jenis_biaya){
        $builder = $this->table($this->table);
        $builder->select('id_valas');
        $builder->where('id_transaksi', $id_transaksi);
        $builder->where('jenis_biaya', $jenis_biaya);
        $builder->groupBy(['id_valas', 'id_transaksi', 'jenis_biaya']);
        $builder->orderBy('id_biaya', 'asc');
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function valaspjum($id_transaksi, $jenis_biaya, $id_pjum){
        $builder = $this->table($this->table);
        $builder->select('id_valas');
        $builder->where('id_transaksi', $id_transaksi);
        $builder->where('jenis_biaya', $jenis_biaya);
        $builder->where('id_pjum', $id_pjum);
        $builder->groupBy(['id_valas', 'id_transaksi', 'jenis_biaya', 'id_pjum']);
        $builder->orderBy('id_biaya', 'asc');
        $query = $builder->get();
        return $query->getResultArray();
    }
    
    public function valaspb($id_transaksi, $jenis_biaya, $id_pb){
        $builder = $this->table($this->table);
        $builder->select('id_valas');
        $builder->where('id_transaksi', $id_transaksi);
        $builder->where('jenis_biaya', $jenis_biaya);
        $builder->where('id_pb', $id_pb);
        $builder->groupBy(['id_valas', 'id_transaksi', 'jenis_biaya', 'id_pb']);
        $builder->orderBy('id_biaya', 'asc');
        $query = $builder->get();
        return $query->getResultArray();
    }
    
    public function valassupport($id_transaksi){
        $builder = $this->table($this->table);
        $builder->select('id_valas');
        $builder->where('id_transaksi', $id_transaksi);
        $jenis_biaya = ['Support'];
        $builder->whereIn('jenis_biaya', $jenis_biaya);
        $builder->groupBy(['id_valas', 'id_transaksi', 'jenis_biaya']);
        $builder->orderBy('id_biaya', 'asc');
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function support($id_transaksi, $jenis_biaya){
        $builder = $this->table($this->table);
        $builder->select('id_biaya');
        $builder->where('id_transaksi', $id_transaksi);
        $builder->where('jenis_biaya', $jenis_biaya);
        $builder->groupBy(['id_biaya', 'id_transaksi', 'jenis_biaya']);
        $builder->orderBy('id_biaya', 'asc');
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function kode_valas($id_transaksi, $jenis_biaya){
        $builder = $this->table($this->table);
        $builder->select('kode_valas');
        $builder->where('id_transaksi', $id_transaksi);
        $builder->where('jenis_biaya', $jenis_biaya);
        $builder->groupBy(['id_valas', 'id_transaksi', 'jenis_biaya']);
        $builder->orderBy('id_biaya', 'asc');
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function kode_valaspjum($id_transaksi, $jenis_biaya, $id_pjum){
        $builder = $this->table($this->table);
        $builder->select('kode_valas');
        $builder->where('id_transaksi', $id_transaksi);
        $builder->where('jenis_biaya', $jenis_biaya);
        $builder->where('id_pjum', $id_pjum);
        $builder->groupBy(['id_valas', 'id_transaksi', 'jenis_biaya', 'id_pjum']);
        $builder->orderBy('id_biaya', 'asc');
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function kode_valaspb($id_transaksi, $jenis_biaya, $id_pb){
        $builder = $this->table($this->table);
        $builder->select('kode_valas');
        $builder->where('id_transaksi', $id_transaksi);
        $builder->where('jenis_biaya', $jenis_biaya);
        $builder->where('id_pb', $id_pb);
        $builder->groupBy(['id_valas', 'id_transaksi', 'jenis_biaya', 'id_pb']);
        $builder->orderBy('id_biaya', 'asc');
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function kode_valassupport($id_transaksi){
        $builder = $this->table($this->table);
        $builder->select('kode_valas');
        $builder->where('id_transaksi', $id_transaksi);
        $jenis_biaya = ['Support'];
        $builder->whereIn('jenis_biaya', $jenis_biaya);
        $builder->groupBy(['id_valas', 'id_transaksi', 'jenis_biaya']);
        $builder->orderBy('id_biaya', 'asc');
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function tukarUangMasuk($id_transaksi, $jenis_biaya, $id_pjum){
        $builder = $this->table($this->table);
        $builder->select('sum(biaya) as uangmasuk, simbol, id_valas');
        $builder->where('id_transaksi', $id_transaksi);
        $builder->where('jenis_biaya', $jenis_biaya);
        $builder->where('id_pjum', $id_pjum);
        $kategori = ['Tukar Uang Masuk'];
        $builder->whereIn('kategori', $kategori);
        $builder->groupBy(['id_valas', 'id_transaksi', 'jenis_biaya']);
        $builder->orderBy('id_biaya', 'asc');
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function tukarUangKeluar($id_transaksi, $jenis_biaya, $id_pjum){
        $builder = $this->table($this->table);
        $builder->select('sum(biaya) as uangkeluar, simbol, id_valas');
        $builder->where('id_transaksi', $id_transaksi);
        $builder->where('jenis_biaya', $jenis_biaya);
        $builder->where('id_pjum', $id_pjum);
        $kategori = ['Tukar Uang Keluar'];
        $builder->whereIn('kategori', $kategori);
        $builder->groupBy(['id_valas', 'id_transaksi', 'jenis_biaya']);
        $builder->orderBy('id_biaya', 'asc');
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function kembalian($id_transaksi, $jenis_biaya, $id_pjum){
        $builder = $this->table($this->table);
        $builder->select('sum(biaya) as kembalian, simbol, id_valas');
        $builder->where('id_transaksi', $id_transaksi);
        $builder->where('jenis_biaya', $jenis_biaya);
        $builder->where('id_pjum', $id_pjum);
        $kategori = ['Kembalian'];
        $builder->whereIn('kategori', $kategori);
        $builder->groupBy(['id_valas', 'id_transaksi', 'jenis_biaya']);
        $builder->orderBy('id_biaya', 'asc');
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function uangMasuk($id_transaksi, $jenis_biaya, $id_pjum){
        $builder = $this->table($this->table);
        $builder->select('sum(biaya) as uangmasuk, simbol, id_valas');
        $builder->where('id_transaksi', $id_transaksi);
        $builder->where('jenis_biaya', $jenis_biaya);
        $builder->where('id_pjum', $id_pjum);
        $kategori = ['Tukar Uang Masuk', 'Kembalian'];
        $builder->whereIn('kategori', $kategori);
        $builder->groupBy(['id_valas', 'id_transaksi', 'jenis_biaya']);
        $builder->orderBy('id_biaya', 'asc');
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function total($id_transaksi, $jenis_biaya, $id_pjum){
        $builder = $this->table($this->table);
        $builder->select('sum(biaya) as pengeluaran, simbol, id_valas');
        $builder->where('id_transaksi', $id_transaksi);
        $builder->where('jenis_biaya', $jenis_biaya);
        $builder->where('id_pjum', $id_pjum);
        $kategori = ['Tukar Uang Masuk', 'Tukar Uang Keluar', 'Kembalian'];
        $builder->wherenotIn('kategori', $kategori);
        $builder->groupBy(['id_valas', 'id_transaksi', 'jenis_biaya']);
        $builder->orderBy('id_biaya', 'asc');
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function totalpb($id_transaksi, $jenis_biaya, $id_pb){
        $builder = $this->table($this->table);
        $builder->select('sum(biaya) as totalBiaya, simbol, id_valas');
        $builder->where('id_transaksi', $id_transaksi);
        $builder->where('jenis_biaya', $jenis_biaya);
        $builder->where('id_pb', $id_pb);
        $builder->groupBy(['id_valas', 'id_transaksi', 'jenis_biaya', 'id_pb']);
        $builder->orderBy('id_biaya', 'asc');
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function totalsupport($id_transaksi){
        $builder = $this->table($this->table);
        $builder->select('sum(biaya) as totalBiaya, simbol, id_valas');
        $builder->where('id_transaksi', $id_transaksi);
        $jenis_biaya = ['Support'];
        $builder->whereIn('jenis_biaya', $jenis_biaya);
        $builder->groupBy(['id_valas', 'id_transaksi', 'jenis_biaya']);
        $builder->orderBy('id_biaya', 'asc');
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function id_valas($id_transaksi, $jenis_biaya, $id_pjum){
        $builder = $this->table($this->table);
        $builder->select('0 as uangmasuk, id_valas');
        $builder->where('id_transaksi', $id_transaksi);
        $builder->where('jenis_biaya', $jenis_biaya);
        $builder->where('id_pjum', $id_pjum);
        $builder->groupBy(['id_valas', 'id_transaksi', 'jenis_biaya']);
        $builder->orderBy('id_biaya', 'asc');
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

    public function getDataEditBiaya($id_biaya ,$id_transaksi, $jenis_biaya, $id_pjum)
    {
        $builder = $this->table($this->table);

        $builder -> where('id_biaya', $id_biaya);
        $builder -> where('id_transaksi', $id_transaksi);
        $builder -> where('jenis_biaya', $jenis_biaya);
        $builder -> where('id_pjum', $id_pjum);
        
        $query = $builder->get();
        return $query->getRowArray();
    }

    public function getDataAll()
    {
        $builder = $this->table /*builder*/ ($this->table /*Model*/);
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function getDataValas($id_transaksi)
    {
        $builder = $this->table($this->table);

        $builder->select('id_pb, id_valas, kode_valas');

        $builder->Where('id_transaksi', $id_transaksi);
        
        $builder->groupBy(['id_pb']);
        $builder->orderBy('id_pb', 'asc');
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function getBiaya($id_biaya, $jenis_biaya)
    {
        $builder = $this->table /*builder*/ ($this->table /*Model*/);
        $builder->where('id_biaya', $id_biaya);
        $builder->where('jenis_biaya', $jenis_biaya);
        $query = $builder->get();
        return $query->getRowArray();
    }

    public function biaya($id_transaksi)
    {
        $builder = $this->table /*builder*/ ($this->table /*Model*/);
        $builder->where('id_transaksi', $id_transaksi);
        $jenis_biaya = ['Support'];
        $builder->wherenotIn('jenis_biaya', $jenis_biaya);
        $builder->orderBy('baris', 'asc');
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function getDataBiaya($id_transaksi, $jenis_biaya)
    {
        $builder = $this->table /*builder*/ ($this->table /*Model*/);
        $builder->where('id_transaksi', $id_transaksi);
        $builder->where('jenis_biaya', $jenis_biaya);
        $builder->orderBy('id_biaya', 'asc');
        $query = $builder->get();
        return $query->getResultArray();
    }
    
    public function getDataBiayapjum($id_transaksi, $jenis_biaya, $id_pjum)
    {
        $builder = $this->table /*builder*/ ($this->table /*Model*/);
        $builder->where('id_transaksi', $id_transaksi);
        $builder->where('jenis_biaya', $jenis_biaya);
        $builder->where('id_pjum', $id_pjum);
        $builder->orderBy('id_biaya', 'asc');
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function getDataBiayapb($id_transaksi, $jenis_biaya, $id_pb)
    {
        $builder = $this->table /*builder*/ ($this->table /*Model*/);
        $builder->where('id_transaksi', $id_transaksi);
        $builder->where('jenis_biaya', $jenis_biaya);
        $builder->where('id_pb', $id_pb);
        $builder->orderBy('id_biaya', 'asc');
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function biayapesawatpjum($id_transaksi)
    {
        $builder = $this->table /*builder*/ ($this->table /*Model*/);
        $builder->where('id_transaksi', $id_transaksi);
        $kategori = ['Pesawat'];
        $builder->whereIn('kategori', $kategori);
        $jenis_biaya = ['Support', 'PB'];
        $builder->wherenotIn('jenis_biaya', $jenis_biaya);
        $builder->orderBy('id_biaya', 'asc');
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function biayapesawatpb($id_transaksi)
    {
        $builder = $this->table /*builder*/ ($this->table /*Model*/);
        $builder->where('id_transaksi', $id_transaksi);
        $kategori = ['Pesawat'];
        $builder->whereIn('kategori', $kategori);
        $jenis_biaya = ['Support', 'PJUM'];
        $builder->wherenotIn('jenis_biaya', $jenis_biaya);
        $builder->orderBy('id_biaya', 'asc');
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function getDataSemua($id_biaya, $jenis_biaya, $id_valas)
    {
        $builder = $this->table /*builder*/ ($this->table /*Model*/);
        $builder->where('id_biaya', $id_biaya);
        $builder->where('jenis_biaya', $jenis_biaya);
        $builder->where('id_valas', $id_valas);
        $query = $builder->get();
        return $query->getResultArray();
    }

    function deleteBiaya($id_transaksi, $baris, $kolom, $jenis_biaya){
        $builder= $this->table($this->table);
        $builder -> where('id_transaksi', $id_transaksi);
        $builder -> where('baris', $baris);
        $builder -> where('kolom', $kolom);
        $builder -> where('jenis_biaya', $jenis_biaya);
        
        if($builder->delete()){
            return true;
        } else {
            return false;
        }
    }

    function deleteBiaya1($id_biaya){
        $builder= $this->table($this->table);
        $builder -> where('id_biaya', $id_biaya);
        
        if($builder->delete()){
            return true;
        } else {
            return false;
        }
    }
}   