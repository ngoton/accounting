<?php

Class assetModel Extends baseModel {
	protected $table = "asset";

	public function getAllAsset($data = null,$join = null) 
    {
        return $this->fetchAll($this->table,$data,$join);
    }

    public function createAsset($data) 
    {    
        /*$data = array(
        	'Assetname' => $data['Assetname'],
        	'password' => $data['password'],
        	'create_time' => $data['create_time'],
        	'role' => $data['role'],
        	);*/
        return $this->insert($this->table,$data);
    }
    public function updateAsset($data,$id) 
    {    
        if ($this->getAssetByWhere($id)) {
        	/*$data = array(
	        	'Assetname' => $data['Assetname'],
	        	'password' => $data['password'],
	        	'create_time' => $data['create_time'],
	        	'role' => $data['role'],
	        	);*/
	        return $this->update($this->table,$data,$id);
        }
        
    }
    public function deleteAsset($id){
    	if ($this->getAsset($id)) {
    		return $this->delete($this->table,array('asset_id'=>$id));
    	}
    }
    public function getAsset($id){
    	return $this->getByID($this->table,$id);
    }
    public function getAssetByWhere($where){
        return $this->getByWhere($this->table,$where);
    }
    public function getAllAssetByWhere($id){
        return $this->query('SELECT * FROM asset WHERE asset_id != '.$id);
    }
    public function getLastAsset(){
        return $this->getLast($this->table);
    }
    public function checkAsset($id){
        return $this->query('SELECT * FROM asset WHERE asset_id != '.$id);
    }
    public function queryAsset($sql){
        return $this->query($sql);
    }
}
?>