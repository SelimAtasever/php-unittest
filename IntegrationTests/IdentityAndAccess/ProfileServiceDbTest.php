<?php

use \model\IdentityAndAccess\application\ProfileService;

use \model\IdentityAndAccess\infrastructure\ImagePathProvider;
use \model\IdentityAndAccess\infrastructure\PersonnelRepository;
use \model\IdentityAndAccess\infrastructure\DepartmentRepository;
use \model\IdentityAndAccess\infrastructure\RoleRepository;
use \model\IdentityAndAccess\infrastructure\IdentityProvider;
use \model\IdentityAndAccess\infrastructure\ImageDirectAccessLinkProvider;

use PHPUnit\Framework\TestCase;


class ProfileServiceDbTest extends TestCase{

	private static \DB $db;
	private static $jwToken;

 	public static function setUpBeforeClass() : void {
    	
    	global $framework;

        $config = $framework->get('config');
    	self::$jwToken = $framework->get('jwt');

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

	public function test_If_updateSelf_Function_Updates_Created_Active_Personnel(){

	 	$image_path_provider = new ImagePathProvider('/Users/selimatasever/Desktop/GitHub/erp-api/v1/tests/yol');

		$image_direct_access_link_provider = new ImageDirectAccessLinkProvider(
			self::$jwToken, 'www.example.com', $image_path_provider, '0.0.0.0 8.8.4.4'
		);

		$identity_provider = new IdentityProvider(1);
		$department_repository = new DepartmentRepository(self::$db, null);
	 	$personnel_repository = new PersonnelRepository(self::$db, null);
	 	$role_repository = new RoleRepository(self::$db, null);

	 	$profile_service = new ProfileService(
	 		$personnel_repository, $role_repository, $department_repository, $identity_provider, $image_direct_access_link_provider
	 	);

	 	self::$db->insert('personnel' , array(
	 		'id' => 1,
	 		'role_id' => 1,
	 		'department_id' => 1,
	 		'image_id' => null,
	 		'firstname' => 'ronnie',
	 		'lastname' => 'pickaring',
	 		'tcno' => '11223344550',
	 		'gender' => 'male',
	 		'phone' => '+90 5142021490',
	 		'email' => 'ronnie@pickaring.co.uk',
	 		'is_active' => true,
	 		'date_added' => (new DateTime())->format('Y-m-d H:i:s'),
	 		'last_modification' => (new DateTime())->format('Y-m-d H:i:s')
	 	));

	 	self::$db->insert('personnel' , array(
	 		'id' => 2,
	 		'role_id' => 2,
	 		'department_id' => 1,
	 		'image_id' => null,
	 		'firstname' => 'dummy',
	 		'lastname' => 'personnel',
	 		'tcno' => '11223304550',
	 		'gender' => 'male',
	 		'phone' => '+90 5142021491',
	 		'email' => 'dummy@personnel.co.uk',
	 		'is_active' => true,
	 		'date_added' => (new DateTime())->format('Y-m-d H:i:s'),
	 		'last_modification' => (new DateTime())->format('Y-m-d H:i:s')
	 	));

	 	$profile_service->updateSelf('+40 5142021490', 'ronnie_pickaring@citroen.co.uk');

	 	$arr_of_phone_and_email = array('phone' => '+40 5142021490', 'email' => 'ronnie_pickaring@citroen.co.uk');
	 	$updated_info = self::$db->query("SELECT phone, email FROM personnel WHERE id = 1")->row;  

	 	$this->assertEquals($arr_of_phone_and_email, $updated_info);
	}

	public function test_If_getSelf_Returns_Active_Personnel_From_Db(){

		$image_path_provider = new ImagePathProvider('/Users/selimatasever/Desktop/GitHub/erp-api/v1/tests/yol');

		$image_direct_access_link_provider = new ImageDirectAccessLinkProvider(
			self::$jwToken, 'www.example.com', $image_path_provider, '0.0.0.0 8.8.4.4'
		);

		$identity_provider = new IdentityProvider(1);
		$department_repository = new DepartmentRepository(self::$db, null);
	 	$personnel_repository = new PersonnelRepository(self::$db, null);
	 	$role_repository = new RoleRepository(self::$db, null);

	 	$profile_service = new ProfileService(
	 		$personnel_repository, $role_repository, $department_repository, $identity_provider, $image_direct_access_link_provider
	 	);

	 	$personnel = $profile_service->getSelf();

	 	$firstname = $personnel->firstname();
	 	$phone = $personnel->phone();
	 	$department_id = $personnel->department_id();
	 	$tcno = $personnel->tcno();

	 	$this->assertEquals($firstname, 'ronnie');
	 	$this->assertEquals($phone, '+40 5142021490');
	 	$this->assertEquals($department_id, 1);
	 	$this->assertEquals($tcno, 11223344550);
	}	

	public function test_If_getRole_Returns_Active_Personnels_Role(){

		$image_path_provider = new ImagePathProvider('/Users/selimatasever/Desktop/GitHub/erp-api/v1/tests/yol');

		$image_direct_access_link_provider = new ImageDirectAccessLinkProvider(
			self::$jwToken, 'www.example.com', $image_path_provider, '0.0.0.0 8.8.4.4'
		);

		$identity_provider = new IdentityProvider(1);
		$department_repository = new DepartmentRepository(self::$db, null);
	 	$personnel_repository = new PersonnelRepository(self::$db, null);
	 	$role_repository = new RoleRepository(self::$db, null);

	 	$profile_service = new ProfileService(
	 		$personnel_repository, $role_repository, $department_repository, $identity_provider, $image_direct_access_link_provider
	 	);

	 	$role = $profile_service->getRole();

	 	$id = $role->id();
	 	$name = $role->name();

	 	$this->assertEquals($id, 1);
	 	$this->assertEquals($name, 'role_name_1');
	}

	public function test_If_getDepartment_Returns_Department_Dto(){

		$image_path_provider = new ImagePathProvider('/Users/selimatasever/Desktop/GitHub/erp-api/v1/tests/yol');

		$image_direct_access_link_provider = new ImageDirectAccessLinkProvider(
			self::$jwToken, 'www.example.com', $image_path_provider, '0.0.0.0 8.8.4.4'
		);

		$identity_provider = new IdentityProvider(1);
		$department_repository = new DepartmentRepository(self::$db, null);
	 	$personnel_repository = new PersonnelRepository(self::$db, null);
	 	$role_repository = new RoleRepository(self::$db, null);

	 	$profile_service = new ProfileService(
	 		$personnel_repository, $role_repository, $department_repository, $identity_provider, $image_direct_access_link_provider
	 	);

	 	$department_dto = $profile_service->getDepartment();
		$department_as_array = json_decode(json_encode($department_dto), true); // dto object, converted to array

		$id = $department_as_array['id'];
		$director = $department_as_array['attributes']['director'];		
		$name = $department_as_array['attributes']['name'];		

		$this->assertEquals($id, 1);
		$this->assertEquals($director, 1);
		$this->assertEquals($name, 'chief');
	}

	public function test_If_getAccessiblePersonnel_Returns_An_Array_Of_Accessible_Personnels(){

		$image_path_provider = new ImagePathProvider('/Users/selimatasever/Desktop/GitHub/erp-api/v1/tests/yol');

		$image_direct_access_link_provider = new ImageDirectAccessLinkProvider(
			self::$jwToken, 'www.example.com', $image_path_provider, '0.0.0.0 8.8.4.4'
		);

		$identity_provider = new IdentityProvider(1);
		$department_repository = new DepartmentRepository(self::$db, null);
	 	$personnel_repository = new PersonnelRepository(self::$db, null);
	 	$role_repository = new RoleRepository(self::$db, null);

	 	$profile_service = new ProfileService(
	 		$personnel_repository, $role_repository, $department_repository, $identity_provider, $image_direct_access_link_provider
	 	);

	 	$arr_of_personnels = $profile_service->getAccessiblePersonnel();

	 	$this->assertEquals(count($arr_of_personnels), 2); // 2 is the number of personnels on db.
	}
}

?>