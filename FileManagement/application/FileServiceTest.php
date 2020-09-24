<?php

use \model\FileManagement\application\FileService;

use \model\FileManagement\domain\model\IFileRepository;
use \model\FileManagement\infrastructure\IImagePathProvider;
use \model\FileManagement\infrastructure\IImageResizer;

use \model\FileManagement\application\exception\FileNotfoundException;
use \model\FileManagement\application\exception\DirectoryNotfoundException;
use \model\common\QueryObject;

use PHPUnit\Framework\TestCase;


class FileServiceTest extends TestCase{

	private FileService $file_service;

	protected function setUp() : void {

		$file_repository = $this->createMock(IFileRepository::class);
		$file_repository->method('findFile')->willReturn(null);
		$file_repository->method('findDirectory')->willReturn(null);

		$image_path_provider = $this->createMock(IImagePathProvider::class);
		$image_path_provider->method('providePath')->willReturn('path as string...');

		$imager_resizer = $this->createMock(IImageResizer::class);
		$imager_resizer->method('resize')->willReturn('new size...');

		$this->file_service = new FileService($file_repository, $image_path_provider, $imager_resizer);
	}

	public function test_moveFile_Throws_Exception_If_File_Isnt_Found(){

		$this->expectException(FileNotFoundException::class);

		$this->file_service->moveFile(1,1,null);

	}

	public function test_removeFile_Throws_Exception_If_File_Isnt_Found(){

		$this->expectException(FileNotfoundException::class);

		$this->file_service->removeFile(1,1);
	}

	public function test_renameFile_Throws_Exception_If_File_Isnt_Found(){

		$this->expectException(FileNotfoundException::class);

		$this->file_service->renameFile(1,1,1);
	}

	public function test_uploadFile_Throws_Exception_If_Directory_Isnt_Found(){

		$this->expectException(DirectoryNotfoundException::class);

		$this->file_service->uploadFile(1,1,'base64','name');
	}

	public function test_removeDirectory_Throws_Exception_If_Directory_Isnt_Found(){

		$this->expectException(DirectoryNotfoundException::class);

		$this->file_service->removeDirectory(1,1);

	}

	public function test_renameDirectory_Throws_Exception_If_Directory_Isnt_Found(){

		$this->expectException(DirectoryNotfoundException::class);

		$this->file_service->renameDirectory(1,1,'name');
	}

	public function test_moveDirectory_Throws_Exception_If_Directory_Isnt_Found(){

		$this->expectException(DirectoryNotfoundException::class);

		$this->file_service->removeDirectory(1,1,1);
	}

	public function test_createDirectory_Throws_Exception_If_Directory_Isnt_Found(){

		$this->expectException(DirectoryNotfoundException::class);

		$this->file_service->createDirectory(1,1,'name');
	}

	public function test_getFile_Throws_Exception_If_File_Isnt_Found(){

		$this->expectException(FileNotfoundException::class);

		$this->file_service->getFile(1,1,'sizing',1,1);
	}

	public function test_getDirectory_Throws_Exception_If_File_Isnt_Found(){

		$this->expectException(DirectoryNotfoundException::class);

		$this->file_service->getDirectory(1,1);
	}
}

?>