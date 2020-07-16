<?php

use \model\IdentityAndAccess\application\IdentityService;
use \model\IdentityAndAccess\domain\model\PersonnelId;
use \model\IdentityAndAccess\domain\model\RoleId;
use \model\IdentityAndAccess\domain\model\IPersonnelRepository;
use \model\IdentityAndAccess\domain\model\IRoleRepository;
use \model\IdentityAndAccess\domain\model\Personnel;
use \model\IdentityAndAccess\domain\model\Role;
use \model\IdentityAndAccess\domain\model\PersonnelDomainService;
use \model\IdentityAndAccess\application\exception\PersonnelNotFoundException;
use \model\IdentityAndAccess\application\exception\RoleNotFoundException;
use \model\IdentityAndAccess\application\DTO\PersonnelDTO;
use \model\IdentityAndAccess\application\DTO\PersonnelQueryDTO;
use \model\IdentityAndAccess\infrastructure\PersonnelRepository;
use \model\IdentityAndAccess\infrastructure\RoleRepository;


use PHPUnit\Framework\TestCase;


class IdentityServiceTest extends TestCase {
	
	private IdentityService $identity_service;
	private $personnel_dto_correct;

	protected function setUp() : void {

		$personnel_repository = $this->createMock(IPersonnelRepository::class);

		$personnel = new Personnel(new PersonnelId(1), new RoleId(1),true, 'jon', 'snow', '11223344556', 'male', '0049224591432', 'jon-snow@mail.com', null, null);
		$this->personnel_dto_correct = PersonnelDTO::fromPersonnel($personnel); // turned personnel to dto (notnull)
		$this->personnel_phone_correct = PersonnelDTO::fromPersonnel($personnel);


		$personnel_repository->method('findById')->willReturn($personnel);
		$personnel_repository->method('findByEmail')->willReturn(new Personnel(new PersonnelId(1), new RoleId(1),true, 'jon', 'snow', '11223344556', 'male', '0049224591432', 'jon-snow@mail.com', null, null));

		$role_repository = $this->createMock(IRoleRepository::class);
		$role_repository->method('findById')->willReturn(null);

	$this->identity_service = new IdentityService($personnel_repository, $role_repository);

	}

	public function testGetPersonnelReturnsMockedPersonnel() {

		$personnel_dto1 = $this->identity_service->getPersonnel(1);

		$this->assertEquals($personnel_dto1, $this->personnel_dto_correct);
	}

	public function testGetPersonnelByEmailReturnsMockedPersonnel() {

		$personnel_phone_dto = $this->identity_service->getPersonnelByEmail('jon-snow@mail.com');

		$this->assertEquals($personnel_phone_dto, $this->personnel_phone_correct);
	}

}

?>