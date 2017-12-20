<?php

Class additionalModel Extends baseModel {
	protected $table = "additional";

	public function getAllAdditional($data = null,$join = null) 
    {
        return $this->fetchAll($this->table,$data,$join);
    }

    public function createAdditional($data) 
    {    
        /*$data = array(
        	'Additionalname' => $data['Additionalname'],
        	'password' => $data['password'],
        	'create_time' => $data['create_time'],
        	'role' => $data['role'],
        	);*/
        return $this->insert($this->table,$data);
    }
    public function updateAdditional($data,$id) 
    {    
        if ($this->getAdditionalByWhere($id)) {
        	/*$data = array(
	        	'Additionalname' => $data['Additionalname'],
	        	'password' => $data['password'],
	        	'create_time' => $data['create_time'],
	        	'role' => $data['role'],
	        	);*/
	        return $this->update($this->table,$data,$id);
        }
        
    }
    public function deleteAdditional($id){
    	if ($this->getAdditional($id)) {
    		return $this->delete($this->table,array('additional_id'=>$id));
    	}
    }
    public function getAdditional($id){
    	return $this->getByID($this->table,$id);
    }
    public function getAdditionalByWhere($where){
        return $this->getByWhere($this->table,$where);
    }
    public function getAllAdditionalByWhere($id){
        return $this->query('SELECT * FROM additional WHERE additional_id != '.$id);
    }
    public function getLastAdditional(){
        return $this->getLast($this->table);
    }
    public function checkAdditional($id){
        return $this->query('SELECT * FROM additional WHERE additional_id != '.$id);
    }
    public function queryAdditional($sql){
        return $this->query($sql);
    }
}
?>