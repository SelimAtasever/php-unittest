<?php

use \model\IdentityAndAccess\application\DepartmentService;

use \model\IdentityAndAccess\domain\model\Department;
use \model\IdentityAndAccess\domain\model\DepartmentId;
use \model\IdentityAndAccess\domain\model\Personnel;
use \model\IdentityAndAccess\domain\model\PersonnelId;
use \model\IdentityAndAccess\domain\model\RoleId;

use \model\IdentityAndAccess\domain\model\IDepartmentRepository;
use \model\IdentityAndAccess\domain\model\IPersonnelRepository;
use \model\IdentityAndAccess\application\IImageDirectAccessLinkProvider;

use \model\IdentityAndAccess\application\exception\DepartmentNotFoundException;
use \model\IdentityAndAccess\application\exception\PersonnelNotFoundException;

use PHPUnit\Framework\TestCase;


class DepartmentServiceTest extends TestCase{

	private DepartmentService $department_service;
	private DepartmentService $department_service_alter;

	protected function setUp() : void

	{
		$personnel = new Personnel(
			new PersonnelId(1), 
			new RoleId(1), 
			new DepartmentId(1),
			true, 
			null,
			'firstname', 
			'lastname', 
			'12345678901', 
			'male',
			'+90 606041204', 
			'sample@domain.com', 
			null, 
			null
		);

		$department = new Department(
			new DepartmentId(1), 
			'first_dep', 
			null, 
			null, 
			1, 
			1,
			1,
			1
		);

		$department_repository = $this->createMock(IDepartmentRepository::class);
		$department_repository->method('findDepartment')->willReturn($department);

		$department_repository_null = $this->createMock(IDepartmentRepository::class);
		$department_repository_null->method('findDepartment')->willReturn(null);

		$personnel_repository = $this->createMock(IPersonnelRepository::class);
		$personnel_repository->method('findById')->willReturn($personnel);

		$personnel_repository_null = $this->createMock(IPersonnelRepository::class);
		$personnel_repository_null->method('findById')->willReturn(null);

		$image_access_link_provider = $this->createMock(IImageDirectAccessLinkProvider::class);
		$image_access_link_provider->method('getLink')->willReturn('path as string.....');


		$this->department_service = new DepartmentService(
			$department_repository_null, $personnel_repository, $image_access_link_provider);

		$this->department_service_alter = new DepartmentService(
			$department_repository, $personnel_repository_null, $image_access_link_provider);

	}

	public function test_assignDepartment_Throws_Exception_If_Department_Isnt_Found(){

		$this->expectException(DepartmentNotFoundException::class);	

		$this->department_service->assignDepartment(1,1);

	}

	public function test_batchUpdateDepartment_Throws_Exception_If_Department_Isnt_Found(){

		$this->expectException(DepartmentNotFoundException::class);

		$this->department_service->batchUpdateDepartment(1, 'name', null, 1);
	}

	public function test_getDepartment_Throws_Exception_If_Department_Isnt_Found(){

		$this->expectException(DepartmentNotFoundException::class);

		$this->department_service->getDepartment(1);
	}

	public function test_personnelCanAccessPersonnel_Throws_Exception_If_Personnel_Isnt_Found(){

		$this->expectException(PersonnelNotFoundException::class);

		$this->department_service_alter->personnelCanAccessPersonnel(1,1);
	}

	public function test_getDepartmentPersonnels_Throws_Exception_If_Department_Isnt_Found(){

		$this->expectException(DepartmentNotFoundException::class);

		$this->department_service->getDepartmentPersonnels(1);
	}

	public function test_getDepartmentDirector_Throws_Exception_If_Department_Isnt_Found(){

		$this->expectException(DepartmentNotFoundException::class);

		$this->department_service->getDepartmentDirector(1);
	}
}

?>