<?php

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
	private DepartmentAccessResolver $department_access_resolver1;
	private DepartmentAccessResolver $department_access_resolver2;
	private DepartmentAccessResolver $department_access_resolver3;
	private DepartmentAccessResolver $department_access_resolver4;
	private DepartmentAccessResolver $department_access_resolver5;
	private DepartmentAccessResolver $department_access_resolver6;
	private DepartmentAccessResolver $department_access_resolver7;
	private DepartmentAccessResolver $department_access_resolver8;

	private $department;
	private $department2;
	private $department3;
	private $department4;
	private $department5;
	private $department6;

	protected function setUp() : void {

		// testIf_Member_Can_Access_Upper_Department
		
		$this->department = new Department(new DepartmentId(1), 'first_dep', null, null, 1, 1);
		$this->department2 = new Department(new DepartmentId(2), 'first_dep', new DepartmentId(1), null, 0, 1);
		$mock_department_repo1 = $this->createMock(IDepartmentRepository::class);
		$mock_department_repo1->method('findDepartment')->willReturn($this->department2);

		// testIf_Member_Cannot_Access_Upper_Department_If_Not_Permitted

		$this->department3 = new Department(new DepartmentId(3), 'sec_dep', null, null, 1, 1);
		$this->department4 = new Department(new DepartmentId(4), 'sec_dep', new DepartmentId(3), null, 0, 0);
		$mock_department_repo2 = $this->createMock(IDepartmentRepository::class);
		$mock_department_repo2->method('findDepartment')->willReturn($this->department4);

		// testIf_Return_False_If_Personnel_DepartmentId_Is_Null

		$this->department5 = new Department(new DepartmentId(5), 'third_dep', null, null, 1, 1);
		$this->department6 = new Department(new DepartmentId(6), 'third_dep', new DepartmentId(5), null, 0, 0);
		$mock_department_repo3 = $this->createMock(IDepartmentRepository::class);
		$mock_department_repo3->method('findDepartment')->willReturn($this->department6); 


	$this->department_access_resolver = new DepartmentAccessResolver($mock_department_repo1);
	$this->department_access_resolver1 = new DepartmentAccessResolver($mock_department_repo2);
	$this->department_access_resolver2 = new DepartmentAccessResolver($mock_department_repo3);


	}

	public function testIf_Member_Can_Access_Upper_Department() {

		$personnel = new Personnel(new PersonnelId(1), new RoleId(1), new DepartmentId(1), true, null,'john', 'doe', '11223344556', 'female', '0049224591432', 'johndoe@mail.com', null, null);

		$check_can_access = $this->department_access_resolver->canAccess($personnel, $this->department);

		$this->assertTrue($check_can_access);
	}

	public function testIf_Member_Cannot_Access_Upper_Department_If_Not_Permitted() {

		$personnel = new Personnel(new PersonnelId(2), new RoleId(1), new DepartmentId(1), true, null,'john', 'doe', '11223344556', 'female', '0049224591432', 'johndoe@mail.com', null, null);

		$check_cannot_access = $this->department_access_resolver1->canAccess($personnel, $this->department3);

		$this->assertFalse($check_cannot_access);
	}

	public function testIf_Returns_False_If_Personnel_DepartmentId_Is_Null() {

		$personnel = new Personnel(new PersonnelId(3), new RoleId(1), null, true, null,'john', 'doe', '11223344556', 'female', '0049224591432', 'johndoe@mail.com', null, null);

		$check_false = $this->department_access_resolver2->canAccess($personnel, $this->department5);

		$this->assertFalse($check_false);
	}
  
	public function testIf_Director_Can_Access_Upper_Department() {

		$department7 = new Department(new DepartmentId(7), 'fourth_dep', null, null, 0, 1);
		$department8 = new Department(new DepartmentId(8), 'fourth_dep', new DepartmentId(7), new PersonnelId(4), 1, 0);
		$mock_department_repo4 = $this->createMock(IDepartmentRepository::class);
		$mock_department_repo4->method('findDepartment')->willReturn($department8);

		$this->department_access_resolver3 = new DepartmentAccessResolver($mock_department_repo4);

		$personnel = new Personnel(new PersonnelId(4), new RoleId(1), new DepartmentId(1), true, null,'john', 'doe', '11223344556', 'female', '0049224591432', 'johndoe@mail.com', null, null);

		$check_director_access = $this->department_access_resolver3->canAccess($personnel, $department7);

		$this->assertTrue($check_director_access);

	}


	public function testIf_Director_Cannot_Access_Upper_Department_If_Not_Permitted(){

		$department9 = new Department(new DepartmentId(9), 'fifth_dep', null, null, 0, 1);
		$department10 = new Department(new DepartmentId(10), 'fifth_dep', new DepartmentId(9), new PersonnelId(5), 0,1);	
		$mock_department_repo5 = $this->createMock(IDepartmentRepository::class);
		$mock_department_repo5->method('findDepartment')->willReturn($department10);

		$this->department_access_resolver4 = new DepartmentAccessResolver($mock_department_repo5);

		$personnel = new Personnel(new PersonnelId(5), new RoleId(1), new DepartmentId(1), true, null,'john', 'doe', '11223344556', 'female', '0049224591432', 'johndoe@mail.com', null, null);

		$check_director_fail = $this->department_access_resolver4->canAccess($personnel, $department9);

		$this->assertFalse($check_director_fail);

	}

	public function testReturns_False_If_Cannot_Find_Department(){ 

		$department11 = new Department(new DepartmentId(10), 'sixth', null, null, 0, 1);
		$mock_department_repo6 = $this->createMock(IDepartmentRepository::class);
		$mock_department_repo6->method('findDepartment')->willReturn(null);

		$this->department_access_resolver5 = new DepartmentAccessResolver($mock_department_repo6);

		$personnel = new Personnel(new PersonnelId(6), new RoleId(1), new DepartmentId(1), true, null,'john', 'doe', '11223344556', 'female', '0049224591432', 'johndoe@mail.com', null, null);
		$check = $this->department_access_resolver5->canAccess($personnel, $department11);

		$this->assertFalse($check);

	}

	public function testIf_Member_CanAccess_To_Their_Own_Department() { 

		$department13 = new Department(new DepartmentId(13), 'seventh', null, null, 0, 0);
		$mock_department_repo7 = $this->createMock(IDepartmentRepository::class);
		$mock_department_repo7->method('findDepartment')->willReturn($department13);

		$this->department_access_resolver6 = new DepartmentAccessResolver($mock_department_repo7);

		$personnel = new Personnel(new PersonnelId(7), new RoleId(1), new DepartmentId(13), true, null,'john', 'doe', '11223344556', 'female', '0049224591432', 'johndoe@mail.com', null, null);
		
		$check = $this->department_access_resolver6->canAccess($personnel, $department13);

		$this->assertTrue($check);

	}

	public function testIf_Member_Can_Access_2_Upper_Department() {


		$personnel = new Personnel(new PersonnelId(1), new RoleId(1), new DepartmentId(15), true, null,'john', 'doe', '11223344556', 'female', '0049224591432', 'johndoe@mail.com', null, null);

		$personnel2 = new Personnel(new PersonnelId(2), new RoleId(1), new DepartmentId(16), true, null,'john', 'doe', '11223344556', 'female', '0049224591432', 'johndoe@mail.com', null, null);

		$department14 = new Department(new DepartmentId(14), 'eighth', null, null, 0, 1);
		$department15 = new Department(new DepartmentId(15), 'eighth', new DepartmentId(14), new PersonnelId(1), 0,1);	
		$department16 = new Department(new DepartmentId(16), 'eighth', new DepartmentId(15), null, 0,2);	


		$mock_department_repo8 = $this->createStub(IDepartmentRepository::class);
		$mock_department_repo8->method('findDepartment')->will(

			$this->returnCallback(function($dep) use ($department14, $department15, $department16) {

				$department_id = $dep->getId();

				if($department_id == 14) {

					return $department14;
				}

				elseif($department_id == 15) {

					return $department15;
				}

				elseif($department_id == 16) {

					return $department16;
				}

				else { return null; }
			}
		));


		$this->department_access_resolver7 = new DepartmentAccessResolver($mock_department_repo8);

		$check_two_above = $this->department_access_resolver7->canAccess($personnel2, $department14);
		$this->assertTrue($check_two_above);


	}

	public function testIf_Parent_Department_Can_Access_Child () {

		$department = new Department(new DepartmentId(7), 'reverse', null, null, 0, 1);
		$department2 = new Department(new DepartmentId(8), 'reserve', new DepartmentId(7), new PersonnelId(4), 1, 0);
		$mock_department_repo = $this->createMock(IDepartmentRepository::class);
		$mock_department_repo->method('findDepartment')->willReturn($department);

		$this->department_access_resolver8 = new DepartmentAccessResolver($mock_department_repo);

		$personnel = new Personnel(new PersonnelId(4), new RoleId(1), new DepartmentId(1), true, null,'john', 'doe', '11223344556', 'female', '0049224591432', 'johndoe@mail.com', null, null);

		$check_director_access = $this->department_access_resolver8->canAccess($personnel, $department2);

		$this->assertTrue($check_director_access);
	}

}

?>