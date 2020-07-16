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

use PHPUnit\Framework\TestCase;


class AuthorizationServiceTest extends TestCase {


	private AuthorizationService $authorization_service;

	protected function setUp() : void {


		$personnel_repository = $this->createMock(IPersonnelRepository::class);
		$personnel_repository->method('findById')->willReturn(null);

		$role_repository = $this->createMock(IRoleRepository::class);
		$role_repository->method('findById')->willReturn(null);

		$submodule_service = $this->createMock(SubmoduleService::class);
		$submodule_service->method('getById')->willReturn(null);

		$personnel_exists = $this->createMock(IPersonnelRepository::class);
		$personnel_exists->method('findById')->willReturn(new Personnel(new PersonnelId(1), new RoleId(1),true, 'john', 'doe', '11223344556', 'female', '0049224591432', 'johndoe@mail.com', null, null));


		$this->authorization_service = new AuthorizationService($personnel_repository, $role_repository, $submodule_service);
		$this->authorization_service2 = new AuthorizationService($personnel_exists, $role_repository, $submodule_service);

	}

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
















