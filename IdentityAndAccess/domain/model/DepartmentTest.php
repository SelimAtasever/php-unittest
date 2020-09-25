<?php

use \model\IdentityAndAccess\domain\model\Department;
use \model\IdentityAndAccess\domain\model\PersonnelId;
use \model\IdentityAndAccess\domain\model\RoleId;
use \model\IdentityAndAccess\domain\model\DepartmentId;
use \model\IdentityAndAccess\domain\model\exception\DepartmentAlreadyHasADirectorException;
use \model\IdentityAndAccess\domain\model\exception\DepartmentNameLengthException;
use \model\IdentityAndAccess\domain\model\Personnel;

use \model\common\ExceptionCollection;


use PHPUnit\Framework\TestCase;


class DepartmentTest extends TestCase {


	public function testIf_Rename_Changes_Departmen_Name() {

		$department = new Department(new DepartmentId(1), 'first_dep', null, null, 10, 11);
		$department->rename('first_department');

		$changed_name = $department->name();
		$this->assertEquals('first_department', $changed_name);

	}

	public function testIf_DepartmentName_MustBe_Longer_Than_0() {

		$this->expectException(DepartmentNameLengthException::class);
		try {
		$department = new Department(new DepartmentId(2), 'department', null, null, 11, 12);
		$department->rename('');

		} catch(ExceptionCollection $e){
			$this->throwFromExceptionCollection($e, DepartmentNameLengthException::class);
		}
	}

	public function testIf_DepartmentName_MustBe_Shorter_Than_129() {

		$this->expectException(DepartmentNameLengthException::class);

		try {
		$department = new Department(new DepartmentId(3), 'department', null, null, 11, 12);
		$department->rename(str_repeat('a', 129));

		} catch (ExceptionCollection $e) {
			$this->throwFromExceptionCollection($e, DepartmentNameLengthException::class);
		}
	}
	

	public function testCannot_Assign_Two_Directors_To_One_Department() {

		$this->expectException(DepartmentAlreadyHasADirectorException::class);

		try {

		$department = new Department(new DepartmentId(4), 'department', null, null, 14, 12);
		$personnel = new Personnel(new PersonnelId(1), new RoleId(1), new DepartmentId(4), true, null,'john', 'doe', '11223344556', 'female', '0049224591432', 'johndoe@mail.com', null, null);
		$personnel2 = new Personnel(new PersonnelId(2), new RoleId(2), new DepartmentId(4), true, null,'mary', 'doe', '16223344556', 'female', '1049224591432', 'marydoe@mail.com', null, null);

		$department->assignDirector($personnel);
		$department->assignDirector($personnel2);

		} catch (ExceptionCollection $e) {
			$this->throwFromExceptionCollection($e, DepartmentAlreadyHasADirectorException::class);
		}

	}

	public function testIf_Clear_Director_Removes_Assigned_Director() {

		$department = new Department(new DepartmentId(1), 'department', null, null, 14, 12);
		$personnel = new Personnel(new PersonnelId(1), new RoleId(1), new DepartmentId(1), true, null,'john', 'doe', '11223344556', 'female', '0049224591432', 'johndoe@mail.com', null, null);

		$department->assignDirector($personnel);
		$check_removed = $department->clearDirector();

		$this->assertEmpty($check_removed);
	
	}

	public function testIf_ParentDepartment_Returns_Correctly() {

		$department1 = new Department(new DepartmentId(1), 'department', null, null, 14, 12);
		$department2 = new Department(new DepartmentId(2), 'department', new DepartmentId(1), null, 14, 12);

		$check_true = $department1->isTopLevelDepartment();

		$this->assertTrue($check_true);

	}

	public function testIf_IsTopLevel_Returns_False_When_Department_Id_Is_Null() {

		$department1 = new Department(new DepartmentId(1), 'department', null, null, 14, 12);
		$department2 = new Department(new DepartmentId(2), 'department', new DepartmentId(1), null, 14, 12);

		$check_false = $department2->isTopLevelDepartment();

		$this->assertFalse($check_false);
	}


	private function throwFromExceptionCollection($exception_collection, $exception) {
		foreach($exception_collection->getExceptions() as $e) {
			if(get_class($e) == $exception) {
				throw new $exception;
			}
		}
	}



}

?>