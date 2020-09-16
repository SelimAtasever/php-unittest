<?php

use \model\IdentityAndAccess\application\IdentityService;

use \model\IdentityAndAccess\infrastructure\ImagePathProvider;
use \model\IdentityAndAccess\infrastructure\ImageDirectAccessLinkProvider;

use \model\IdentityAndAccess\infrastructure\PersonnelRepository;
use \model\IdentityAndAccess\infrastructure\DepartmentRepository;
use \model\IdentityAndAccess\infrastructure\RoleRepository;

use \model\common\QueryObject;
use \model\common\ExceptionCollection;

use PHPUnit\Framework\TestCase;


class IdentityServiceDbTest extends TestCase{

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

       self::$db->command("DELETE FROM personnel");
	}

	public function test_If_registerPersonnel_Creates_A_New_Personnel_And_Returns_Its_Id(){

		$image_path_provider = new ImagePathProvider('/Users/selimatasever/Desktop/GitHub/erp-api/v1/tests/yol');

		$image_direct_access_link_provider = new ImageDirectAccessLinkProvider(
			self::$jwToken, 'www.example.com', $image_path_provider, '0.0.0.0 8.8.4.4'
		);

		$department_repository = new DepartmentRepository(self::$db, null);
		$role_repository = new RoleRepository(self::$db, null);
		$personnel_repository = new PersonnelRepository(self::$db, null);

		$identity_service = new IdentityService(
			$personnel_repository, $role_repository, $department_repository, $image_direct_access_link_provider
		);
		$function_returned_id = $identity_service->registerPersonnel(
			1,
			null,
			1,
			'joe',
			'doe',
			'12345678900',
			'male',
			'+49104205479',
			'joe@doe.com',
			true
		);

		$db_returned_id = self::$db->query("SELECT * FROM personnel WHERE id = :id", array(
			':id' => $function_returned_id
		))->row['id'];

		$this->assertEquals($function_returned_id, $db_returned_id);
	}

	public function test_If_updatePersonnel_Updates_New_Created_Personnel_On_Db(){

		$image_path_provider = new ImagePathProvider('/Users/selimatasever/Desktop/GitHub/erp-api/v1/tests/yol');

		$image_direct_access_link_provider = new ImageDirectAccessLinkProvider(
			self::$jwToken, 'www.example.com', $image_path_provider, '0.0.0.0 8.8.4.4'
		);

		$department_repository = new DepartmentRepository(self::$db, null);
		$role_repository = new RoleRepository(self::$db, null);
		$personnel_repository = new PersonnelRepository(self::$db, null);

		$identity_service = new IdentityService(
			$personnel_repository, $role_repository, $department_repository, $image_direct_access_link_provider
		);

		$personnel_id = $identity_service->registerPersonnel(
			2,
			null,
			2,
			'zoe',
			'doe',
			'00345678900',
			'female',
			'+48104205479',
			'zoe@doe.com',
			true
		);

		$identity_service->updatePersonnel(
			$personnel_id,
			2,
			2,
			null,
			'john',
			'dove',
			'11223344550',
			'male',
			'+019214904124',
			'johndove@kant.ist',
			true
		);

		$updated_fullname_db = self::$db->query("SELECT firstname, lastname FROM personnel WHERE id = :id", array(
			':id' => $personnel_id
		))->row;

		$arr_of_fullname = array('firstname' => 'john', 'lastname' => 'dove');
		$this->assertEquals($updated_fullname_db, $arr_of_fullname);
   }	

   	public function test_If_unregisterPersonnel_Removes_The_Personnel_With_Given_Id(){

   		$image_path_provider = new ImagePathProvider('/Users/selimatasever/Desktop/GitHub/erp-api/v1/tests/yol');

		$image_direct_access_link_provider = new ImageDirectAccessLinkProvider(
			self::$jwToken, 'www.example.com', $image_path_provider, '0.0.0.0 8.8.4.4'
		);

		$department_repository = new DepartmentRepository(self::$db, null);
		$role_repository = new RoleRepository(self::$db, null);
		$personnel_repository = new PersonnelRepository(self::$db, null);

		$identity_service = new IdentityService(
			$personnel_repository, $role_repository, $department_repository, $image_direct_access_link_provider
		);

		$personnel_id = $identity_service->registerPersonnel(
			1,
			null,
			2,
			'jill',
			'doe',
			'80345678900',
			'female',
			'+48104205409',
			'jill@do.com',
			true
		);

		$identity_service->unregisterPersonnel($personnel_id);

		$this->assertEmpty(
			self::$db->query("SELECT * FROM personnel WHERE id = :id", array(
			':id' => $personnel_id
		))->row
   	  );
  	}


  	public function test_If_getPersonnel_Returns_Personnel_From_Db(){

  		$image_path_provider = new ImagePathProvider('/Users/selimatasever/Desktop/GitHub/erp-api/v1/tests/yol');

		$image_direct_access_link_provider = new ImageDirectAccessLinkProvider(
			self::$jwToken, 'www.example.com', $image_path_provider, '0.0.0.0 8.8.4.4'
		);

		$department_repository = new DepartmentRepository(self::$db, null);
		$role_repository = new RoleRepository(self::$db, null);
		$personnel_repository = new PersonnelRepository(self::$db, null);

		$identity_service = new IdentityService(
			$personnel_repository, $role_repository, $department_repository, $image_direct_access_link_provider
		);

		$personnel_id = $identity_service->registerPersonnel(
			1,
			null,
			2,
			'jill',
			'doe',
			'80345678900',
			'female',
			'+48104205409',
			'jill@do.com',
			true
		);

		$personnel_dto = $identity_service->getPersonnel($personnel_id);
		$this->assertNotEmpty($personnel_dto);

		$id = $personnel_dto->id();
		$firstname = $personnel_dto->firstname();
		$lastname = $personnel_dto->lastname();
		$tcno = $personnel_dto->tcno();

		$this->assertEquals($id, $personnel_id);
		$this->assertEquals($firstname, 'jill');
		$this->assertEquals($lastname, 'doe');
		$this->assertEquals($tcno, 80345678900);

 	}

 	public function test_If_getPersonnelBasic_Returns_Personnel_Dto(){

 		$image_path_provider = new ImagePathProvider('/Users/selimatasever/Desktop/GitHub/erp-api/v1/tests/yol');

		$image_direct_access_link_provider = new ImageDirectAccessLinkProvider(
			self::$jwToken, 'www.example.com', $image_path_provider, '0.0.0.0 8.8.4.4'
		);

		$department_repository = new DepartmentRepository(self::$db, null);
		$role_repository = new RoleRepository(self::$db, null);
		$personnel_repository = new PersonnelRepository(self::$db, null);

		$identity_service = new IdentityService(
			$personnel_repository, $role_repository, $department_repository, $image_direct_access_link_provider
		);

		$personnel_id = $identity_service->registerPersonnel(
			1,
			null,
			2,
			'mike',
			'doe',
			'70345678900',
			'male',
			'+47104205409',
			'mike@do.com',
			true
		);

 		$personnel_dto = $identity_service->getPersonnelBasic($personnel_id);
 		$this->assertNotEmpty($personnel_dto);
 	}

 	public function test_If_getPersonnelByEmail_Returns_Personnel_From_Db() {

 		$image_path_provider = new ImagePathProvider('/Users/selimatasever/Desktop/GitHub/erp-api/v1/tests/yol');

		$image_direct_access_link_provider = new ImageDirectAccessLinkProvider(
			self::$jwToken, 'www.example.com', $image_path_provider, '0.0.0.0 8.8.4.4'
		);

		$department_repository = new DepartmentRepository(self::$db, null);
		$role_repository = new RoleRepository(self::$db, null);
		$personnel_repository = new PersonnelRepository(self::$db, null);

		$identity_service = new IdentityService(
			$personnel_repository, $role_repository, $department_repository, $image_direct_access_link_provider
		);	

		$personnel_id = $identity_service->registerPersonnel(
			2,
			null,
			2,
			'george',
			'doe',
			'60345678900',
			'male',
			'+46104205409',
			'george@doe.com',
			true
		);

		$personnel = $identity_service->getPersonnelByEmail('george@doe.com');

		$id = $personnel->id();
		$firstname = $personnel->firstname();
		$lastname = $personnel->lastname();
		$email = $personnel->email();

		$this->assertEquals($personnel_id, $id);
		$this->assertEquals($firstname, 'george');
		$this->assertEquals($lastname, 'doe');
		$this->assertEquals($email, 'george@doe.com');
 	}

 	public function test_If_getPersonnelByPhone_Returns_Personnel_From_Db(){

 		$image_path_provider = new ImagePathProvider('/Users/selimatasever/Desktop/GitHub/erp-api/v1/tests/yol');

		$image_direct_access_link_provider = new ImageDirectAccessLinkProvider(
			self::$jwToken, 'www.example.com', $image_path_provider, '0.0.0.0 8.8.4.4'
		);

		$department_repository = new DepartmentRepository(self::$db, null);
		$role_repository = new RoleRepository(self::$db, null);
		$personnel_repository = new PersonnelRepository(self::$db, null);

		$identity_service = new IdentityService(
			$personnel_repository, $role_repository, $department_repository, $image_direct_access_link_provider
		);	

		$personnel_id = $identity_service->registerPersonnel(
			2,
			null,
			2,
			'will',
			'doe',
			'50345678900',
			'male',
			'+1 389238139',
			'will@doe.com',
			true
		);

		$personnel = $identity_service->getPersonnelByPhone('+1 389238139');

		$id = $personnel->id();
		$department_id = $personnel->department_id();
		$role_id = $personnel->role_id();
		$email = $personnel->email();

		$this->assertEquals($id, $personnel_id);
		$this->assertEquals($department_id, 2);
		$this->assertEquals($role_id, 2);
		$this->assertEquals($email, 'will@doe.com');
 	
 	}

 	public function test_If_getPersonnels_Returns_Personnels_Array_From_Db(){

 		$image_path_provider = new ImagePathProvider('/Users/selimatasever/Desktop/GitHub/erp-api/v1/tests/yol');

		$image_direct_access_link_provider = new ImageDirectAccessLinkProvider(
			self::$jwToken, 'www.example.com', $image_path_provider, '0.0.0.0 8.8.4.4'
		);

		$department_repository = new DepartmentRepository(self::$db, null);
		$role_repository = new RoleRepository(self::$db, null);
		$personnel_repository = new PersonnelRepository(self::$db, null);

		$identity_service = new IdentityService(
			$personnel_repository, $role_repository, $department_repository, $image_direct_access_link_provider
		);	

		$personnel_query_dto = $identity_service->getPersonnels(new QueryObject());
		$arr_personnels = $personnel_query_dto->personnels();

		$this->assertEquals(count($arr_personnels), 6); // 6 is the number of existing personnel on db. 
 	}	


 	public function test_If_personnelWithIdExists_Returns_True_If_Personnel_Exists(){

 		$image_path_provider = new ImagePathProvider('/Users/selimatasever/Desktop/GitHub/erp-api/v1/tests/yol');

		$image_direct_access_link_provider = new ImageDirectAccessLinkProvider(
			self::$jwToken, 'www.example.com', $image_path_provider, '0.0.0.0 8.8.4.4'
		);

		$department_repository = new DepartmentRepository(self::$db, null);
		$role_repository = new RoleRepository(self::$db, null);
		$personnel_repository = new PersonnelRepository(self::$db, null);

		$identity_service = new IdentityService(
			$personnel_repository, $role_repository, $department_repository, $image_direct_access_link_provider
		);	

		$personnel_id = $identity_service->registerPersonnel(
			2,
			null,
			1,
			'rob',
			'doe',
			'40345678900',
			'male',
			'+1 388238139',
			'rob@doe.com',
			true
		);

		$personnel_exists = $identity_service->personnelWithIdExists($personnel_id);
		$this->assertTrue($personnel_exists);

		$personnel_doesnt_exist = $identity_service->personnelWithIdExists(1);
		$this->assertFalse($personnel_doesnt_exist);
 	}
}

?>