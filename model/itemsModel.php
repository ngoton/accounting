<?php

Class itemsModel Extends baseModel {
	protected $table = "items";

	public function getAllItems($data = null,$join = null) 
    {
        return $this->fetchAll($this->table,$data,$join);
    }

    public function createItems($data) 
    {    
        /*$data = array(
        	'Itemname' => $data['Itemname'],
        	'password' => $data['password'],
        	'create_time' => $data['create_time'],
        	'role' => $data['role'],
        	);*/
        return $this->insert($this->table,$data);
    }
    public function updateItems($data,$id) 
    {    
        if ($this->getItemsByWhere($id)) {
        	/*$data = array(
	        	'Itemname' => $data['Itemname'],
	        	'password' => $data['password'],
	        	'create_time' => $data['create_time'],
	        	'role' => $data['role'],
	        	);*/
	        return $this->update($this->table,$data,$id);
        }
        
    }
    public function deleteItems($id){
    	if ($this->getItems($id)) {
    		return $this->delete($this->table,array('items_id'=>$id));
    	}
    }
    public function getItems($id){
    	return $this->getByID($this->table,$id);
    }
    public function getItemsByWhere($where){
        return $this->getByWhere($this->table,$where);
    }
    public function getAllItemsByWhere($id){
        return $this->query('SELECT * FROM items WHERE items_id != '.$id);
    }
    public function getLastItems(){
        return $this->getLast($this->table);
    }
    public function checkItems($id){
        return $this->query('SELECT * FROM items WHERE items_id != '.$id);
    }
    public function queryItems($sql){
        return $this->query($sql);
    }
}
?>