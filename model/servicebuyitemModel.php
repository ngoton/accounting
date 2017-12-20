<?php

Class servicebuyitemModel Extends baseModel {
	protected $table = "service_buy_item";

	public function getAllService($data = null,$join = null) 
    {
        return $this->fetchAll($this->table,$data,$join);
    }

    public function createService($data) 
    {    
        /*$data = array(
        	'Servicename' => $data['Servicename'],
        	'password' => $data['password'],
        	'create_time' => $data['create_time'],
        	'role' => $data['role'],
        	);*/
        return $this->insert($this->table,$data);
    }
    public function updateService($data,$id) 
    {    
        if ($this->getServiceByWhere($id)) {
        	/*$data = array(
	        	'Servicename' => $data['Servicename'],
	        	'password' => $data['password'],
	        	'create_time' => $data['create_time'],
	        	'role' => $data['role'],
	        	);*/
	        return $this->update($this->table,$data,$id);
        }
        
    }
    public function deleteService($id){
    	if ($this->getService($id)) {
    		return $this->delete($this->table,array('service_buy_item_id'=>$id));
    	}
    }
    public function getService($id){
    	return $this->getByID($this->table,$id);
    }
    public function getServiceByWhere($where){
        return $this->getByWhere($this->table,$where);
    }
    public function getAllServiceByWhere($id){
        return $this->query('SELECT * FROM service_buy_item WHERE service_buy_item_id != '.$id);
    }
    public function getLastService(){
        return $this->getLast($this->table);
    }
    public function checkService($id){
        return $this->query('SELECT * FROM service_buy_item WHERE service_buy_item_id != '.$id);
    }
    public function queryService($sql){
        return $this->query($sql);
    }
}
?>