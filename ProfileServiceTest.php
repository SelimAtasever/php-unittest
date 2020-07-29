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


use PHPUnit\Framework\TestCase;


class ProfileServiceTest extends TestCase {

		
	private ProfileService $profile_service;
	private $personnel_dto_correct;
	private $role_dto_correct;

	protected function setUp() : void {

		$mock_identity = $this->createMock(IIdentityProvider::class);
		$mock_identity->method('identity')->willReturn(15);

		$personnel = new Personnel(new PersonnelId(15), new RoleId(1), true, null, 'jon', 'snow', '11223344556', 'male', '0049224591432', 'jon-snow@mail.com', null, null);
		$this->personnel_dto_correct = PersonnelDTO::fromPersonnel($personnel); 

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

		

	$this->profile_service = new ProfileService($stub_repo, $stub_role, $mock_identity);

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