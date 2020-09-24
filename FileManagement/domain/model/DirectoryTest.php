<?php

use \model\FileManagement\domain\model\Directory;
use \model\common\domain\model\SubmoduleId;
use \model\FileManagement\domain\model\DirectoryId;

use PHPUnit\Framework\TestCase;


class DirectoryTest extends Testcase {


	public function testIfRenameChangedDirectoryName() {


		$directory = new Directory(new DirectoryId(1), new SubmoduleId(1), null , 'old_directory' , null);

		$this->assertEquals('old_directory', $directory->name());

		$directory->rename('new_directory');

		$this->assertEquals('new_directory', $directory->name());


	}

	public function testIfMoveFunctionMovesDirectoryId() {

		$directory = new Directory(new DirectoryId(1), new SubmoduleId(1), new DirectoryId('3') , 'old_directory' , null);

		$this->assertEquals(new DirectoryId('3'), $directory->parentId());

		$directory->move(new DirectoryId('2'));

		$this->assertEquals(new DirectoryId('2'), $directory->parentId());
	}

	public function testNullDateTimeReturnsCurrentDateTime() {


		 $directory = new Directory(new DirectoryId(1), new SubmoduleId(1), null , 'old_directory' , null);

		 $date = $directory->dateAdded();

		 $this->assertEquals((new \DateTime('now'))->format('Y-m-d H:i:s'), $date->format('Y-m-d H:i:s'));

		 $this->assertTrue((new \DateTime('now'))->getTimestamp() - $date->getTimestamp() < 5); 

	}


}


?>