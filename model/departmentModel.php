<?php

Class departmentModel Extends baseModel {
	protected $table = "department";

	public function getAllDepartment($data = null,$join = null) 
    {
        return $this->fetchAll($this->table,$data,$join);
    }

    public function createDepartment($data) 
    {    
        /*$data = array(
        	'staff_id' => $data['staff_id'],
        	'staff_name' => $data['staff_name'],
        	'staff_birth' => $data['staff_birth'],
        	'staff_gender' => $data['staff_gender'],
            'staff_address' => $data['staff_address'],
            'staff_phone' => $data['staff_phone'],
            'staff_email' => $data['staff_email'],
            'cmnd' => $data['cmnd'],
            'bank' => $data['bank'],
            'account' => $data['account'],
        	);*/

        return $this->insert($this->table,$data);
    }
    public function updateDepartment($data,$where) 
    {    
        if ($this->getDepartmentByWhere($where)) {
        	/*$data = array(
            'staff_id' => $data['staff_id'],
            'staff_name' => $data['staff_name'],
            'staff_birth' => $data['staff_birth'],
            'staff_gender' => $data['staff_gender'],
            'staff_address' => $data['staff_address'],
            'staff_phone' => $data['staff_phone'],
            'staff_email' => $data['staff_email'],
            'cmnd' => $data['cmnd'],
            'bank' => $data['bank'],
            'account' => $data['account'],
            );*/
	        return $this->update($this->table,$data,$where);
        }
        
    }
    public function deleteDepartment($id){
    	if ($this->getDepartment($id)) {
    		return $this->delete($this->table,array('department_id'=>$id));
    	}
    }
    public function getDepartment($id){
        return $this->getByID($this->table,$id);
    }
    public function getDepartmentByWhere($where){
    	return $this->getByWhere($this->table,$where);
    }
    public function getAllDepartmentByWhere($id){
        return $this->query('SELECT * FROM department WHERE department_id != '.$id);
    }
    public function queryDepartment($sql){
        return $this->query($sql);
    }
    public function getLastDepartment(){
        return $this->getLast($this->table);
    }
}
?>