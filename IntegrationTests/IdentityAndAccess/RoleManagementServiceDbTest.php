<?php

use \model\IdentityAndAccess\application\RoleManagementService;
use \model\common\application\SubmoduleService;

use \model\IdentityAndAccess\infrastructure\PersonnelRepository;
use \model\IdentityAndAccess\infrastructure\RoleRepository;

use \model\common\infrastructure\ModuleRepository;
use \model\common\infrastructure\SubmoduleRepository;

use \model\IdentityAndAccess\application\DTO\PrivilegeDTO;
use PHPUnit\Framework\TestCase;
use \model\common\QueryObject;

class RoleManagementServiceDbTest extends TestCase{

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

	public function test_getRole_Returns_Role_From_Db(){

		$submodule_repository = new SubmoduleRepository();
		$module_repository = new ModuleRepository();

		$submodule_service = new SubmoduleService($submodule_repository, $module_repository);
		$personnel_repository = new PersonnelRepository(self::$db, null);
	 	$role_repository = new RoleRepository(self::$db, null);

	 	$role_management_service = new RoleManagementService(
	 		$personnel_repository, $role_repository, $submodule_service
	 	);

	 	$role = $role_management_service->getRole(1);

	 	$id = $role->id();
	 	$name = $role->name();

	 	$this->assertEquals($id, 1);
	 	$this->assertEquals($name, 'role_name_1');
	}

	public function test_If_getRoles_Returns_Existing_Roles_On_Db(){

		$submodule_repository = new SubmoduleRepository();
		$module_repository = new ModuleRepository();

		$submodule_service = new SubmoduleService($submodule_repository, $module_repository);
		$personnel_repository = new PersonnelRepository(self::$db, null);
	 	$role_repository = new RoleRepository(self::$db, null);

	 	$role_management_service = new RoleManagementService(
	 		$personnel_repository, $role_repository, $submodule_service
	 	);

	 	$roles = $role_management_service->getRoles(new QueryObject());
	 	$this->assertNotEmpty($roles);
	}

	public function test_registerRole_Creates_A_New_Role_And_Returns_Its_Id(){

		$submodule_repository = new SubmoduleRepository();
		$module_repository = new ModuleRepository();

		$submodule_service = new SubmoduleService($submodule_repository, $module_repository);
		$personnel_repository = new PersonnelRepository(self::$db, null);
	 	$role_repository = new RoleRepository(self::$db, null);

	 	$role_management_service = new RoleManagementService(
	 		$personnel_repository, $role_repository, $submodule_service
	 	);

	 	$privilege_dto = new PrivilegeDTO(1,true,true,true);
	 	$privileges = array($privilege_dto);
	 	$role_id = $role_management_service->registerRole('role_name_3' , $privileges);

	 	$role_id_from_db = self::$db->query("SELECT * FROM role WHERE id = :id", array(
	 		':id' => $role_id
	 	))->row['id'];

	 	$this->assertEquals($role_id, $role_id_from_db);

	}

	public function test_If_updateRole_Updates_Creates_Role(){

		$submodule_repository = new SubmoduleRepository();
		$module_repository = new ModuleRepository();

		$submodule_service = new SubmoduleService($submodule_repository, $module_repository);
		$personnel_repository = new PersonnelRepository(self::$db, null);
	 	$role_repository = new RoleRepository(self::$db, null);

	 	$role_management_service = new RoleManagementService(
	 		$personnel_repository, $role_repository, $submodule_service
	 	);

	 	$privilege_dto = new PrivilegeDTO(1,true,true,true);
	 	$privileges = array($privilege_dto);
	 	$role_id = $role_management_service->registerRole('role_4' , $privileges);

	 	$role_management_service->updateRole($role_id, 'role_name_4', $privileges);

	 	$db_role_update = self::$db->query("SELECT * FROM role WHERE id = :id", array(
	 		':id' => $role_id
	 	))->row['name'];

	 	$this->assertEquals($db_role_update, 'role_name_4');
	}

	public function test_If_removeRole_Removes_Created_Role(){

		$submodule_repository = new SubmoduleRepository();
		$module_repository = new ModuleRepository();

		$submodule_service = new SubmoduleService($submodule_repository, $module_repository);
		$personnel_repository = new PersonnelRepository(self::$db, null);
	 	$role_repository = new RoleRepository(self::$db, null);

	 	$role_management_service = new RoleManagementService(
	 		$personnel_repository, $role_repository, $submodule_service
	 	);

	 	$privilege_dto = new PrivilegeDTO(1,true,true,true);
	 	$privileges = array($privilege_dto);
	 	$role_id = $role_management_service->registerRole('role_4' , $privileges);

	 	$role_management_service->removeRole($role_id);

	 	$this->assertEmpty(self::$db->query("SELECT * FROM role WHERE id = :id", array(
	 		':id' => $role_id
	 	))->row);
	}

	public function test_If_getPersonnelRole_Returns_Personnels_Role(){

		$submodule_repository = new SubmoduleRepository();
		$module_repository = new ModuleRepository();

		$submodule_service = new SubmoduleService($submodule_repository, $module_repository);
		$personnel_repository = new PersonnelRepository(self::$db, null);
	 	$role_repository = new RoleRepository(self::$db, null);

	 	$role_management_service = new RoleManagementService(
	 		$personnel_repository, $role_repository, $submodule_service
	 	);

	 	self::$db->insert('personnel' , array(
	 		'id' => 1,
	 		'role_id' => 2,
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

	 	$role = $role_management_service->getPersonnelRole(1);

	 	$id = $role->id();
	 	$name = $role->name();

	 	$this->assertEquals($id, 2);
	 	$this->assertEquals($name, 'role_name_2');
	}

}

?>