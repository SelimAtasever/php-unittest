<?php

use \model\FileManagement\application\FileService;
use \model\FileManagement\infrastructure\FileRepository;

use \model\FileManagement\domain\model\IFileRepository;
use \model\FileManagement\infrastructure\IImagePathProvider;
use \model\FileManagement\infrastructure\IImageResizer;
use \model\FileManagement\infrastructure\ImagePathProvider;
use \model\FileManagement\infrastructure\RootDirectoryLocator;
use \model\FileManagement\infrastructure\ImageResizer;

use \model\common\QueryObject;

use PHPUnit\Framework\TestCase;

class FileServiceDbTest extends TestCase{

	private static \DB $db;

 	public static function setUpBeforeClass() : void {
    	
    	global $framework;
        $config = $framework->get('config');

        self::$db = new \DB(
            $config->get('db_file_type'),
            $config->get('db_file_hostname'),
            $config->get('db_file_username'),
            $config->get('db_file_password'),
            $config->get('db_file_database'),
            $config->get('db_file_port')
        );

       self::$db->command("DELETE FROM file");
       self::$db->command("DELETE FROM directory");

	}

	public function test_If_createDirectory_Creates_A_New_Directory_And_Returns_Its_Id(){
	
		$locator = new RootDirectoryLocator('./role_root_dir/');
		$bin_locator = new RootDirectoryLocator('./role_root_bin_dir/');

		$file_repository = new FileRepository(self::$db, $locator, $bin_locator, null);
		$image_path_provider = new ImagePathProvider(
			'/Users/selimatasever/Desktop/GitHub/erp-api/v1/tests/yol');
		
		$image_resizer = new ImageResizer(new ImagePathProvider(
			'/Users/selimatasever/Desktop/GitHub/erp-api/v1/tests/yol/'));

		$file_service = new FileService($file_repository, $image_path_provider, $image_resizer);

		$returned_directory_id = $file_service->createDirectory(1,null,'directory name');
		$this->assertNotEmpty($returned_directory_id);
	
	}

	public function test_If_uploadFile_Creates_A_New_File_And_Returns_Its_Id(){

		$locator = new RootDirectoryLocator('./role_root_dir/');
		$bin_locator = new RootDirectoryLocator('./role_root_bin_dir/');

		$file_repository = new FileRepository(self::$db, $locator, $bin_locator, null);

		$image_path_provider = new ImagePathProvider(
			'/Users/selimatasever/Desktop/GitHub/erp-api/v1/tests/yol');
		
		$image_resizer = new ImageResizer(new ImagePathProvider(
			'/Users/selimatasever/Desktop/GitHub/erp-api/v1/tests/yol/'));

		$file_service = new FileService($file_repository, $image_path_provider, $image_resizer);

		$returned_file_id = $file_service->uploadFile(1,null,'base64', 'file_name');
		$this->assertNotEmpty($returned_file_id);
	}	


	public function test_If_renameDirectory_Changes_The_Name_Of_The_Directory(){

		$locator = new RootDirectoryLocator('./role_root_dir/');
		$bin_locator = new RootDirectoryLocator('./role_root_bin_dir/');

		$file_repository = new FileRepository(self::$db, $locator, $bin_locator, null);

		$image_path_provider = new ImagePathProvider(
			'/Users/selimatasever/Desktop/GitHub/erp-api/v1/tests/yol');
		
		$image_resizer = new ImageResizer(new ImagePathProvider(
			'/Users/selimatasever/Desktop/GitHub/erp-api/v1/tests/yol/'));

		$file_service = new FileService($file_repository, $image_path_provider, $image_resizer);

		$returned_directory_id = $file_service->createDirectory(2,null,'directory_name_2'); // returns unique id.

		$file_service->renameDirectory($returned_directory_id,2,'changed_directory_name');

		$changed_name = self::$db->query("SELECT * FROM directory WHERE id = :id" , array(
			':id' => $returned_directory_id
		))->row['name'];

		$this->assertEquals($changed_name, 'changed_directory_name');
	}

	public function test_If_renameFile_Changes_The_Name_Of_The_File(){

		$locator = new RootDirectoryLocator('./role_root_dir/');
		$bin_locator = new RootDirectoryLocator('./role_root_bin_dir/');

		$file_repository = new FileRepository(self::$db, $locator, $bin_locator, null);

		$image_path_provider = new ImagePathProvider(
			'/Users/selimatasever/Desktop/GitHub/erp-api/v1/tests/yol');
		
		$image_resizer = new ImageResizer(new ImagePathProvider(
			'/Users/selimatasever/Desktop/GitHub/erp-api/v1/tests/yol/'));

		$file_service = new FileService($file_repository, $image_path_provider, $image_resizer);

		$returned_file_id = $file_service->uploadFile(2,null,'base64', 'file_name'); 

		$file_service->renameFile($returned_file_id,2,'changed_file_name');

		$changed_name = self::$db->query("SELECT * FROM file WHERE id = :id" , array(
			':id' => $returned_file_id
		))->row['name'];

		$this->assertEquals($changed_name, 'changed_file_name');

	}

	public function test_If_moveDirectory_Carries_Directory_To_Parent_Directory(){

		$locator = new RootDirectoryLocator('./role_root_dir/');
		$bin_locator = new RootDirectoryLocator('./role_root_bin_dir/');

		$file_repository = new FileRepository(self::$db, $locator, $bin_locator, null);

		$image_path_provider = new ImagePathProvider(
			'/Users/selimatasever/Desktop/GitHub/erp-api/v1/tests/yol');
		
		$image_resizer = new ImageResizer(new ImagePathProvider(
			'/Users/selimatasever/Desktop/GitHub/erp-api/v1/tests/yol/'));

		$file_service = new FileService($file_repository, $image_path_provider, $image_resizer);

		$parent_directory_id = $file_service->createDirectory(3, null, 'directory_name_3');
		$child_directory_id = $file_service->createDirectory(3, null, 'directory_name_4');

		$file_service->moveDirectory($child_directory_id, 3, $parent_directory_id);

		$child_directorys_parent_id = self::$db->query("SELECT * FROM directory WHERE id = :id", array(
			':id' => $child_directory_id
		))->row['parent_id'];

		$this->assertEquals($child_directorys_parent_id, $parent_directory_id);

	}

	public function test_If_moveFile_Carries_File_To_Desired_Directory(){

		$locator = new RootDirectoryLocator('./role_root_dir/');
		$bin_locator = new RootDirectoryLocator('./role_root_bin_dir/');

		$file_repository = new FileRepository(self::$db, $locator, $bin_locator, null);

		$image_path_provider = new ImagePathProvider(
			'/Users/selimatasever/Desktop/GitHub/erp-api/v1/tests/yol');
		
		$image_resizer = new ImageResizer(new ImagePathProvider(
			'/Users/selimatasever/Desktop/GitHub/erp-api/v1/tests/yol/'));

		$file_service = new FileService($file_repository, $image_path_provider, $image_resizer);

		$returned_file_id = $file_service->uploadFile(2,null,'base64', 'file_name_3'); 
		$returned_directory_id = $file_service->createDirectory(2, null, 'directory_name_5');

		$file_service->moveFile($returned_file_id, 2, $returned_directory_id);

		$file_diretory_id = self::$db->query("SELECT * FROM file WHERE id = :id", array(
			':id' => $returned_file_id
		))->row['directory_id'];
		
		$this->assertEquals($file_diretory_id, $returned_directory_id);
	}

	public function test_If_getDirectory_Returns_Directory_From_Db_Correctly(){

		$locator = new RootDirectoryLocator('./role_root_dir/');
		$bin_locator = new RootDirectoryLocator('./role_root_bin_dir/');

		$file_repository = new FileRepository(self::$db, $locator, $bin_locator, null);

		$image_path_provider = new ImagePathProvider(
			'/Users/selimatasever/Desktop/GitHub/erp-api/v1/tests/yol');
		
		$image_resizer = new ImageResizer(new ImagePathProvider(
			'/Users/selimatasever/Desktop/GitHub/erp-api/v1/tests/yol/'));

		$file_service = new FileService($file_repository, $image_path_provider, $image_resizer);

		$directory_id = $file_service->createDirectory(1, null, 'directory name_2');

		$returned_directory = $file_service->getDirectory(1, $directory_id);

		$this->assertNotEmpty($returned_directory);

		$new_directory_name_query = self::$db->query("SELECT * FROM directory WHERE id = :id", array(
			':id' => $directory_id
		))->row['name'];
		
		$this->assertEquals($returned_directory->name(), $new_directory_name_query);
	}

	public function test_If_getFile_Returns_File_From_Db_Correctly(){

		$locator = new RootDirectoryLocator('./role_root_dir/');
		$bin_locator = new RootDirectoryLocator('./role_root_bin_dir/');

		$file_repository = new FileRepository(self::$db, $locator, $bin_locator, null);

		$image_path_provider = new ImagePathProvider(
			'/Users/selimatasever/Desktop/GitHub/erp-api/v1/tests/yol');
		
		$image_resizer = new ImageResizer(new ImagePathProvider(
			'/Users/selimatasever/Desktop/GitHub/erp-api/v1/tests/yol/'));

		$file_service = new FileService($file_repository, $image_path_provider, $image_resizer);
	
		$file_id = $file_service->uploadFile(3, null, 'base64', 'file_name_4');

		$returned_file = $file_service->getFile(3, $file_id, null,null,null);
		$this->assertNotEmpty($returned_file);

		$new_file_name_query = self::$db->query("SELECT * FROM file WHERE id = :id", array(
			':id' => $file_id
		))->row['name'];
		
		$this->assertEquals($returned_file->name(), $new_file_name_query);
	}

	public function test_If_getDirectories_Returns_Directories_Dto_Array(){


		$locator = new RootDirectoryLocator('./role_root_dir/');
		$bin_locator = new RootDirectoryLocator('./role_root_bin_dir/');

		$file_repository = new FileRepository(self::$db, $locator, $bin_locator, null);

		$image_path_provider = new ImagePathProvider(
			'/Users/selimatasever/Desktop/GitHub/erp-api/v1/tests/yol');
		
		$image_resizer = new ImageResizer(new ImagePathProvider(
			'/Users/selimatasever/Desktop/GitHub/erp-api/v1/tests/yol/'));

		$file_service = new FileService($file_repository, $image_path_provider, $image_resizer);

		$directory_query_dtos = $file_service->getDirectories(2, null, new QueryObject());

		$arr_of_directories = $directory_query_dtos->directories();

		$this->assertIsArray($arr_of_directories); 
		$this->assertEquals(count($arr_of_directories), 2);

	}

	public function test_If_getFiles_Returns_Directories_Dto_Array(){

		$locator = new RootDirectoryLocator('./role_root_dir/');
		$bin_locator = new RootDirectoryLocator('./role_root_bin_dir/');

		$file_repository = new FileRepository(self::$db, $locator, $bin_locator, null);

		$image_path_provider = new ImagePathProvider(
			'/Users/selimatasever/Desktop/GitHub/erp-api/v1/tests/yol');
		
		$image_resizer = new ImageResizer(new ImagePathProvider(
			'/Users/selimatasever/Desktop/GitHub/erp-api/v1/tests/yol/'));

		$file_service = new FileService($file_repository, $image_path_provider, $image_resizer);

		$file_query_dtos = $file_service->getFiles(3, null, new QueryObject(), null, null, null);

		$arr_of_files = $file_query_dtos->files();

		$this->assertIsArray($arr_of_files);
		$this->assertEquals(count($arr_of_files), 1);
	}

	public function test_If_removeDirectory_Removes_Created_Directory(){

		$locator = new RootDirectoryLocator('./role_root_dir/');

		$bin_locator = new RootDirectoryLocator('./role_root_bin_dir/');

		$file_repository = new FileRepository(self::$db, $locator, $bin_locator, null);

		$image_path_provider = new ImagePathProvider(
			'/Users/selimatasever/Desktop/GitHub/erp-api/v1/tests/yol');
		
		$image_resizer = new ImageResizer(new ImagePathProvider(
			'/Users/selimatasever/Desktop/GitHub/erp-api/v1/tests/yol/'));

		$file_service = new FileService($file_repository, $image_path_provider, $image_resizer);

		$directory_id = $file_service->createDirectory(1, null, 'deleted directory');

		$file_service->removeDirectory($directory_id, 1);

		$confirm_directory_not_exist = self::$db->query("SELECT * FROM file WHERE id = :id", array(
			':id' => $directory_id
		))->row;

		$this->assertEmpty($confirm_directory_not_exist);
	}


	public function test_If_removeFile_Removes_Created_File(){

		$locator = new RootDirectoryLocator('./role_root_dir/');

		$bin_locator = new RootDirectoryLocator('./role_root_bin_dir/');

		$file_repository = new FileRepository(self::$db, $locator, $bin_locator, null);

		$image_path_provider = new ImagePathProvider(
			'/Users/selimatasever/Desktop/GitHub/erp-api/v1/tests/yol');
		
		$image_resizer = new ImageResizer(new ImagePathProvider(
			'/Users/selimatasever/Desktop/GitHub/erp-api/v1/tests/yol/'));

		$file_service = new FileService($file_repository, $image_path_provider, $image_resizer);

		$file_id = $file_service->uploadFile(1, null, 'base64', 'deleted file');

		$file_service->removeFile($file_id, 1);

		$confirm_directory_not_exist = self::$db->query("SELECT * FROM file WHERE id = :id", array(
			':id' => $file_id
		))->row;

		$this->assertEmpty($confirm_directory_not_exist);
	} 
} 	

?>