<?php

use \model\IdentityAndAccess\application\ProfileService;
use \model\IdentityAndAccess\application\IIdentityProvider;
use \model\IdentityAndAccess\domain\model\Personnel;
use \model\IdentityAndAccess\domain\model\PersonnelId;
use \model\IdentityAndAccess\domain\model\RoleId;
use \model\IdentityAndAccess\domain\model\IPersonnelRepository;
use \model\IdentityAndAccess\domain\model\PersonnelDomainService;
use \model\IdentityAndAccess\domain\model\IRoleRepository;
use \model\IdentityAndAccess\application\DTO\PersonnelDTO;
use \model\IdentityAndAccess\domain\model\Role;
use \model\IdentityAndAccess\application\DTO\RoleDTO;
use \model\IdentityAndAccess\application\DTO\DepartmentDTO;
use \model\IdentityAndAccess\domain\model\IDepartmentRepository;
use \model\IdentityAndAccess\domain\model\Department;
use \model\IdentityAndAccess\domain\model\DepartmentId;
use \model\IdentityAndAccess\application\IImageDirectAccessLinkProvider;

use \model\IdentityAndAccess\application\exception\RoleNotFoundException;
use \model\IdentityAndAccess\application\exception\DepartmentNotFoundException;
use \model\IdentityAndAccess\application\exception\PersonnelNotFoundException;

use PHPUnit\Framework\TestCase;


class ProfileServiceTest extends TestCase {

	private ProfileService $profile_service;
	private $personnel_dto_correct;
	private $role_dto_correct;
	private $department_dto_correct;

	protected function setUp() : void {

		$identity_provider = $this->createMock(IIdentityProvider::class);
		$identity_provider->method('identity')->willReturn(15);

		$personnel = new Personnel(new PersonnelId(15), new RoleId(1),new DepartmentId(1), true, null, 'jon', 'snow', '11223344556', 'male', '0049224591432', 'jon-snow@mail.com', null, null);
		$this->personnel_dto_correct = PersonnelDTO::fromPersonnel($personnel,null); 

		$personnel_repository = $this->createStub(IPersonnelRepository::class);
		$personnel_repository->expects($this->any())->method('findById')->will(

			$this->returnCallback(function($identity) use ($personnel) { 

				$id = $identity->getId();

				if($id == 15) {
					return $personnel;
				}

				else{ return null; }

			}));

		$new_role = new Role(new RoleId(1), 'created_role');
		$this->role_dto_correct = RoleDTO::fromRole($new_role);

		$role_repository = $this->createStub(IRoleRepository::class);
		$role_repository->expects($this->any())->method('findById')->will(

			$this->returnCallback(function($role) use ($new_role) {

				$role_id = $role->getId();

				if($role_id == 1) {

					return $new_role;
				}

				else { return null; }
			}
		));

		$department = new Department(new DepartmentId(1), 'name', null, null, 1,1,1,1);
		$this->department_dto_correct = DepartmentDTO::fromDepartment($department);

		$department_repository = $this->createMock(IDepartmentRepository::class);
		$department_repository->method('findDepartment')->willReturn($department);

		$image_access_link_provider = $this->createMock(IImageDirectAccessLinkProvider::class);
		$image_access_link_provider->method('getLink')->willReturn('path as string.....');
		

	$this->profile_service = new ProfileService(
			$personnel_repository, $role_repository, $department_repository, $identity_provider, $image_access_link_provider);

	}

	/******	 TESTS 	******/


	public function test_If_updateSelf_Updating_Personnel(){

		$personnel = new Personnel(new PersonnelId(15), new RoleId(1),new DepartmentId(1), true, null, 'jon', 'snow', '11223344556', 'male', '0049224591432', 'jon-doe@mail.com', null, null);

		$role = new Role(new RoleId(1), 'created_role');

		$department = new Department(new DepartmentId(1), 'name', null, null, 1,1,1,1);

		$personnel_repository = $this->createMock(IPersonnelRepository::class);
		$personnel_repository->method('findById')->willReturn($personnel);

		$role_repository = $this->createMock(IRoleRepository::class);
		$role_repository->method('findById')->willReturn($role);

		$department_repository = $this->createMock(IDepartmentRepository::class);
		$department_repository->method('findDepartment')->willReturn($department);

		$identity_provider = $this->createMock(IIdentityProvider::class);
		$identity_provider->method('identity')->willReturn(1);

		$image_access_link_provider = $this->createMock(IImageDirectAccessLinkProvider::class);
		$image_access_link_provider->method('getLink')->willReturn('path as string...');

		$profile_service = new ProfileService(
			$personnel_repository, $role_repository, $department_repository, $identity_provider, $image_access_link_provider);

		$profile_service->updateSelf('+4212912489412', 'coo@doe.com');

		$updated_phone = $personnel->getPhone();
		$updated_mail = $personnel->getEmail();

		$this->assertNotEquals($updated_phone, '0049224591432');
		$this->assertEquals($updated_phone,'+4212912489412');

		$this->assertNotEquals($updated_mail, 'jon-doe@mail.com');
		$this->assertEquals($updated_mail, 'coo@doe.com');
	}

	public function test_If_getDepartment_Returns_DepartmentDTO_Correctly(){

		$get_department_dto = $this->profile_service->getDepartment();

		$this->assertEquals($get_department_dto, $this->department_dto_correct);
	}


	public function test_If_getSelf_Returns_PersonnelDTO_Correctly() {

		$personnel_dto = $this->profile_service->getSelf();

		$this->assertEquals($personnel_dto, $this->personnel_dto_correct);

	}

	public function test_If_getRole_Returns_RoleDTO_Correctly() {

		$role_dto = $this->profile_service->getRole();

		$this->assertEquals($role_dto, $this->role_dto_correct);
	}

	public function test_If_getSelf_Throws_Exception_If_Personnel_Isnt_Found(){

		$this->expectException(PersonnelNotFoundException::class);

		$department = new Department(new DepartmentId(1), 'name', null, null, 1,1,1,1);

		$role = new Role(new RoleId(1), 'created_role');

		$personnel_repository = $this->createMock(IPersonnelRepository::class);
		$personnel_repository->method('findById')->willReturn(null);

		$role_repository = $this->createMock(IRoleRepository::class);
		$role_repository->method('findById')->willReturn($role);

		$department_repository = $this->createMock(IDepartmentRepository::class);
		$department_repository->method('findDepartment')->willReturn($department);

		$identity_provider = $this->createMock(IIdentityProvider::class);
		$identity_provider->method('identity')->willReturn(1);

		$image_access_link_provider = $this->createMock(IImageDirectAccessLinkProvider::class);
		$image_access_link_provider->method('getLink')->willReturn('path as string...');

		$profile_service = new ProfileService(
			$personnel_repository, $role_repository, $department_repository, $identity_provider, $image_access_link_provider);

		$profile_service->getSelf();

	}


	public function test_If_getRole_Throws_Exceptions_If_Role_Isnt_Found(){ 

		$this->expectException(RoleNotFoundException::class);
		
		$personnel = new Personnel(new PersonnelId(15), new RoleId(1),null, true, null, 'jon', 'snow', '11223344556', 'male', '0049224591432', 'jon-snow@mail.com', null, null);

		$department = new Department(new DepartmentId(1), 'name', null, null, 1,1,1,1);

		$personnel_repository = $this->createMock(IPersonnelRepository::class);
		$personnel_repository->method('findById')->willReturn($personnel);

		$role_repository = $this->createMock(IRoleRepository::class);
		$role_repository->method('findById')->willReturn(null);

		$department_repository = $this->createMock(IDepartmentRepository::class);
		$department_repository->method('findDepartment')->willReturn($department);

		$identity_provider = $this->createMock(IIdentityProvider::class);
		$identity_provider->method('identity')->willReturn(1);

		$image_access_link_provider = $this->createMock(IImageDirectAccessLinkProvider::class);
		$image_access_link_provider->method('getLink')->willReturn('path as string...');

		$profile_service = new ProfileService(
			$personnel_repository, $role_repository, $department_repository, $identity_provider, $image_access_link_provider);

		$profile_service->getRole();
	}

	public function test_If_getDepartment_Throws_Exceptions_If_Department_Isnt_Found(){

		$this->expectException(DepartmentNotFoundException::class);
		
		$personnel = new Personnel(new PersonnelId(15), new RoleId(1),new DepartmentId(1), true, null, 'jon', 'snow', '11223344556', 'male', '0049224591432', 'jon-snow@mail.com', null, null);

		$role = new Role(new RoleId(1), 'created_role');

		$personnel_repository = $this->createMock(IPersonnelRepository::class);
		$personnel_repository->method('findById')->willReturn($personnel);

		$role_repository = $this->createMock(IRoleRepository::class);
		$role_repository->method('findById')->willReturn($role);

		$department_repository = $this->createMock(IDepartmentRepository::class);
		$department_repository->method('findDepartment')->willReturn(null);

		$identity_provider = $this->createMock(IIdentityProvider::class);
		$identity_provider->method('identity')->willReturn(1);

		$image_access_link_provider = $this->createMock(IImageDirectAccessLinkProvider::class);
		$image_access_link_provider->method('getLink')->willReturn('path as string...');

		$profile_service = new ProfileService(
			$personnel_repository, $role_repository, $department_repository, $identity_provider, $image_access_link_provider);

		$profile_service->getDepartment();
	}
}

?>