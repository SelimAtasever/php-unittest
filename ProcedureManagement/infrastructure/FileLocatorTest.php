<?php

use \model\ProcedureManagement\infrastructure\FileLocator;
use \model\ProcedureManagement\domain\model\AttachmentId;
use \model\ProcedureManagement\domain\model\StepId;

use PHPUnit\Framework\TestCase;

class FileLocatorTest extends TestCase{

	public function test_If_getFilePath_Returns_The_File_Path(){

		$root = DIR_REPOSITORY .'repo/test/file_locator_example.txt';

		$file_locator = new FileLocator($root);

		$path = $file_locator->getFilePath(new AttachmentId(1), new StepId(1), 'txt');
		$this->assertEquals(DIR_REPOSITORY .'repo/test/file_locator_example.txtprocedure/1/1.txt', $path);
	}
}

?>