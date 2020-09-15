<?php

use \model\IdentityAndAccess\application\DepartmentService;
use \model\IdentityAndAccess\infrastructure\DepartmentRepository;
use \model\IdentityAndAccess\infrastructure\PersonnelRepository;
use \model\IdentityAndAccess\infrastructure\ImageDirectAccessLinkProvider;
use \model\IdentityAndAccess\infrastructure\ImagePathProvider;

use \system\library\JWToken;
use \model\common\QueryObject;

use PHPUnit\Framework\TestCase;


class DepartmentServiceDbTest extends TestCase{

	private static \DB $db;
	private static $jwToken;

 	public static function setUpBeforeClass() : void {
    	
    	global $framework;

    	self::$jwToken = $framework->get('jwt');
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
       //self::$db->command("DELETE FROM submodule");

	}

	public function test_If_assignDepartment_Creates_A_New_Department(){

		$image_path_provider = new ImagePathProvider('/Users/selimatasever/Desktop/GitHub/erp-api/v1/tests/yol');

		$image_direct_access_link_provider = new ImageDirectAccessLinkProvider(
			self::$jwToken, 'www.example.com', $image_path_provider, '0.0.0.0 8.8.4.4'
		);

		$department_repository = new DepartmentRepository(self::$db, null);
		$personnel_repository = new PersonnelRepository(self::$db, null);

		$department_service = new DepartmentService(
			$department_repository, $personnel_repository, $image_direct_access_link_provider
		);

		self::$db->insert('department', array(
			'id' => 1,
			'name' => 'chief',
			'parent_id' => null, 
			'director' => 1,
			'director_allowed_parent_depth' => 1,
			'director_allowed_child_depth' => 1,
			'member_allowed_parent_depth' => 0,
			'member_allowed_child_depth' => 0,
			'order' => 1
		));

		self::$db->insert('department', array(
			'id' => 2,
			'name' => 'assistant manager',
			'parent_id' => 1, 
			'director' => 2,
			'director_allowed_parent_depth' => 1,
			'director_allowed_child_depth' => 1,
			'member_allowed_parent_depth' => 0,
			'member_allowed_child_depth' => 0,
			'order' => 2
		));

		$department_service->assignDepartment(1,1);

		$db_department_id = self::$db->query("SELECT * FROM department WHERE id = 1")->row['id'];
		$this->assertEquals($db_department_id, 1);

		$db_department_name = self::$db->query("SELECT * FROM department WHERE id = 2")->row['name'];
		$this->assertEquals($db_department_name, 'assistant manager');

	}

	public function test_If_getDepartment_Returns_Department_Dto_From_Db(){

		$image_path_provider = new ImagePathProvider('/Users/selimatasever/Desktop/GitHub/erp-api/v1/tests/yol');

		$image_direct_access_link_provider = new ImageDirectAccessLinkProvider(
			self::$jwToken, 'www.example.com', $image_path_provider, '0.0.0.0 8.8.4.4'
		);

		$department_repository = new DepartmentRepository(self::$db, null);
		$personnel_repository = new PersonnelRepository(self::$db, null);

		$department_service = new DepartmentService(
			$department_repository, $personnel_repository, $image_direct_access_link_provider
		);

		$department_dto = $department_service->getDepartment(1);
		$this->assertNotEmpty($department_dto);
	}

	public function test_If_getDepartments_Returns_Departments(){

		$image_path_provider = new ImagePathProvider('/Users/selimatasever/Desktop/GitHub/erp-api/v1/tests/yol');

		$image_direct_access_link_provider = new ImageDirectAccessLinkProvider(
			self::$jwToken, 'www.example.com', $image_path_provider, '0.0.0.0 8.8.4.4'
		);

		$department_repository = new DepartmentRepository(self::$db, null);
		$personnel_repository = new PersonnelRepository(self::$db, null);

		$department_service = new DepartmentService(
			$department_repository, $personnel_repository, $image_direct_access_link_provider
		);

		$departments_query_dto = $department_service->getDepartments(new QueryObject());

		$departments_as_arr = $departments_query_dto->departments();
		$this->assertEquals(2, count($departments_as_arr));
	}

	public function test_If_getPersonnelDepartment_Returns_Personnels_Department_Dto(){

		$image_path_provider = new ImagePathProvider('/Users/selimatasever/Desktop/GitHub/erp-api/v1/tests/yol');

		$image_direct_access_link_provider = new ImageDirectAccessLinkProvider(
			self::$jwToken, 'www.example.com', $image_path_provider, '0.0.0.0 8.8.4.4'
		);

		$department_repository = new DepartmentRepository(self::$db, null);
		$personnel_repository = new PersonnelRepository(self::$db, null);

		$department_service = new DepartmentService(
			$department_repository, $personnel_repository, $image_direct_access_link_provider
		);

		$returned_department_dto = $department_service->getPersonnelDepartment(1);
		$this->assertNotEmpty($returned_department_dto);
	}

	public function test_If_Personnel_Can_Access_Lower_Department_Personnel(){

		$image_path_provider = new ImagePathProvider('/Users/selimatasever/Desktop/GitHub/erp-api/v1/tests/yol');

		$image_direct_access_link_provider = new ImageDirectAccessLinkProvider(
			self::$jwToken, 'www.example.com', $image_path_provider, '0.0.0.0 8.8.4.4'
		);

		$department_repository = new DepartmentRepository(self::$db, null);
		$personnel_repository = new PersonnelRepository(self::$db, null);

		$department_service = new DepartmentService(
			$department_repository, $personnel_repository, $image_direct_access_link_provider
		);

		self::$db->insert('personnel', array(
			'id' => 2,
			'role_id' => 2,
			'department_id' => 2,
			'image_id' => null,
			'firstname' => 'arnie',
			'lastname' => 'schwarzenegger',
			'tcno' => 14140042140,
			'gender' => 'male',
			'phone' => '0001241891204',
			'email' => 'schwarzenegger@kant.ist',
			'is_active' => 1,
			'date_added' => (new DateTime())->format('Y-m-d H:i:s'),
			'last_modification' => (new DateTime())->format('Y-m-d H:i:s')
		));

		self::$db->insert('personnel', array(
			'id' => 3,
			'role_id' => 3,
			'department_id' => 2,
			'image_id' => null,
			'firstname' => 'JCV',
			'lastname' => 'damme',
			'tcno' => 44140042140,
			'gender' => 'male',
			'phone' => '6661241891204',
			'email' => 'damme@kant.ist',
			'is_active' => 1,
			'date_added' => (new DateTime())->format('Y-m-d H:i:s'),
			'last_modification' => (new DateTime())->format('Y-m-d H:i:s')
		));

		$confirm_can_access = $department_service->personnelCanAccessPersonnel(1,2);
		$this->assertTrue($confirm_can_access);
	}

	public function test_If_Personnel_Can_Access_Upper_Department_Personnel(){

		$image_path_provider = new ImagePathProvider('/Users/selimatasever/Desktop/GitHub/erp-api/v1/tests/yol');

		$image_direct_access_link_provider = new ImageDirectAccessLinkProvider(
			self::$jwToken, 'www.example.com', $image_path_provider, '0.0.0.0 8.8.4.4'
		);

		$department_repository = new DepartmentRepository(self::$db, null);
		$personnel_repository = new PersonnelRepository(self::$db, null);

		$department_service = new DepartmentService(
			$department_repository, $personnel_repository, $image_direct_access_link_provider
		);

		$confirm_can_access = $department_service->personnelCanAccessPersonnel(2,1);
		$this->assertTrue($confirm_can_access);
	}

	public function test_If_getDepartmentPersonnels_Returns_Dto_Array_Of_Departments_Personnels(){

		$image_path_provider = new ImagePathProvider('/Users/selimatasever/Desktop/GitHub/erp-api/v1/tests/yol');

		$image_direct_access_link_provider = new ImageDirectAccessLinkProvider(
			self::$jwToken, 'www.example.com', $image_path_provider, '0.0.0.0 8.8.4.4'
		);

		$department_repository = new DepartmentRepository(self::$db, null);
		$personnel_repository = new PersonnelRepository(self::$db, null);

		$department_service = new DepartmentService(
			$department_repository, $personnel_repository, $image_direct_access_link_provider
		);

		$personnels_dto_arr = $department_service->getDepartmentPersonnels(2);
		$this->assertEquals(2, count($personnels_dto_arr));

	}

	public function test_If_batchUpdateDepartment_Adds_New_Personnels_To_Existing_Department_And_Updates_Department(){

		$image_path_provider = new ImagePathProvider('/Users/selimatasever/Desktop/GitHub/erp-api/v1/tests/yol');

		$image_direct_access_link_provider = new ImageDirectAccessLinkProvider(
			self::$jwToken, 'www.example.com', $image_path_provider, '0.0.0.0 8.8.4.4'
		);

		$department_repository = new DepartmentRepository(self::$db, null);
		$personnel_repository = new PersonnelRepository(self::$db, null);

		$department_service = new DepartmentService(
			$department_repository, $personnel_repository, $image_direct_access_link_provider
		);

		self::$db->insert('personnel', array(
			'id' => 4,
			'role_id' => 4,
			'department_id' => null,
			'image_id' => null,
			'firstname' => 'joe',
			'lastname' => 'doe',
			'tcno' => 11140042140,
			'gender' => 'male',
			'phone' => '2201241891200',
			'email' => 'joedoe@kant.ist',
			'is_active' => 1,
			'date_added' => (new DateTime())->format('Y-m-d H:i:s'),
			'last_modification' => (new DateTime())->format('Y-m-d H:i:s')
		));

		self::$db->insert('personnel', array(
			'id' => 5,
			'role_id' => 5,
			'department_id' => null,
			'image_id' => null,
			'firstname' => 'zoe',
			'lastname' => 'doe',
			'tcno' => 11110042144,
			'gender' => 'female',
			'phone' => '2200241891202',
			'email' => 'zoedoe@kant.ist',
			'is_active' => 1,
			'date_added' => (new DateTime())->format('Y-m-d H:i:s'),
			'last_modification' => (new DateTime())->format('Y-m-d H:i:s')
		));

		$arr_personnel_id = array(4,5);
		$department_service->batchUpdateDepartment(2, 'HR', $arr_personnel_id, 2);

		$joe_tcno = self::$db->query("SELECT * FROM personnel WHERE id=4")->row['tcno'];
		$this->assertEquals(11140042140, $joe_tcno);

		$zoe_phone = self::$db->query("SELECT * FROM personnel WHERE id=5")->row['phone'];
		$this->assertEquals('2200241891202', $zoe_phone);
	}



}

?>