<?php

use \model\IdentityAndAccess\domain\model\Role;
use \model\IdentityAndAccess\domain\model\Privilege;
use \model\IdentityAndAccess\domain\model\exception\RoleNameIsTooLongException; 
use \model\IdentityAndAccess\domain\model\exception\RoleNameIsNullException;
use \model\IdentityAndAccess\domain\model\exception\RoleDuplicatePrivilegeException;
use \model\common\ExceptionCollection;
use \model\IdentityAndAccess\domain\model\RoleId;
use \model\common\domain\model\SubmoduleId;

use PHPUnit\Framework\TestCase;


class RoleTest extends TestCase {

	public function testRoleNameCannotBeNull() {

		$this->expectException(RoleNameIsNullException::class);

		$role = new Role(new RoleId(1), '');

	}


	public function testRoleCanBeRenamed() {

		$role = new Role(new RoleId(1) ,'ornek');

		$this->assertEquals('ornek', $role->getName());

		$role->rename('role');

		$this->assertEquals('role', $role->getName());

	}


	public function testRoleNameCannotBeLongerThan50Characters() {


		$this->expectException(RoleNameIsTooLongException::class);

		$role = new Role(new RoleId(1), str_repeat(1, 51));

	}


	public function testAddPrivilegeCanCreateNewPrivilege() {

		
		$role = new Role(new RoleId(1), 'testrole');

		$privilege = new Privilege(new SubmoduleId(1), true, false, false);
		$privilege2 = new Privilege(new SubmoduleId(2), false, true, false);

		$role->addPrivilege($privilege);
		$role->addPrivilege($privilege2);

		$privilege_array = $role->getPrivileges();

		$this->assertEquals([$privilege, $privilege2], $privilege_array);

	}


	public function testClearPrivilegesCanClearAddedPrivileges() {


		$role = new Role(new RoleId(1), 'test-role');

		$privilege = new Privilege(new SubmoduleId(3), true, false, false);

		$role->addPrivilege($privilege);

		$role->clearPrivileges();

		$this->assertEmpty($role->getPrivileges());

	}


	public function testPrivilagesCannotUseSameSubdomainId() {

		$this->expectException(RoleDuplicatePrivilegeException::class);

		$role = new Role(new RoleId(1), 'test-_-role');

		$privilege = new Privilege(new SubmoduleId(1), true, false, false);
		$privilege2 = new Privilege(new SubmoduleId(1), false, true, false);

		$role->addPrivilege($privilege);
		$role->addPrivilege($privilege2);
	}


	public function testEqualsReturnsTrueIfRoleIdsAreSame() {

		$role = new Role(new RoleId(1), '-role-');

		$role2 = new Role(new RoleId(1), '-test-');

		$this->assertTrue($role->equals($role2));

	}

	public function testEqualsReturnsFalseIfRoleIdsAreDifferent() {

		$role = new Role(new RoleId(2), 'role');

		$role2 = new Role(new RoleId(1), 'test');

		$this->assertNotTrue($role->equals($role2));


	}


}


?>
