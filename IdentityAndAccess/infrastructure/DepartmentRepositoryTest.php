<?php

use \model\IdentityAndAccess\infrastructure\DepartmentRepository;
use \model\IdentityAndAccess\domain\model\Department;
use \model\IdentityAndAccess\domain\model\DepartmentId;
use \model\IdentityAndAccess\domain\model\PersonnelId;
use \model\common\QueryObject;

use PHPUnit\Framework\TestCase;

class DepartmentRepositoryTest extends TestCase{

	private static \DB $db;
    public static function setUpBeforeClass() : void {
    	
    	global $framework;
        $config = $framework->get('config');

        self::$db = new \DB(
            $config->get('db_iaa_type'),
            $config->get('db_iaa_hostname'),
            $config->get('db_iaa_username'),
            $config->get('db_iaa_password'),
            $config->get('db_iaa_database'),
            $config->get('db_iaa_port')
        );

        self::$db->command("DELETE FROM department");

    }

    public function test_If_save_Method_Updates_Existing_Department(){

    	self::$db->insert('department', array(
    		'id' => 1,
    		'name' => 'Manager',
    		'parent_id' => null,
    		'director' => null,
    		'director_allowed_parent_depth' => 1,
    		'director_allowed_child_depth' => 1,
    		'member_allowed_parent_depth' => 0,
    		'member_allowed_child_depth' => 0,
    		'order' => 1

    	));

    	self::$db->insert('department', array(
    		'id' => 2,
    		'name' => 'Assistant Manager',
    		'parent_id' => 1,
    		'director' => null,
    		'director_allowed_parent_depth' => 1,
    		'director_allowed_child_depth' => 1,
    		'member_allowed_parent_depth' => 1,
    		'member_allowed_child_depth' => 1,
    		'order' => 2
    	));

    	$department_repository = new DepartmentRepository(self::$db, null);

    	$department_repository ->save(new Department(
    		new DepartmentId(1),
    		'COO',
    		null,
    		null,
    		1,
    		1,
    		1,
    		1,
    		1
    	));

    	$new_department_name = self::$db->query("SELECT * FROM department WHERE id = 1")->row['name'];
    	$this->assertEquals('COO', $new_department_name);
    }

    public function test_If_findDepartment_Returns_Department_Dbo(){

    	$department_repository = new DepartmentRepository(self::$db, null);

    	$department_dbo = $department_repository->findDepartment(new DepartmentId(2));

    	$this->assertIsObject($department_dbo);

    	$db_name = self::$db->query("SELECT * FROM department WHERE id=2")->row['name'];

    	$this->assertEquals($department_dbo->name(), $db_name);
    }

    public function test_If_fetchChildDepartments_Returns_An_Array_Of_Child_Departments(){

    	self::$db->insert('department', array(
    		'id' => 3,
    		'name' => 'Secretary',
    		'parent_id' => 1,
    		'director' => null,
    		'director_allowed_parent_depth' => 1,
    		'director_allowed_child_depth' => 1,
    		'member_allowed_parent_depth' => 1,
    		'member_allowed_child_depth' => 1,
    		'order' => 3

    	));

    	$department_repository = new DepartmentRepository(self::$db, null);

    	$returned_child_departments = $department_repository->fetchChildDepartments(new DepartmentId(1));

    	$this->assertIsArray($returned_child_departments);
    	$this->assertEquals(count($returned_child_departments), 2);
    }

    public function test_If_fetchAll_Method_Returns_All_Departments_On_Db(){

    	$department_repository = new DepartmentRepository(self::$db, null);

    	$returned_departments = $department_repository->fetchAll(new QueryObject());

		$this->assertIsArray($returned_departments);
		$this->assertEquals(count($returned_departments), 3);   
    }	

    public function test_If_count_Method_Returns_The_Total_Number_Of_Departments(){

    	$department_repository = new DepartmentRepository(self::$db, null);

    	$number_of_departments = $department_repository->count(new QueryObject());
    	
    	$this->assertEquals(3, $number_of_departments);
    }
}

?>