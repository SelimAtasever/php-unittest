<?php

use model\StructureProfile\infrastructure\AttachmentLocator;

use \model\StructureProfile\domain\model\StructureId;
use \model\StructureProfile\domain\model\Attachment;
use \model\StructureProfile\domain\model\AttachmentId;

use PHPUnit\Framework\TestCase;


class AttachmentLocatorTest extends TestCase{

	public function test_If_getFilePath_Returns_File_Path_Correctly(){

		$attachment_locator = new AttachmentLocator(
			DIR_REPOSITORY .'repo/test/file_locator_example.txt'
		);

		$file_path = $attachment_locator->getFilePath(
			new AttachmentId(1),
			new StructureId(1),
			'sap'
		);

		$this->assertEquals(DIR_REPOSITORY .'repo/test/file_locator_example.txtgis/attachment/1/1.sap', $file_path);
	}
}

?>