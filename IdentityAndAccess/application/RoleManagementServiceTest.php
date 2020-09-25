<?php

use \model\IdentityAndAccess\application\RoleManagementService;
use \model\IdentityAndAccess\domain\model\exception\RoleNameIsNotUniqueException;
use \model\IdentityAndAccess\domain\model\PersonnelId;
use \model\IdentityAndAccess\domain\model\RoleId;
use \model\IdentityAndAccess\domain\model\IPersonnelRepository;
use \model\IdentityAndAccess\domain\model\IRoleRepository;
use \model\IdentityAndAccess\domain\model\Role;
use \model\IdentityAndAccess\domain\model\Personnel;
use \model\IdentityAndAccess\domain\model\Privilege;
use \model\IdentityAndAccess\application\exception\PersonnelNotFoundException;
use \model\IdentityAndAccess\application\exception\RoleNotFoundException;
use \model\IdentityAndAccess\application\DTO\RoleDTO;
use \model\IdentityAndAccess\application\DTO\RoleQueryDTO;
use \model\IdentityAndAccess\infrastructure\PersonnelRepository;
use \model\IdentityAndAccess\infrastructure\RoleRepository;
use \model\common\application\SubmoduleService;

use PHPUnit\Framework\TestCase;


class RoleManagementServiceTest extends TestCase {


	private RoleManagementService $role_management_service;
	private $role_dto_correct;


	protected function setUp() : void {


		$personnel_repository = $this->createMock(IPersonnelRepository::class);
		$personnel_repository_2 = $this->createMock(IPersonnelRepository::class);

		$personnel = new Personnel(new PersonnelId(1), new RoleId(1),true, 'jon', 'snow', '11223344556', 'male', '0049224591432', 'jon-snow@mail.com', null, null);
		$personnel_2 = new Personnel(new PersonnelId(1), null ,true, 'jon', 'snow', '11223344556', 'male', '0049224591432', 'jon-snow@mail.com', null, null);
		$personnel_repository->method('findById')->willReturn($personnel);
		$personnel_repository_2->method('findById')->willReturn($personnel_2);

		$role = new Role(new RoleId(1), 'mock_role');
		$this->role_dto_correct = RoleDTO::fromRole($role); 

		$role_repository = $this->createMock(IRoleRepository::class);
		$role_repository->method('findById')->willReturn($role);


		$submodule_service = $this->createMock(SubmoduleService::class);
		$submodule_service->method('getById')->willReturn(null);


		$this->role_management_service = new RoleManagementService($personnel_repository, $role_repository, $submodule_service);
		$this->role_management_service_2 = new RoleManagementService($personnel_repository_2, $role_repository, $submodule_service);


	}


	public function testGetRoleReturnsRoleDTO(){

		$role_dto_check = $this->role_management_service->getRole(1);

		$this->assertEquals($role_dto_check, $this->role_dto_correct);


	}

	public function testGetPersonnelRoleReturnsRoleDTO() {

		$personnel_role_check = $this->role_management_service->getPersonnelRole(1); //personnel-id

		$this->assertEquals($personnel_role_check, $this->role_dto_correct);
	}


	public function testGetPersonnelRoleReturnsNullIfRoleIdIsNull() {

		$personnel_role_check = $this->role_management_service_2->getPersonnelRole(1);

		$this->assertEmpty($personnel_role_check);
	
	}


}


?>