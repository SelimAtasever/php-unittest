<?php

use \model\IdentityAndAccess\application\AuthorizationService;
use \model\IdentityAndAccess\infrastructure\PersonnelRepository;
use \model\IdentityAndAccess\infrastructure\RoleRepository;
use \model\common\application\SubmoduleService;

use \model\common\infrastructure\ModuleRepository;
use \model\common\infrastructure\SubmoduleRepository;

use PHPUnit\Framework\TestCase;


class AuthorizationServiceDbTest extends TestCase{

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

       self::$db->command("DELETE FROM personnel");
       self::$db->command("DELETE FROM role");
       self::$db->command("DELETE FROM privilege");


	}		

	public function test_canView_Returns_True_When_Personnel_Has_Access(){

		$module_repository = new ModuleRepository();
		$submodule_repository = new SubmoduleRepository();
		
		$submodule_service = new SubmoduleService($submodule_repository, $module_repository);
		$personnel_repository = new PersonnelRepository(self::$db, null);
		$role_repository = new RoleRepository(self::$db, null);

		$authorization_service = new AuthorizationService(
			$personnel_repository, $role_repository, $submodule_service
		);

		self::$db->insert('personnel', array(
			'id' => 1,
			'role_id' => 1,
			'department_id' => 1,
			'image_id' => null,
			'firstname' => 'sly',
			'lastname' => 'stallone',
			'tcno' => 34140042140,
			'gender' => 'male',
			'phone' => '0049241891204',
			'email' => 'stallone@kant.ist',
			'is_active' => 1,
			'date_added' => (new DateTime())->format('Y-m-d H:i:s'),
			'last_modification' => (new DateTime())->format('Y-m-d H:i:s')
		));

		self::$db->insert('role', array(
			'id' => 1,
			'name' => 'role_name_1'
		));

		self::$db->insert('role', array(
			'id' => 2,
			'name' => 'role_name_2'
		));

		self::$db->insert('privilege', array(
			'role_id' => 1,
			'submodule_id' => 1,
			'create_privileges' => 1,
			'update_privileges' => 1,
			'delete_privileges' => 1
		));

		self::$db->insert('privilege', array(
			'role_id' => 2,
			'submodule_id' => 2,
			'create_privileges' => 1,
			'update_privileges' => 1,
			'delete_privileges' => 1
		));

		$confirm_canView = $authorization_service->canView(1,1); 
		$this->assertTrue($confirm_canView);
	}

	public function test_canCreate_Returns_True_When_Personnel_Has_Access(){

		$module_repository = new ModuleRepository();
		$submodule_repository = new SubmoduleRepository();
		
		$submodule_service = new SubmoduleService($submodule_repository, $module_repository);
		$personnel_repository = new PersonnelRepository(self::$db, null);
		$role_repository = new RoleRepository(self::$db, null);

		$authorization_service = new AuthorizationService(
			$personnel_repository, $role_repository, $submodule_service
		);

		$confrim_canCreate = $authorization_service->canCreate(1,1);
		$this->assertTrue($confrim_canCreate);
	}

	public function test_canUpdate_Returns_True_When_Personnel_Has_Access(){

		$module_repository = new ModuleRepository();
		$submodule_repository = new SubmoduleRepository();
		
		$submodule_service = new SubmoduleService($submodule_repository, $module_repository);
		$personnel_repository = new PersonnelRepository(self::$db, null);
		$role_repository = new RoleRepository(self::$db, null);

		$authorization_service = new AuthorizationService(
			$personnel_repository, $role_repository, $submodule_service
		);

		$confirm_canUpdate = $authorization_service->canUpdate(1,1); 
		$this->assertTrue($confirm_canUpdate);
	}

	public function test_If_canDelete_Returns_True_When_Personnel_Has_Access(){

		$module_repository = new ModuleRepository();
		$submodule_repository = new SubmoduleRepository();
		
		$submodule_service = new SubmoduleService($submodule_repository, $module_repository);
		$personnel_repository = new PersonnelRepository(self::$db, null);
		$role_repository = new RoleRepository(self::$db, null);

		$authorization_service = new AuthorizationService(
			$personnel_repository, $role_repository, $submodule_service
		);

		$confirm_canDelete = $authorization_service->canDelete(1,1);
		$this->assertTrue($confirm_canDelete);

	}

	public function test_If_getAuthorizedSubmodules_Returns_An_Array_Of_Submodule_Dtos(){

		$module_repository = new ModuleRepository();
		$submodule_repository = new SubmoduleRepository();
		
		$submodule_service = new SubmoduleService($submodule_repository, $module_repository);
		$personnel_repository = new PersonnelRepository(self::$db, null);
		$role_repository = new RoleRepository(self::$db, null);

		$authorization_service = new AuthorizationService(
			$personnel_repository, $role_repository, $submodule_service
		);

		$returned_submodule_dtos = $authorization_service->getAuthorizedSubmodules(1);
		$this->assertEquals(count($returned_submodule_dtos), 1);
	}
}

?>