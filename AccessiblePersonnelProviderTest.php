<?php

use model\IdentityAndAccess\domain\model\AccessiblePersonnelProvider;
use model\IdentityAndAccess\domain\model\IDepartmentRepository;
use model\IdentityAndAccess\domain\model\IPersonnelRepository;
use model\IdentityAndAccess\domain\model\Personnel;
use model\IdentityAndAccess\domain\model\PersonnelId;
use model\IdentityAndAccess\domain\model\Department;
use model\IdentityAndAccess\domain\model\DepartmentId;

use PHPUnit\Framework\TestCase;


class AccessiblePersonnelProviderTest extends TestCase{

private AccessiblePersonnelProvider $accesible_personnel_provider;

	
	public function test_If_getAccessiblePersonnels_Returns_Accessible_Child_Personnels(){

		$child_departments_arr = array(new Department(new DepartmentId(1), 'this is default department', new DepartmentId(2), null, 1,1,1,1));
		$lower_department = new Department(new DepartmentId(1), 'LOWER DEPARTMENT', new DepartmentId(2), null, 1,1,1,1);
		$upper_department = new Department(new DepartmentId(2), 'UPPER DEPARTMENT', null, null, 1,1,1,1);


		$lower_personnel_arr = array(new Personnel(new PersonnelId(1), null, new DepartmentId(1), true, null, 'michael', 'corleone','10411223345', 'male', '+4201244109499', 'm-corleone@mail.it', new DateTime(), null)); 

		$upper_personnel = new Personnel(new PersonnelId(2), null, new DepartmentId(2), true, null, 'vito', 'corleone','10111223345', 'male', '+420124410941', 'v-corleone@mail.it', new DateTime(), null);

		$department_stub = $this->createMock(IDepartmentRepository::class);

		$department_stub->method('findDepartment')->will(

			$this->returnCallback(function ($dep) use ($upper_department, $lower_department) {

				$department_id = $dep->getId();
				
				if($department_id == 1) {
					return $lower_department;
				}

				elseif($department_id == 2) {
					return $upper_department;
				}

				else {return null;}

			}
	 	));

	 	$department_stub->method('fetchChildDepartments')->willReturn($child_departments_arr);

		$personnel_mock = $this->createMock(IPersonnelRepository::class);
		$personnel_mock->method('findByDepartmentId')->willReturn($lower_personnel_arr);


		$this->accesible_personnel_provider = new AccessiblePersonnelProvider($department_stub ,$personnel_mock);

		$confirm_returns_personnels = $this->accesible_personnel_provider->getAccessiblePersonnels($upper_personnel);

		$this->assertNotEmpty($confirm_returns_personnels);
	
	}


	public function test_If_getAccessiblePersonnels_Returns_Null_When_DepartmentId_Is_Null(){

		$child_departments_arr = array(new Department(new DepartmentId(1), 'this is default department', new DepartmentId(2), null, 1,1,1,1));

		$lower_department = new Department(new DepartmentId(1), 'LOWER DEPARTMENT', null, null, 1,1,1,1); // lower dep has no parent_id

		$upper_department = new Department(new DepartmentId(2), 'UPPER DEPARTMENT', null, null, 1,1,1,1);

		$lower_personnel = new Personnel(new PersonnelId(1), null, new DepartmentId(1), true, null, 'michael', 'corleone','10411223345', 'male', '+4201244109499', 'm-corleone@mail.it', new DateTime(), null);

		$upper_personnel = new Personnel(new PersonnelId(2), null, new DepartmentId(2), true, null, 'vito', 'corleone','10111223345', 'male', '+420124410941', 'v-corleone@mail.it', new DateTime(), null);

		$department_stub = $this->createMock(IDepartmentRepository::class);

		$department_stub->method('findDepartment')->will(

			$this->returnCallback(function ($dep) use ($upper_department, $lower_department) {

				$department_id = $dep->getId();
				
				if($department_id == 1) {
					return $lower_department;
				}

				elseif($department_id == 2) {
					return $upper_department;
				}

				else {return null;}

			}
	 	));

	 	$department_stub->method('fetchChildDepartments')->willReturn($child_departments_arr);

 
		$personnel_mock = $this->createMock(IPersonnelRepository::class);
		$personnel_mock->method('findById')->willReturn($lower_personnel);


		$this->accesible_personnel_provider = new AccessiblePersonnelProvider($department_stub ,$personnel_mock);

		$confirm_returns_personnels = $this->accesible_personnel_provider->getAccessiblePersonnels($upper_personnel);

		$this->assertEmpty($confirm_returns_personnels);
	
	}

	public function test_If_getAccessiblePersonnels_Returns_Child_Departments_Accessible_Personnels(){

	  $parent_department = new Department(new DepartmentId(1), 'parent department', null, null, 1,1,1,1);
	  $child_department_1 = new Department(new DepartmentId(2), 'child department 1 of 1st degree', new DepartmentId(1), null, 1,1,1,1);

	  $parent_department_personnel = new Personnel(
	   new PersonnelId(1), 
	   null, 
	   new DepartmentId(1), 
	   true, 
	   null, 
	   'vito', 
	   'corleone',
	   '10111223345', 
	   'male', 
	   '+420124410941', 
	   'v-corleone@mail.it', 
	   new DateTime(), 
	   null
	  );

	  $child_department_1_personnels = array(
	   new Personnel(
	   new PersonnelId(2), 
	   null, 
	   new DepartmentId(2), 
	   true, 
	   null, 
	   'michael', 
	   'corleone',
	   '10411223345', 
	   'male', 
	   '+4201244109499', 
	   'm-corleone@mail.it', 
	   new DateTime(), 
	   null
	   )
	  );

	  $department_repository = $this->createMock(IDepartmentRepository::class);
	  $department_repository->method('findDepartment')->willReturn($parent_department);
	   $department_repository->method('fetchChildDepartments')->willReturn(
	   array($child_department_1)
	  );

	  $personnel_repository = $this->createMock(IPersonnelRepository::class);
	  $personnel_repository->method('findByDepartmentId')->willReturnCallback(

	   function(DepartmentId $department_id) use ($parent_department, $child_department_1, $parent_department_personnel, $child_department_1_personnels) {
	    if($department_id->equals($parent_department->id()))
	     return array($parent_department_personnel);
	    else if($department_id->equals($child_department_1->id()))
	     return $child_department_1_personnels;
	   }
	  );

	  $this->accesible_personnel_provider = new AccessiblePersonnelProvider($department_repository ,$personnel_repository);

	  $accessible_personnels = $this->accesible_personnel_provider->getAccessiblePersonnels($parent_department_personnel);

	  $this->assertEquals(2, count($accessible_personnels)); // multi arrays counted
 }

	public function test_If_getAccessiblePersonnels_Cannot_Access_Personnel_Member_Depth_Is_Zero(){ 

		$child_departments_arr = array(new Department(new DepartmentId(1), 'this is default department', new DepartmentId(2), null, 1,1,1,1));
		$lower_department = new Department(new DepartmentId(1), 'LOWER DEPARTMENT', new DepartmentId(2), null, 1,1,1,1);
		$upper_department = new Department(new DepartmentId(2), 'UPPER DEPARTMENT', null, null, 0,0,0,0);


		$lower_personnel_arr = array(new Personnel(new PersonnelId(1), null, new DepartmentId(1), true, null, 'michael', 'corleone','10411223345', 'male', '+4201244109499', 'm-corleone@mail.it', new DateTime(), null));

		$middle_personnel_arr = array(new Personnel(new PersonnelId(1), null, new DepartmentId(1), true, null, 'sonny', 'corleone','19419229345', 'male', '+4201214101199', 's-corleone@mail.it', new DateTime(), null)); 

		$upper_personnel = new Personnel(new PersonnelId(2), null, new DepartmentId(2), true, null, 'vito', 'corleone','10111223345', 'male', '+420124410941', 'v-corleone@mail.it', new DateTime(), null);

		$department_stub = $this->createMock(IDepartmentRepository::class);

		$department_stub->method('findDepartment')->will(

			$this->returnCallback(function ($dep) use ($upper_department, $lower_department) {

				$department_id = $dep->getId();
				
				if($department_id == 1) {
					return $lower_department;
				}

				elseif($department_id == 2) {
					return $upper_department;
				}

				else {return null;}

			}
	 	));

	 	$department_stub->method('fetchChildDepartments')->willReturn($child_departments_arr);

		$personnel_mock = $this->createMock(IPersonnelRepository::class);
		$personnel_mock->method('findByDepartmentId')->willReturn($lower_personnel_arr, $middle_personnel_arr);


		$this->accesible_personnel_provider = new AccessiblePersonnelProvider($department_stub ,$personnel_mock);

		$number_of_arrays = count($this->accesible_personnel_provider->getAccessiblePersonnels($upper_personnel));

		$this->assertNotEquals(2, $number_of_arrays); // only one returned
	}

	public function test_If_getAccessiblePersonnels_Returns_Parent_Departments_Accessible_Personnels(){ //REACHING UPPER????

		$child_department = new Department(new DepartmentId(1), 'CHILD DEPARTMENT', new DepartmentId(2), null, 2,2,2,2);
		$parent_department_1 = new Department(new DepartmentId(2), 'FIRST PARENT DEPARTMENT', new DepartmentId(3), null, 1,1,1,1);
		$parent_department_2 = new Department(new DepartmentId(3), 'SECOND PARENT DEPARTMENT', null, null, 1,1,1,1);

		$child_department_personnel = new Personnel(
			new PersonnelId(1),
			null, 
			new DepartmentId(1), 
			true, 
			null, 
			'michael', 
			'corleone',
			'10411223345', 
			'male', '+4201244109499', 
			'm-corleone@mail.it', 
			new DateTime(), 
			null);

		$parent_department_1_personnel = array(new Personnel(
			new PersonnelId(2),
			null, 
			new DepartmentId(2), 
			true, 
			null, 
			'sonny', 
			'corleone',
			'10411223345', 
			'male', '+4201244109499', 
			's-corleone@mail.it', 
			new DateTime(), 
			null)
		);

		$parent_department_2_personnel = array(new Personnel(
			new PersonnelId(3),
			null, 
			new DepartmentId(3), 
			true, 
			null, 
			'vito', 
			'corleone',
			'10411223345', 
			'male', '+4201244109499', 
			'v-corleone@mail.it', 
			new DateTime(), 
			null)
		);

	$department_repository = $this->createMock(IDepartmentRepository::class);
	$department_repository->method('findDepartment')->willReturn($child_department);
	$department_repository->method('fetchChildDepartments')->willReturnCallback(
		function(DepartmentId $department_id) use ($parent_department_1, $parent_department_1_personnel, $parent_department_2, $parent_department_2_personnel) {
		
		if($department_id->equals($parent_department_1->id())){
			return array($parent_department_1);
		}

		else if($department_id->equals($parent_department_2->id())){
			return array($parent_department_2);
		}
	} 
	);

	$personnel_repository = $this->createMock(IPersonnelRepository::class);
	$personnel_repository->method('findByDepartmentId')->will(
		$this->returnCallback(function (DepartmentId $department_id) use ($child_department, $child_department_personnel, $parent_department_1, $parent_department_1_personnel, $parent_department_2, $parent_department_2_personnel){

			if($department_id->equals($child_department->id())){
				return array($child_department_personnel);
			}

			else if($department_id->equals($parent_department_1->id())){
				return $parent_department_1_personnel;
			}

			else if($department_id->equals($parent_department_2->id())){
				return $parent_department_2_personnel;
			}

		}
	));		

	$this->accesible_personnel_provider = new AccessiblePersonnelProvider($department_repository, $personnel_repository);
	$number_of_arrays = count($this->accesible_personnel_provider->getAccessiblePersonnels($child_department_personnel));

	$this->assertEquals(3, $number_of_arrays); 

}
	
	public function test_If_getAccessiblePersonnels_Cannot_Access_Personnel_Director_Depth_Is_Zero(){ 

		$child_departments_arr = array(new Department(new DepartmentId(1), 'this is default department', new DepartmentId(2), null, 1,1,1,1));
		$lower_department = new Department(new DepartmentId(1), 'LOWER DEPARTMENT', new DepartmentId(2), null, 1,1,1,1);
		$upper_department = new Department(new DepartmentId(2), 'UPPER DEPARTMENT', null, null, 1,1,0,0);


		$lower_personnel_arr = array(new Personnel(new PersonnelId(1), null, new DepartmentId(1), true, null, 'michael', 'corleone','10411223345', 'male', '+4201244109499', 'm-corleone@mail.it', new DateTime(), null));

		$middle_personnel_arr = array(new Personnel(new PersonnelId(1), null, new DepartmentId(1), true, null, 'sonny', 'corleone','19419229345', 'male', '+4201214101199', 's-corleone@mail.it', new DateTime(), null)); 

		$upper_personnel = new Personnel(new PersonnelId(2), null, new DepartmentId(2), true, null, 'vito', 'corleone','10111223345', 'male', '+420124410941', 'v-corleone@mail.it', new DateTime(), null);

		$department_stub = $this->createMock(IDepartmentRepository::class);

		$department_stub->method('findDepartment')->will(

			$this->returnCallback(function ($dep) use ($upper_department, $lower_department) {

				$department_id = $dep->getId();
				
				if($department_id == 1) {
					return $lower_department;
				}

				elseif($department_id == 2) {
					return $upper_department;
				}

				else {return null;}

			}
	 	));

	 	$department_stub->method('fetchChildDepartments')->willReturn($child_departments_arr);

		$personnel_mock = $this->createMock(IPersonnelRepository::class);
		$personnel_mock->method('findByDepartmentId')->willReturn($lower_personnel_arr, $middle_personnel_arr);


 		$this->accesible_personnel_provider = new AccessiblePersonnelProvider($department_stub ,$personnel_mock);

		$number_of_arrays = count($this->accesible_personnel_provider->getAccessiblePersonnels($upper_personnel));

		$this->assertNotEquals(2, $number_of_arrays); 
	}

}

?>