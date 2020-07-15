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


	public function testIfSaveCreatesNewRole() {

		$role_repository = new RoleRepository();

		$new_role = $role_repository->save(new Role(null, 'role1'));

		$check_role_db = self::$db->query("SELECT * FROM role WHERE id = :id", array(
			':id' => $new_role->getId()
		))->row;

		$this->assertNotEmpty($check_role_db);
	}

	public function testIfSaveUpdatesAnExistingRole(){

		$role_repository = new RoleRepository();

		$new_role = $role_repository->save(new Role(null, 'role-1'));

		$role_repository->save(new Role($new_role, 'role-2'));

		$updated_role = $role_repository->findById($new_role);

		$this->assertEquals($updated_role->getName(), 'role-2');

	}

	public function testIfRemoveDeletesRoleAndCarriesItToRoleBin() {

		$role_repository = new RoleRepository();

		$new_role = $role_repository->save(new Role(null, 'role-3'));

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


	public function testIfExistsWithNameFindsTheRole() {

		$role_repository = new RoleRepository();

		$new_role = $role_repository->save(new Role(null, 'role-4'));

		$check_role_exists = $role_repository->existsWithName('role-4');

		$this->assertTrue($check_role_exists);
	}


	public function testIfFetchRoleReturnsCorrectNumberOfRoles() {

		self::$db->command("DELETE FROM role");
        self::$db->command("DELETE FROM role_bin");

		$role_repository = new RoleRepository();

		$new_role1 = $role_repository->save(new Role(null, 'role-5'));
		$new_role2 = $role_repository->save(new Role(null, 'role-6'));

		$fetched_roles = $role_repository->fetchAll(new QueryObject());

		$this->assertEquals(2, count($fetched_roles));

		



	}

}

?>