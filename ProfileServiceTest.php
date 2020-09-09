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
use \model\IdentityAndAccess\domain\model\IDepartmentRepository;
use \model\IdentityAndAccess\domain\model\Department;
use \model\IdentityAndAccess\domain\model\DepartmentId;
use \model\IdentityAndAccess\application\IImageDirectAccessLinkProvider;

use PHPUnit\Framework\TestCase;


class ProfileServiceTest extends TestCase {

		
	private ProfileService $profile_service;
	private $personnel_dto_correct;
	private $role_dto_correct;

	protected function setUp() : void {

		$mock_identity = $this->createMock(IIdentityProvider::class);
		$mock_identity->method('identity')->willReturn(15);

		$personnel = new Personnel(new PersonnelId(15), new RoleId(1),null, true, null, 'jon', 'snow', '11223344556', 'male', '0049224591432', 'jon-snow@mail.com', null, null);
		$this->personnel_dto_correct = PersonnelDTO::fromPersonnel($personnel,null); 

		$stub_repo = $this->createStub(IPersonnelRepository::class);
		$stub_repo->expects($this->any())->method('findById')->will(

			$this->returnCallback(function($identity) use ($personnel) { 	//anonim fonksiyon yerine blok dısında function tanımlanabilir.

				$id = $identity->getId();

				if($id == 15) {

					return $personnel;
				}

				else{ return null; }

			}));


		$new_role = new Role(new RoleId(1), 'created_role');
		$this->role_dto_correct = RoleDTO::fromRole($new_role);

		$stub_role = $this->createStub(IRoleRepository::class);
		$stub_role->expects($this->any())->method('findById')->will(

			$this->returnCallback(function($role) use ($new_role) {

				$role_id = $role->getId();

				if($role_id == 1) {

					return $new_role;
				}

				else { return null; }
			}
		));

		$department = new Department(new DepartmentId(1), 'name', null, null, 1,1,1,1);

		$mock_dep = $this->createMock(IDepartmentRepository::class);
		$mock_dep->method('findDepartment')->willReturn($department);

		$image_access_link_provider = $this->createMock(IImageDirectAccessLinkProvider::class);
		$image_access_link_provider->method('getLink')->willReturn('path as string.....');
		

	$this->profile_service = new ProfileService($stub_repo, $stub_role, $mock_dep ,$mock_identity, $image_access_link_provider);

	}

	public function test_If_GetSelf_Returns_PersonnelDTO_Correctly() {

		$personnel_dto = $this->profile_service->getSelf();

		$this->assertEquals($personnel_dto, $this->personnel_dto_correct);

	}

	public function test_If_GetRole_Returns_RoleDTO_Correctly() {

		$role_dto = $this->profile_service->getRole();

		$this->assertEquals($role_dto, $this->role_dto_correct);
	}

}

?>