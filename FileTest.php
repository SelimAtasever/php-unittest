	
<?php

use \model\FileManagement\domain\model\File;
use \model\FileManagement\domain\model\FileId;
use \model\common\domain\model\SubmoduleId;
use \model\FileManagement\domain\model\DirectoryId;

use PHPUnit\Framework\TestCase;

class FileTest extends TestCase {


	public function testRenameFunctionChangesName() {

		$file = new File(new FileId(1), new SubmoduleId(1), null , 'base', 'new_file', null);

		$this->assertEquals('new_file', $file->name());

		$file->rename('this_is_new_file');

		$this->assertEquals('this_is_new_file', $file->name());

	}

	public function testMoveFunctionMovesDirectoryId() {

		$file = new File(new FileId(1), new SubmoduleId(1), new DirectoryId('1') , 'base', 'new_file', null);

		$this->assertEquals(new DirectoryId('1'), $file->directoryId());

		$file->move(new DirectoryId('2'));

		$this->assertEquals(new DirectoryId('2'), $file->directoryId());

	}


	public function testIfNullDateTimeReturnsCurrentDataTime() {

		$file = new File(new FileId(1), new SubmoduleId(1), null , 'base', 'new_file', null);

		$new_date = $file->dateAdded();

		$this->assertEquals((new \DateTime('now'))->format('Y-m-d H:i:s'), $new_date->format('Y-m-d H:i:s'));

		$this->assertTrue((new DateTime())->getTimestamp() - $new_date->getTimestamp() < 5); 

	}

}


?>