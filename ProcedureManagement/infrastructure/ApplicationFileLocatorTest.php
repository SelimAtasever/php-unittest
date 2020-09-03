<?php 

use \model\ProcedureManagement\infrastructure\ApplicationFileLocator;
use \model\ProcedureManagement\domain\model\ProcedureId;

use PHPUnit\Framework\TestCase;

class ApplicationFileLocatorTest extends TestCase{

	public function test_If_locate_Method_Returns_The_Path_Of_The_Created_File(){

		$root = DIR_REPOSITORY .'repo/test/new_file.txt';

		$application_file_locator = new ApplicationFileLocator($root);

		$path = $application_file_locator->locate(new ProcedureId(1), 1, null);
		$this->assertEquals(DIR_REPOSITORY .'repo/test/new_file.txtprocedure/application/1/1', $path);

	}

	public function test_If_locate_Method_Indeed_Creates_A_File(){

		$root = DIR_REPOSITORY . '/repo/procedure/application/new_file.txtprocedure/application/1';

		$confirm_file_exists = file_exists($root);
		$this->assertTrue($confirm_file_exists);
	}
}


?>