<?php

Class invoiceModel Extends baseModel {
	protected $table = "invoice";

	public function getAllInvoice($data = null,$join = null) 
    {
        return $this->fetchAll($this->table,$data,$join);
    }

    public function createInvoice($data) 
    {    
        /*$data = array(
        	'Invoicename' => $data['Invoicename'],
        	'password' => $data['password'],
        	'create_time' => $data['create_time'],
        	'role' => $data['role'],
        	);*/
        return $this->insert($this->table,$data);
    }
    public function updateInvoice($data,$id) 
    {    
        if ($this->getInvoiceByWhere($id)) {
        	/*$data = array(
	        	'Invoicename' => $data['Invoicename'],
	        	'password' => $data['password'],
	        	'create_time' => $data['create_time'],
	        	'role' => $data['role'],
	        	);*/
	        return $this->update($this->table,$data,$id);
        }
        
    }
    public function deleteInvoice($id){
    	if ($this->getInvoice($id)) {
    		return $this->delete($this->table,array('invoice_id'=>$id));
    	}
    }
    public function getInvoice($id){
    	return $this->getByID($this->table,$id);
    }
    public function getInvoiceByWhere($where){
        return $this->getByWhere($this->table,$where);
    }
    public function getAllInvoiceByWhere($id){
        return $this->query('SELECT * FROM invoice WHERE invoice_id != '.$id);
    }
    public function getLastInvoice(){
        return $this->getLast($this->table);
    }
    public function checkInvoice($id){
        return $this->query('SELECT * FROM invoice WHERE invoice_id != '.$id);
    }
    public function queryInvoice($sql){
        return $this->query($sql);
    }
}
?>