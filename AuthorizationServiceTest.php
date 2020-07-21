<?php 

use \model\IdentityAndAccess\application\AuthorizationService;
use \model\IdentityAndAccess\application\exception\PersonnelNotFoundException;
use \model\IdentityAndAccess\application\exception\RoleNotFoundException;
use \model\IdentityAndAccess\domain\model\PersonnelId;
use \model\IdentityAndAccess\domain\model\IPersonnelRepository;
use \model\IdentityAndAccess\domain\model\IRoleRepository;
use \model\IdentityAndAccess\domain\model\AuthorizationDomainService;
use \model\IdentityAndAccess\domain\model\Personnel;
use \model\IdentityAndAccess\domain\model\RoleId;
use \model\IdentityAndAccess\domain\model\Role;
use \model\IdentityAndAccess\infrastructure\PersonnelRepository;
use \model\IdentityAndAccess\infrastructure\RoleRepository;
use \model\common\domain\model\SubmoduleId;
use \model\common\application\SubmoduleService;
use \model\IdentityAndAccess\domain\model\Privilege;

use PHPUnit\Framework\TestCase;


class AuthorizationServiceTest extends TestCase {


	private AuthorizationService $authorization_service;
	private Personnel $personnel;
	private Role $role;
	private Role $role2;


	protected function setUp() : void {

		$this->personnel = new Personnel(new PersonnelId(1), new RoleId(1),true, null , 'john', 'doe', '11223344556', 'male', '0049224591432', 'johndoe@mail.com', null, null);	
		$this->role = new Role(new RoleId(1), 'role1'); 	//role will return false if wont take privilege
		$this->role->addPrivilege(new Privilege(new SubmoduleId(1), true, true, true));
		
		$this->role2 = new Role(new RoleId(2), 'role2');
		$this->role2->addPrivilege(new Privilege(new SubmoduleId(2), false, false, false));


		$can_create = $this->createMock(IPersonnelRepository::class);
		$can_create->method('findById')->willReturn($this->personnel);

		$can_crud_role = $this->createMock(IRoleRepository::class);
		$can_crud_role->method('findById')->willReturn($this->role);

		$cannot_crud_role = $this->createMock(IRoleRepository::class);
		$cannot_crud_role->method('findById')->willReturn($this->role2);

		$personnel_repository = $this->createMock(IPersonnelRepository::class);
		$personnel_repository->method('findById')->willReturn(null);

		$role_repository = $this->createMock(IRoleRepository::class);
		$role_repository->method('findById')->willReturn(null);

		$submodule_service = $this->createMock(SubmoduleService::class);
		$submodule_service->method('getById')->willReturn(null);

		$personnel_exists = $this->createMock(IPersonnelRepository::class);
		$personnel_exists->method('findById')->willReturn(new Personnel(new PersonnelId(1), new RoleId(1),true, null, 'john', 'doe', '11223344556', 'female', '0049224591432', 'johndoe@mail.com', null, null));


		$this->authorization_service = new AuthorizationService($personnel_repository, $role_repository, $submodule_service);   //for testing exceptions
		$this->authorization_service2 = new AuthorizationService($personnel_exists, $role_repository, $submodule_service);		// for testing existing personnel
		$this->authorization_service3 = new AuthorizationService($can_create, $can_crud_role, $submodule_service);				// for testing crud
		$this->authorization_service4 = new AuthorizationService($can_create, $cannot_crud_role, $submodule_service);				// for testing cannot crud

	}
	


	// ************** Testing Can CRUD ************** //



	public function testIfCanCreateReturnsTrue () {

		$check_returns_true = $this->authorization_service3->canCreate(1,1);

		$this->assertTrue($check_returns_true);
	}

	public function testIfCanViewReturnsTrue() {

		$check_returns_true = $this->authorization_service3->canView(1,1);

		$this->assertTrue($check_returns_true);
	}

	public function testIfCanUpdateReturnsTrue() {

		$check_returns_true = $this->authorization_service3->canUpdate(1,1);

		$this->assertTrue($check_returns_true);
	}

	public function testIfCanDeleteReturnsTrue() {

		$check_returns_true = $this->authorization_service3->canDelete(1,1);

		$this->assertTrue($check_returns_true);
	}



	// ************** Testing Cannot CRUD ************** //



	public function testIfCanCreateReturnsFalse() {

		$check_returns_false = $this->authorization_service4->canCreate(2,2);

		$this->assertFalse($check_returns_false);
	}

	public function testIfCanViewReturnsFalse() {

		$check_returns_false = $this->authorization_service3->canView(2,2);

		$this->assertFalse($check_returns_false);
	}


	public function testIfCanUpdateReturnsFalse() {

		$check_returns_false = $this->authorization_service4->canUpdate(2,2);

		$this->assertFalse($check_returns_false);
	}


	public function testIfCanDeleteReturnsFalse() {

		$check_returns_false = $this->authorization_service4->canDelete(2,2);

		$this->assertFalse($check_returns_false);
	}



	// ************** Testing Exceptions ************** //




	public function testCanViewThrowsExceptionIfCannotFindPersonnelId () {


		$this->expectException(PersonnelNotFoundException::class);

        $check_can_view = $this->authorization_service->canView(1, 1);
        
	}

	public function testCanCreateThrowsExceptionIfCannotFindPersonnelId () {

		$this->expectException(PersonnelNotFoundException::class);

		$this->authorization_service->canCreate(1,1);


	}

	public function testCanUpdateThrowsExceptiopIfCannotFindPersonnelId() {

		$this->expectException(PersonnelNotFoundException::class);

		$this->authorization_service->canUpdate(1,1);
	}

	public function testCanDeleteThrowsExceptiopIfCannotFindPersonnelId() {

		$this->expectException(PersonnelNotFoundException::class);

		$this->authorization_service->canDelete(1,1);
	
	}


	// ************** Testing Existing Personnel ************** //



	public function testCanViewThrowsExceptionIfCannotFindRoleId() {

		$this->expectException(RoleNotFoundException::class);

        $check_can_view = $this->authorization_service2->canView(1, 1);

	}


	public function testCanCreateThrowsExceptionIfCannotFindRoleId () {

		$this->expectException(RoleNotFoundException::class);

		$check_can_view = $this->authorization_service2->canCreate(1,1);
	}


	public function testCanUpdateThrowsExceptiopIfCannotFindRoleId () {

		$this->expectException(RoleNotFoundException::class);

		$check_can_view = $this->authorization_service2->canUpdate(1,1); 
	}


	public function testCanDeleteThrowsExceptiopIfCannotFindRoleId() {

		$this->expectException(RoleNotFoundException::class);

		$check_can_view = $this->authorization_service2->canDelete(1,1);


	}

}

?>
















