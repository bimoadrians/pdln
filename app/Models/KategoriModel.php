<?php

namespace App\Models;

use CodeIgniter\Model;

class KategoriModel extends Model
{
    protected $table = "kategori";
    protected $primaryKey = "id_kategori";
    protected $allowedFields = ['id_kategori', 'baris', 'id_transaksi', 'id_pjum', 'id_pb', 'nomor', 'submit', 'jenis_biaya', 'kategori', 'status', 'tanggal', 'note', 'ref', 'jumlah_personil', 'treasury', 'negara_tujuan', 'negara_trading', 'created_by', 'edited_at', 'edited_by'];//data mana yang boleh diisi manual

    // SELECT * from kategori where status IN('dibelikan gs');
    // SELECT * from biaya where baris IN(6) && kolom IN(7);
    // SELECT id_transaksi from kategori where negara_tujuan IN('vietnam') or negara_trading IN('vietnam');
    // SELECT id_kategori from kategori where id_transaksi IN(1) and negara_tujuan IN('singapura', 'thailand') or id_transaksi IN(1) and negara_trading IN('singapura', 'thailand');

    public function getData($parameter)
    {
        $builder = $this->table /*builder*/ ($this->table /*Model*/);
        $builder->Where('id_kategori=', $parameter);
        $query = $builder->get();
        return $query->getRowArray();
    }

    public function insertKategori($data)
    {
        helper("global_fungsi_helper");
        $builder = $this->table($this->table);

        foreach ($data as $key => $value) {
            # code...
            $data[$key] = purify($value);
        }

        if(isset($data['id_kategori'])) { //kalo udh punya post_id brarti masuk dalam proses edit
            $aksi = $builder->save($data);
            $id = $data['id_kategori'];
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

    public function getDataAll()
    {
        $builder = $this->table($this->table);
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function alldata(){
        $builder = $this->table($this->table);
        
        $builder->orderBy('id_kategori', 'asc');
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function alldataId($id_transaksi, $jenis_biaya)
    {
        $builder = $this->table($this->table);

        $builder->select('id_kategori');

        $builder->Where('id_transaksi', $id_transaksi);
        $builder->Where('jenis_biaya', $jenis_biaya);
        
        $builder->orderBy('id_kategori', 'asc');
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function alldataNomor($id_transaksi, $jenis_biaya){
        $builder = $this->table($this->table);

        $builder->Where('id_transaksi', $id_transaksi);
        $builder->Where('jenis_biaya', $jenis_biaya);
        
        $builder->orderBy('baris', 'asc');
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function alldataNomorpjum($id_transaksi, $jenis_biaya, $id_pjum){
        $builder = $this->table($this->table);

        $builder->Where('id_transaksi', $id_transaksi);
        $builder->Where('jenis_biaya', $jenis_biaya);
        $builder->Where('id_pjum', $id_pjum);
        
        $builder->orderBy('baris', 'asc');
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function alldataNomorpb($id_transaksi, $jenis_biaya, $id_pb){
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
        
        $builder->orderBy('baris', 'asc');
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function nomor($id_transaksi, $jenis_biaya){
        $builder = $this->table($this->table);

        $builder->Where('id_transaksi', $id_transaksi);
        $builder->Where('jenis_biaya', $jenis_biaya);
        
        $builder->groupBy('nomor');
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function cek($id_transaksi){
        $builder = $this->table($this->table);

        $builder->Select('id_transaksi', $id_transaksi);
        $builder->Where('id_transaksi', $id_transaksi);

        $query = $builder->get();
        return $query->getRowArray();
    }

    public function add($data){
        $builder = $this->table($this->table);

        if(isset($data['id_kategori'])) { //kalo udh punya post_id brarti masuk dalam proses edit
            $aksi = $builder->save($data);
            $id = $data['id_kategori'];
        } else {
            $aksi = $builder->insert($data);
            $id = $data['id_kategori'];
        }

        if($aksi) {
            return $id;//kalo dalam proses penambahan atau edit sukses kita keluarkan id nya
        } else {
            return false; //kalo gagal brarti false
        }
    }

    public function cekdata($id_transaksi, $baris, $jenis_biaya){
        $builder = $this->table($this->table);

        $builder->Where('id_transaksi', $id_transaksi);
        $builder->Where('baris', $baris);
        $builder->Where('jenis_biaya', $jenis_biaya);

        $support = ['Support'];
        $builder->wherenotIn('jenis_biaya', $support);

        $query = $builder->get();
        return $query->getRowArray();
    }

    public function cekdata1($id_transaksi, $baris){
        $builder = $this->table($this->table);

        $builder->Where('id_transaksi', $id_transaksi);
        $builder->Where('baris', $baris);

        $support = ['Support'];
        $builder->wherenotIn('jenis_biaya', $support);

        $query = $builder->get();
        return $query->getRowArray();
    }

    public function cekdata2($id_transaksi, $jenis_biaya){
        $builder = $this->table($this->table);

        $builder->Where('id_transaksi', $id_transaksi);
        $builder->Where('jenis_biaya', $jenis_biaya);

        $support = ['Support'];
        $builder->wherenotIn('jenis_biaya', $support);

        $query = $builder->get();
        return $query->getRowArray();
    }

    public function cekdatapjum($id_transaksi, $baris, $id_pjum){
        $builder = $this->table($this->table);

        $builder->Where('id_transaksi', $id_transaksi);
        $builder->Where('baris', $baris);
        $builder->Where('id_pjum', $id_pjum);

        $query = $builder->get();
        return $query->getRowArray();
    }

    public function cekdatapb($id_transaksi, $baris, $id_pb){
        $builder = $this->table($this->table);

        $builder->Where('id_transaksi', $id_transaksi);
        $builder->Where('baris', $baris);
        $builder->Where('id_pb', $id_pb);

        $query = $builder->get();
        return $query->getRowArray();
    }

    public function cekpb($id_transaksi, $jenis_biaya){
        $builder = $this->table($this->table);

        $builder->Where('id_transaksi', $id_transaksi);
        $builder->Where('jenis_biaya', $jenis_biaya);

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

    public function cekkategori($id_kategori){
        $builder = $this->table($this->table);

        $builder->Where('id_kategori', $id_kategori);

        $query = $builder->get();
        return $query->getRowArray();
    }

    public function getDataHapus($id_kategori)
    {
        $builder = $this->table($this->table);

        $builder->Where('id_kategori', $id_kategori);

        $query = $builder->get();
        return $query->getRowArray();
    }

    public function getDatakategori($id_biaya)
    {
        $builder = $this->table($this->table);

        $builder->Where('id_biaya', $id_biaya);     

        $query = $builder->get();
        return $query->getResultArray();
    }

    public function getDataId($id_biaya, $jenis_biaya)
    {
        $builder = $this->table($this->table);

        $builder->Where('id_biaya', $id_biaya);
        $builder->Where('jenis_biaya', $jenis_biaya);
        $builder->orderBy('tanggal', 'asc');        

        $query = $builder->get();
        return $query->getResultArray();
    }

    public function getDataIdtransaksi($id_transaksi, $jenis_biaya)
    {
        $builder = $this->table($this->table);

        $builder->Where('id_transaksi', $id_transaksi);
        $builder->Where('jenis_biaya', $jenis_biaya);
        $builder->orderBy('baris', 'asc');

        $query = $builder->get();
        return $query->getResultArray();
    }
    
    public function getDataIdtransaksipjum($id_transaksi, $jenis_biaya, $id_pjum)
    {
        $builder = $this->table($this->table);

        $builder->Where('id_transaksi', $id_transaksi);
        $builder->Where('jenis_biaya', $jenis_biaya);
        $builder->Where('id_pjum', $id_pjum);
        $builder->orderBy('id_kategori', 'asc');

        $query = $builder->get();
        return $query->getResultArray();
    }

    public function getDataIdtransaksipb($id_transaksi, $jenis_biaya, $id_pb)
    {
        $builder = $this->table($this->table);

        $builder->Where('id_transaksi', $id_transaksi);
        $builder->Where('jenis_biaya', $jenis_biaya);
        $builder->Where('id_pb', $id_pb);
        $builder->orderBy('id_kategori', 'asc');

        $query = $builder->get();
        return $query->getResultArray();
    }

    public function kategori($id_transaksi)
    {
        $builder = $this->table /*builder*/ ($this->table /*Model*/);
        $builder->where('id_transaksi', $id_transaksi);
        $jenis_biaya = ['Support'];
        $builder->wherenotIn('jenis_biaya', $jenis_biaya);
        $builder->orderBy('baris', 'asc');
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function kategorisemua($tanggal_awal, $tanggal_akhir, $negara){
        $builder = $this->table($this->table);

        $builder->select('id_transaksi');

        $builder->Where('tanggal >=', $tanggal_awal);
        $builder->Where('tanggal <=', $tanggal_akhir);
        $builder->whereIn('negara_tujuan', $negara);
        $builder->orwhereIn('negara_trading', $negara);

        $jenis_biaya = ['Support'];
        $builder->wherenotIn('jenis_biaya', $jenis_biaya);
        // $builder->Where('submit_pjum', 4);
        // $builder->Where('submit_pb', 4);

        $query = $builder->findAll();
        return $query;
    }

    public function list_kategori($id_transaksi)
    {
        $builder = $this->table /*builder*/ ($this->table /*Model*/);
        $builder->select('kategori');
        $builder->where('id_transaksi', $id_transaksi);
        $kategori = ['Tiket Pesawat', 'Bagasi Pesawat', 'Porter Pesawat', 'Hotel', 'Makan dan Minum', 'Transportasi', 'Laundry', 'Lain-lain', 'Tukar Uang Keluar', 'Tukar Uang Masuk', 'Kembalian'];
        $builder->whereIn('kategori', $kategori);
        $builder->orderBy('baris', 'asc');
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function kategoripesawatpjum($id_transaksi)
    {
        $builder = $this->table /*builder*/ ($this->table /*Model*/);
        $builder->where('id_transaksi', $id_transaksi);
        $kategori = ['Pesawat'];
        $builder->whereIn('kategori', $kategori);
        $builder->where('jenis_biaya', 'pjum');
        $builder->orderBy('id_kategori', 'asc');
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function kategoripesawatpb($id_transaksi)
    {
        $builder = $this->table /*builder*/ ($this->table /*Model*/);
        $builder->where('id_transaksi', $id_transaksi);
        $kategori = ['Pesawat'];
        $builder->whereIn('kategori', $kategori);
        $builder->where('jenis_biaya', 'pb');
        $builder->orderBy('id_kategori', 'asc');
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function indexpesawat($id_transaksi){
        $builder = $this->table($this->table);
        $builder->select('kategori');
        $builder->where('id_transaksi', $id_transaksi);
        $kategori = ['Pesawat'];
        $builder->whereIn('kategori', $kategori);
        $jenis_biaya = ['Support'];
        $builder->wherenotIn('jenis_biaya', $jenis_biaya);
        $builder->orderBy('id_kategori', 'asc');
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function indexpesawatpjum($id_transaksi){
        $builder = $this->table($this->table);
        $builder->select('kategori');
        $builder->where('id_transaksi', $id_transaksi);
        $kategori = ['Pesawat'];
        $builder->whereIn('kategori', $kategori);
        $jenis_biaya = ['Support', 'PB'];
        $builder->wherenotIn('jenis_biaya', $jenis_biaya);
        $builder->orderBy('id_kategori', 'asc');
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function getDataEdit($id_kategori, $id_transaksi, $jenis_biaya, $id_pb)
    {
        $builder = $this->table($this->table);

        $builder -> where('id_kategori', $id_kategori);
        $builder -> where('id_transaksi', $id_transaksi);
        $builder -> where('jenis_biaya', $jenis_biaya);
        $builder -> where('id_pb', $id_pb);
        $query = $builder->get();
        return $query->getRowArray();
    }

    public function getDataTreasury($id_kategori, $id_transaksi, $jenis_biaya, $id_pjum)
    {
        $builder = $this->table($this->table);

        $builder -> where('id_kategori', $id_kategori);
        $builder -> where('id_transaksi', $id_transaksi);
        $builder -> where('jenis_biaya', $jenis_biaya);
        $builder -> where('id_pjum', $id_pjum);
        $query = $builder->get();
        return $query->getRowArray();
    }

    public function getDataEdit1($id_kategori, $_kategori, $jenis_biaya, $kode_valas)
    {
        $builder = $this->table($this->table);

        $builder -> where('id_kategori', $id_kategori);
        $builder -> where('kategori', $_kategori);
        $builder -> where('jenis_biaya', $jenis_biaya);
        $builder -> where('kode_valas', $kode_valas);
        $query = $builder->get();
        return $query->getRowArray();
    }

    function deleteKategori($id_kategori){
        $builder= $this->table($this->table);
        $builder->where('id_kategori', $id_kategori);
        if($builder->delete()){
            return true;
        } else {
            return false;
        }
    }

    function deleteKategori1($id_transaksi, $baris, $jenis_biaya){
        $builder= $this->table($this->table);
        $builder -> where('id_transaksi', $id_transaksi);
        $builder -> where('baris', $baris);
        $builder -> where('jenis_biaya', $jenis_biaya);
        
        if($builder->delete()){
            return true;
        } else {
            return false;
        }
    }

    function deleteKategori2($id_transaksi, $baris, $id_pjum){
        $builder= $this->table($this->table);
        $builder -> where('id_transaksi', $id_transaksi);
        $builder -> where('baris', $baris);
        $builder -> where('id_pjum', $id_pjum);

        if($builder->delete()){
            return true;
        } else {
            return false;
        }
    }

    function deleteKategori3($id_transaksi, $baris, $id_pb){
        $builder= $this->table($this->table);
        $builder -> where('id_transaksi', $id_transaksi);
        $builder -> where('baris', $baris);
        $builder -> where('id_pb', $id_pb);

        if($builder->delete()){
            return true;
        } else {
            return false;
        }
    }
}