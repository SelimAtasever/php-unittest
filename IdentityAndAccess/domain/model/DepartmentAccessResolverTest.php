cp<?php

use \model\IdentityAndAccess\domain\model\DepartmentAccessResolver;
use \model\IdentityAndAccess\domain\model\IDepartmentRepository;
use \model\IdentityAndAccess\domain\model\Personnel;
use \model\IdentityAndAccess\domain\model\PersonnelId;
use \model\IdentityAndAccess\domain\model\Department;
use \model\IdentityAndAccess\domain\model\DepartmentId;
use \model\IdentityAndAccess\domain\model\RoleId;

use PHPUnit\Framework\TestCase;

class DepartmentAccessResolverTest extends TestCase {
		
	private DepartmentAccessResolver $department_access_resolver;


	public function testIf_Member_Can_Access_Upper_Department() { 

		$parent_personnel = new Personnel(
			new PersonnelId(1), 
			new RoleId(1), 
			new DepartmentId(1), 
			true, 
			null,
			'parent', 
			'doe', 
			'11223344556', 
			'female', 
			'0049224591432', 
			'johndoe@mail.com', 
			null, 
			null
		);

		$child_personnel = new Personnel(
			new PersonnelId(2), 
			new RoleId(2), 
			new DepartmentId(2), 
			true, 
			null,
			'child', 
			'doe', 
			'11223344556', 
			'female', 
			'0049224194460', 
			'marydoe@mail.com', 
			null, 
			null
		);
		
		$parent_department = new Department(new DepartmentId(1), 'upper_dep', null,null, 0,0,0,0);
		$child_department = new Department(new DepartmentId(2), 'child dep', new DepartmentId(1),null, 0,0,1,0);

		$department_repository = $this->createMock(IDepartmentRepository::class);
		$department_repository->method('findDepartment')->willReturnCallback(
			function($dep) use ($child_department, $parent_department){

				$department_id = $dep->getId();
				if($department_id == 1)
					return $parent_department;
				
				else if($department_id == 2)
					return $child_department;
			}
		);

		$department_access_resolver = new DepartmentAccessResolver($department_repository);
		$confirm_can_access = $department_access_resolver->canAccess($child_personnel, $parent_personnel);

		$this->assertTrue($confirm_can_access);
		
	}


	public function testIf_Member_Cannot_Access_Upper_Department_If_Not_Permitted() {

		$parent_personnel = new Personnel(
			new PersonnelId(1), 
			new RoleId(1), 
			new DepartmentId(1), 
			true, 
			null,
			'parent', 
			'doe', 
			'11223344556', 
			'female', 
			'0049224591432', 
			'johndoe@mail.com', 
			null, 
			null
		);

		$child_personnel = new Personnel(
			new PersonnelId(2), 
			new RoleId(2), 
			new DepartmentId(2), 
			true, 
			null,
			'child', 
			'doe', 
			'11223344556', 
			'female', 
			'0049224194460', 
			'marydoe@mail.com', 
			null, 
			null
		);
		
		$parent_department = new Department(new DepartmentId(1), 'upper_dep', null,null, 0,0,0,0);
		$child_department = new Department(new DepartmentId(2), 'child dep', new DepartmentId(1),null, 0,0,0,0);
			// third 0 is the member parent depth, should have been 1 in order to access, this will be false.

		$department_repository = $this->createMock(IDepartmentRepository::class);
		$department_repository->method('findDepartment')->willReturnCallback(
			function($dep) use ($child_department, $parent_department){

				$department_id = $dep->getId();
				if($department_id == 1)
					return $parent_department;
				
				else if($department_id == 2)
					return $child_department;
			}
		);

		$department_access_resolver = new DepartmentAccessResolver($department_repository);
		$confirm_can_access = $department_access_resolver->canAccess($child_personnel, $parent_personnel);

		$this->assertFalse($confirm_can_access);
	}


	public function test_canAccess_Returns_False_If_Child_Departments_Parent_Department_Id_Is_Null() {

		$parent_personnel = new Personnel(
			new PersonnelId(1), 
			new RoleId(1), 
			new DepartmentId(1), 
			true, 
			null,
			'parent', 
			'doe', 
			'11223344556', 
			'female', 
			'0049224591432', 
			'johndoe@mail.com', 
			null, 
			null
		);

		$child_personnel = new Personnel(
			new PersonnelId(2), 
			new RoleId(2), 
			new DepartmentId(2), 
			true, 
			null,
			'child', 
			'doe', 
			'11223344556', 
			'female', 
			'0049224194460', 
			'marydoe@mail.com', 
			null, 
			null
		);
		
		$parent_department = new Department(new DepartmentId(1), 'upper_dep', null,null, 0,0,0,0);
		$child_department = new Department(new DepartmentId(2), 'child dep', null,null, 0,0,1,0);

		$department_repository = $this->createMock(IDepartmentRepository::class);
		$department_repository->method('findDepartment')->willReturnCallback(
			function($dep) use ($child_department, $parent_department){

				$department_id = $dep->getId();
				if($department_id == 1)
					return $parent_department;
				
				else if($department_id == 2)
					return $child_department;
			}
		);

		$department_access_resolver = new DepartmentAccessResolver($department_repository);
		$confirm_can_access = $department_access_resolver->canAccess($child_personnel, $parent_personnel);

		$this->assertFalse($confirm_can_access);
	}

  
	public function testIf_Director_Can_Access_Upper_Department() {
		
		$parent_personnel = new Personnel(
			new PersonnelId(1), 
			new RoleId(1), 
			new DepartmentId(1), 
			true, 
			null,
			'parent', 
			'doe', 
			'11223344556', 
			'female', 
			'0049224591432', 
			'johndoe@mail.com', 
			null, 
			null
		);

		$child_personnel = new Personnel(
			new PersonnelId(2), 
			new RoleId(2), 
			new DepartmentId(2), 
			true, 
			null,
			'child', 
			'doe', 
			'11223344556', 
			'female', 
			'0049224194460', 
			'marydoe@mail.com', 
			null, 
			null
		);

		$parent_department = new Department(new DepartmentId(1), 'upper_dep', null,null, 0,0,0,0);
		$child_department = new Department(new DepartmentId(2), 'child dep_1', new DepartmentId(1),null, 1,0,0,0);

		$department_repository = $this->createMock(IDepartmentRepository::class);
		$department_repository->method('findDepartment')->willReturnCallback(
			function($dep) use ($child_department, $parent_department){

				$department_id = $dep->getId();
				if($department_id == 1)
					return $parent_department;
				
				else if($department_id == 2)
					return $child_department;
			}
		);


		$this->department_access_resolver = new DepartmentAccessResolver($department_repository);
		$child_department->assignDirector($child_personnel);

		$check_director_access = $this->department_access_resolver->canAccess($child_personnel, $parent_personnel);
		$this->assertTrue($check_director_access);

	}


	public function testReturns_False_If_Cannot_Find_Department(){ 

		$parent_personnel = new Personnel(new PersonnelId(1), new RoleId(1), new DepartmentId(1), true, null,'john', 'doe', '11223344556', 'female', '0049224591432', 'johndoe@mail.com', null, null);

		$child_personnel = new Personnel(new PersonnelId(2), new RoleId(1), new DepartmentId(2), true, null,'john', 'doe', '11223344556', 'female', '0049224591432', 'johndoe@mail.com', null, null);

		$parent_dep = new Department(new DepartmentId(1), 'sixth', null, null, 0, 1,0,0);
		$child_dep = new Department(new DepartmentId(2), 'sixth',new DepartmentId(1), null, 0, 1,0,0);

		$department_repository = $this->createMock(IDepartmentRepository::class);
		$department_repository->method('findDepartment')->willReturn(null);

		$this->department_access_resolver = new DepartmentAccessResolver($department_repository);

		$confirm_returns_false = $this->department_access_resolver->canAccess($child_personnel, $parent_personnel);
		$this->assertFalse($confirm_returns_false);

	}

	public function test_If_Members_CanAccess_To_Their_Own_Department() { 

		
		$member_1 = new Personnel(new PersonnelId(1), new RoleId(1), new DepartmentId(1), true, null,'john', 'doe', '11223344556', 'female', '0049224591432', 'johndoe@mail.com', null, null);

		$member_2 = new Personnel(new PersonnelId(2), new RoleId(2), new DepartmentId(1), true, null,'john', 'doe', '11223344556', 'female', '0049224591432', 'johndoe@mail.com', null, null);

		$department = new Department(new DepartmentId(1), 'sixth', null, null, 0,0,0,0);

		$department_repository = $this->createMock(IDepartmentRepository::class);
		$department_repository->method('findDepartment')->willReturn($department);

		$this->department_access_resolver = new DepartmentAccessResolver($department_repository);

		$confirm_returns_true = $this->department_access_resolver->canAccess($member_1, $member_2);

		$this->assertTrue($confirm_returns_true);

	}

	public function testIf_Member_Can_Access_Two_Upper_Parent_Member() {


		$parent_personnel = new Personnel(new PersonnelId(1), new RoleId(1), new DepartmentId(1), true, null,'john', 'doe', '11223344556', 'female', '0049224591432', 'johndoe@mail.com', null, null);

		$child_personnel_1 = new Personnel(new PersonnelId(2), new RoleId(1), new DepartmentId(2), true, null,'john', 'doe', '11223344556', 'female', '0049224591432', 'johndoe@mail.com', null, null);

		$child_personnel_2 = new Personnel(new PersonnelId(3), new RoleId(1), new DepartmentId(3), true, null,'john', 'doe', '11223344556', 'female', '0049224591432', 'johndoe@mail.com', null, null);

		$parent_department = new Department(new DepartmentId(1), 'parent_dep', null, null, 0, 0, 0, 0);
		$child_department_1 = new Department(new DepartmentId(2), 'child_dep_1', new DepartmentId(1), new PersonnelId(1), 0,1,0,0);	
		$child_department_2 = new Department(new DepartmentId(3), 'child_dep_2', new DepartmentId(2), null, 0,0,2,0);	


		$department_repository = $this->createStub(IDepartmentRepository::class);
		$department_repository->method('findDepartment')->will(

			$this->returnCallback(function($dep) use ($parent_department, $child_department_1, $child_department_2) {

				$department_id = $dep->getId();

				if($department_id == 1) {

					return $parent_department;
				}

				elseif($department_id == 2) {

					return $child_department_1;
				}

				elseif($department_id == 3) {

					return $child_department_2;
				}

				else { return null; }
			}
		));

		$this->department_access_resolver = new DepartmentAccessResolver($department_repository);

		$check_can_access_two_above = $this->department_access_resolver->canAccess($child_personnel_2, $parent_personnel);
		$this->assertTrue($check_can_access_two_above);
	}


	public function testIf_Director_Can_Access_Two_Upper_Parent_Member() {


		$parent_personnel = new Personnel(new PersonnelId(1), new RoleId(1), new DepartmentId(1), true, null,'john', 'doe', '11223344556', 'female', '0049224591432', 'johndoe@mail.com', null, null);

		$child_personnel_1 = new Personnel(new PersonnelId(2), new RoleId(1), new DepartmentId(2), true, null,'john', 'doe', '11223344556', 'female', '0049224591432', 'johndoe@mail.com', null, null);

		$child_personnel_2 = new Personnel(new PersonnelId(3), new RoleId(1), new DepartmentId(3), true, null,'john', 'doe', '11223344556', 'female', '0049224591432', 'johndoe@mail.com', null, null);

		$parent_department = new Department(new DepartmentId(1), 'parent_dep', null, null, 0, 0, 0, 0);
		$child_department_1 = new Department(new DepartmentId(2), 'child_dep_1', new DepartmentId(1), new PersonnelId(1), 0,1,0,0);	
		$child_department_2 = new Department(new DepartmentId(3), 'child_dep_2', new DepartmentId(2), null, 2,0,0,0);	


		$department_repository = $this->createStub(IDepartmentRepository::class);
		$department_repository->method('findDepartment')->will(

			$this->returnCallback(function($dep) use ($parent_department, $child_department_1, $child_department_2) {

				$department_id = $dep->getId();

				if($department_id == 1) {

					return $parent_department;
				}

				elseif($department_id == 2) {

					return $child_department_1;
				}

				elseif($department_id == 3) {

					return $child_department_2;
				}

				else { return null; }
			}
		));

		$this->department_access_resolver = new DepartmentAccessResolver($department_repository);
		$child_department_2->assignDirector($child_personnel_2);

		$check_can_access_two_above = $this->department_access_resolver->canAccess($child_personnel_2, $parent_personnel);
		$this->assertTrue($check_can_access_two_above);
	}


	public function test_If_Parent_Department_Director_Can_Access_Lower_Department_Member () {

		$parent_personnel = new Personnel(new PersonnelId(1), new RoleId(1), new DepartmentId(1), true, null,'john', 'doe', '11223344556', 'female', '0049224591432', 'johndoe@mail.com', null, null);

		$child_personnel = new Personnel(new PersonnelId(2), new RoleId(1), new DepartmentId(2), true, null,'zoe', 'doe', '11223344556', 'female', '0049224591432', 'johndoe@mail.com', null, null);

		$parent_department = new Department(new DepartmentId(1), 'parent_dep', null, null, 0, 1, 0, 0);
		$child_department = new Department(new DepartmentId(2), 'child_dep', new DepartmentId(1), null, 0, 0, 0, 0);

		$department_repository = $this->createMock(IDepartmentRepository::class);
		$department_repository->method('findDepartment')->willReturnCallback(

			function ($dep_id) use ($parent_department, $child_department){

				$department_id = $dep_id->getId();
				
				if($department_id == 1)
					return $parent_department;

				else if($department_id ==2)
					return $child_department;
			}
		);

		$this->department_access_resolver = new DepartmentAccessResolver($department_repository);
		$parent_department->assignDirector($parent_personnel);

		$confirm_can_access = $this->department_access_resolver->canAccess($parent_personnel, $child_personnel);
		$this->assertTrue($confirm_can_access);
	
	}

	public function test_If_Parent_Department_Member_Can_Access_Two_Lower_Department_Members(){

		$parent_personnel_2 = new Personnel(
			new PersonnelId(1), 
			new RoleId(1), 
			new DepartmentId(1), 
			true, 
			null,
			'john', 
			'doe', 
			'11223344556', 
			'female', 
			'0049224591432', 
			'johndoe@mail.com', 
			null, 
			null
		);

		$parent_personnel_1 = new Personnel(
			new PersonnelId(2), 
			new RoleId(1), 
			new DepartmentId(2), 
			true, 
			null,
			'john', 
			'doe', 
			'11223344556', 
			'female', 
			'0049224591432', 
			'johndoe@mail.com', 
			null, 
			null
		);

		$child_personnel = new Personnel(
			new PersonnelId(3), 
			new RoleId(1), 
			new DepartmentId(3), 
			true, 
			null,
			'john', 
			'doe', 
			'11223344556', 
			'female', 
			'0049224591432', 
			'johndoe@mail.com', 
			null, 
			null
		);

		$parent_department_2 = new Department(new DepartmentId(1), 'parent_dep', null, null, 0, 0, 0, 2);
		$parent_department_1 = new Department(new DepartmentId(2), 'child_dep', new DepartmentId(1), null, 0, 0, 0, 0);
		$child_department = new Department(new DepartmentId(3), 'child_dep_2', new DepartmentId(2), null, 0, 0, 0, 0);

		$department_repository = $this->createMock(IDepartmentRepository::class);
		$department_repository->method('findDepartment')->willReturnCallback(
		
			function ($dep_id) use ($parent_department_2, $parent_department_1, $child_department){

				$department_id = $dep_id->getId();

				if($department_id == 1)
					return $parent_department_2;

				else if($department_id == 2)
					return $parent_department_1;

				else if($department_id == 3)
					return $child_department;
			}
		);

		$this->department_access_resolver = new DepartmentAccessResolver($department_repository);
		$confirm_can_access = $this->department_access_resolver->canAccess($parent_personnel_2, $child_personnel);

		$this->assertTrue($confirm_can_access);

	}

	public function test_If_Parent_Department_Member_Can_Access_Lower_Department_Member(){

		$parent_personnel = new Personnel(new PersonnelId(1), new RoleId(1), new DepartmentId(1), true, null,'john', 'doe', '11223344556', 'female', '0049224591432', 'johndoe@mail.com', null, null);

		$child_personnel = new Personnel(new PersonnelId(2), new RoleId(1), new DepartmentId(2), true, null,'zoe', 'doe', '11223344556', 'female', '0049224591432', 'johndoe@mail.com', null, null);

		$parent_department = new Department(new DepartmentId(1), 'parent_dep', null, null, 0, 0, 0, 1);
		$child_department = new Department(new DepartmentId(2), 'child_dep', new DepartmentId(1), null, 0, 0, 0, 0);

		$department_repository = $this->createMock(IDepartmentRepository::class);
		$department_repository->method('findDepartment')->willReturnCallback(

			function ($dep_id) use ($parent_department, $child_department){

				$department_id = $dep_id->getId();
				
				if($department_id == 1)
					return $parent_department;

				else if($department_id ==2)
					return $child_department;
			}
		);

		$this->department_access_resolver = new DepartmentAccessResolver($department_repository);

		$confirm_can_access = $this->department_access_resolver->canAccess($parent_personnel, $child_personnel);
		$this->assertTrue($confirm_can_access);		
	}

	public function test_If_Parent_Department_Director_Can_Access_Two_Lower_Department_Members(){

		$parent_personnel_2 = new Personnel(
			new PersonnelId(1), 
			new RoleId(1), 
			new DepartmentId(1), 
			true, 
			null,
			'john', 
			'doe', 
			'11223344556', 
			'female', 
			'0049224591432', 
			'johndoe@mail.com', 
			null, 
			null
		);

		$parent_personnel_1 = new Personnel(
			new PersonnelId(2), 
			new RoleId(1), 
			new DepartmentId(2), 
			true, 
			null,
			'john', 
			'doe', 
			'11223344556', 
			'female', 
			'0049224591432', 
			'johndoe@mail.com', 
			null, 
			null
		);

		$child_personnel = new Personnel(
			new PersonnelId(3), 
			new RoleId(1), 
			new DepartmentId(3), 
			true, 
			null,
			'john', 
			'doe', 
			'11223344556', 
			'female', 
			'0049224591432', 
			'johndoe@mail.com', 
			null, 
			null
		);

		$parent_department_2 = new Department(new DepartmentId(1), 'parent_dep', null, null, 0, 2, 0, 0);
		$parent_department_1 = new Department(new DepartmentId(2), 'child_dep', new DepartmentId(1), null, 0, 0, 0, 0);
		$child_department = new Department(new DepartmentId(3), 'child_dep_2', new DepartmentId(2), null, 0, 0, 0, 0);

		$department_repository = $this->createMock(IDepartmentRepository::class);
		$department_repository->method('findDepartment')->willReturnCallback(
		
			function ($dep_id) use ($parent_department_2, $parent_department_1, $child_department){

				$department_id = $dep_id->getId();

				if($department_id == 1)
					return $parent_department_2;

				else if($department_id == 2)
					return $parent_department_1;

				else if($department_id == 3)
					return $child_department;
			}
		);

		$this->department_access_resolver = new DepartmentAccessResolver($department_repository);
		$parent_department_2->assignDirector($parent_personnel_2);

		$confirm_can_access = $this->department_access_resolver->canAccess($parent_personnel_2, $child_personnel);
		$this->assertTrue($confirm_can_access);

	}
} 

?>