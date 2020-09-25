<?php

use \model\IdentityAndAccess\infrastructure\RoleRepository;
use \model\IdentityAndAccess\domain\model\RoleId;
use \model\IdentityAndAccess\domain\model\IRoleRepository;
use \model\IdentityAndAccess\domain\model\Role;
use \model\IdentityAndAccess\domain\model\Privilege;
use \model\common\domain\model\SubmoduleId;
use \model\common\QueryObject;

use PHPUnit\Framework\TestCase;

class RoleRepositoryTest extends TestCase {


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

        self::$db->command("DELETE FROM role");
        self::$db->command("DELETE FROM role_bin");
	}


	public function test_If_save_Method_Creates_A_New_Role() {

		$role_repository = new RoleRepository(self::$db);

		$new_role = $role_repository->save(new Role(
			null, 
			'role_2'
		));

		$check_role_db = self::$db->query("SELECT * FROM role WHERE id = :id", array(
			':id' => $new_role->getId()
		))->row;

		$this->assertNotEmpty($check_role_db);
	}

	public function test_If_save_Method_Updates_Existing_Role(){

		$role_repository = new RoleRepository(self::$db);

		$new_role = $role_repository->save(new Role(
			null, 
			'role-1'
		));

		$role_repository->save(new Role($new_role, 'role_1'));

		$updated_role = $role_repository->findById($new_role);

		$this->assertEquals($updated_role->getName(), 'role_1');

	}

	public function test_If_remove_Method_Deletes_The_Role_And_Carries_It_To_Role_Bin() {

		$role_repository = new RoleRepository(self::$db);

		$new_role = $role_repository->save(new Role(
			null, 
			'role-3'
		));

		$role_repository->remove($new_role);

		$check_if_role_deleted = self::$db->query("SELECT * FROM role WHERE id = :id", array(
			':id' => $new_role->getId()
		))->row;

		$this->assertEmpty($check_if_role_deleted);

		$check_role_bin = self::$db->query("SELECT * FROM role_bin WHERE id= :id", array(
			':id' => $new_role->getId()
		))->row;

		$this->assertNotEmpty($check_role_bin);
	}


	public function test_If_existsWithName_Method_Finds_The_Role_With_Roles_Name(){

		$role_repository = new RoleRepository(self::$db);

		$new_role = $role_repository->save(new Role(
			null, 'role_3'
		));

		$check_role_exists = $role_repository->existsWithName('role_3');

		$this->assertTrue($check_role_exists);
	}


	public function test_If_count_Method_Returns_The_Number_Of_Roles_On_Db(){

		$role_repository = new RoleRepository(self::$db);

		$number_of_roles = $role_repository->roleCount(new QueryObject());
		
		$this->assertEquals($number_of_roles, 3);
	}


	public function test_If_fetchAll_Method_Returns_All_Roles_On_Db(){

		$role_repository = new RoleRepository(self::$db);

		$array_of_roles = $role_repository->fetchAll(new QueryObject());

		$this->assertIsArray($array_of_roles);
		$this->assertEquals(count($array_of_roles), 3);
	}

}

?>