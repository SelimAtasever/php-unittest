<?php

use \model\FileManagement\infrastructure\FileRepository;
use \model\FileManagement\infrastructure\IRootDirectoryLocator;
use \model\common\domain\model\SubmoduleId;
use \model\FileManagement\domain\model\DirectoryId;
use \model\FileManagement\domain\model\Directory;
use \model\FileManagement\domain\model\File;
use \model\FileManagement\domain\model\FileId;

use \model\common\QueryObject;
use PHPUnit\Framework\TestCase;


class FileRepositoryTest extends TestCase {

	private static \DB $db;
	private $file_repository_instance;

	private IRootDirectoryLocator $locator;
	private IRootDirectoryLocator $bin_locator;

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

        self::$db->command("DELETE FROM directory");
		self::$db->command("DELETE FROM directory_bin");
        self::$db->command("DELETE FROM file");
        self::$db->command("DELETE FROM directory_bin");


        $clear_files = glob('./role_root_dir/*'); // prevents folder dup.
 			
 			foreach($clear_files as $file){ 
		  	  	if(is_file($file))
    				unlink($file);
    			else
    				self::rrmdir($file);
		}



		$clear_files = glob('./role_root_bin_dir/*'); // prevents file dup.
 			
 			foreach($clear_files as $file){ 
		  	  	if(is_file($file))
    				unlink($file);
    			else
    				self::rrmdir($file);
		}
	}

	/**
     * Removes directories recursively
     *
     * @param  mixed $dir
     * @return void
     */
    private static function rrmdir($dir) {
        if(is_dir($dir)) {
            $objects = scandir($dir);

            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir."/".$object) == "dir") 
                        self::rrmdir($dir."/".$object); 
                    else 
                        unlink($dir."/".$object);
                }
            }

            reset($objects);
            rmdir($dir);
        }
    }

	protected function setUp() : void {

        $this->locator = $this->createMock(IRootDirectoryLocator::class);
        $this->locator->method('getRootDirectoryFor')->willReturn('./role_root_dir/');

        $this->bin_locator = $this->createMock(IRootDirectoryLocator::class);
        $this->bin_locator->method('getRootDirectoryFor')->willReturn('./role_root_bin_dir/');

        $this->file_repository = new FileRepository(self::$db, $this->locator, $this->bin_locator);

	}


	public function test_If_saveDirectory_Creates_A_Folder_And_Returns_Its_Id() { 

		
		$id_obj = $this->file_repository->saveDirectory(new Directory(
			null, 
			new SubmoduleId(1), 
			null, 
			'New Folder 1', 
			null
		));

		$id = $id_obj->getId();

		$db_id = self::$db->query("SELECT * FROM directory WHERE id = '$id'")->row['id'];

		$this->assertEquals($id, $db_id); /* assert created folder id matches with the one on db */ 

		$folder = $this->locator->getRootDirectoryFor(new SubmoduleId(1)) . $id_obj->getId(); 

		$this->assertFileExists($folder);

		$this->assertEquals($folder, './role_root_dir/' . $id);
				
	}


	public function test_If_Save_Directory_Created_Directory_With_Parent_Id() { 


		$parent_directory_id = $this->file_repository->saveDirectory(new Directory(
			null, 
			new SubmoduleId(1), 
			null, 
			'Parent Directory', 
			null
		));
		
		$child_directory_id = $this->file_repository->saveDirectory(new Directory(
			null, 
			new SubmoduleId(1), 
			$parent_directory_id, 
			'Child Directory', 
			null
		));

		$child_directory_db_id = self::$db->query("SELECT * FROM directory WHERE id = :id", array(
			':id'=>$child_directory_id->getId()
		))->row['id'];

		$this->assertEquals($child_directory_id->getId(), $child_directory_db_id);	

		$folder = $this->locator->getRootDirectoryFor(new SubmoduleId(1)) . $parent_directory_id->getId(); 



		$this->assertFileExists($folder);

		$this->assertEquals($folder, './role_root_dir/' . $parent_directory_id->getId());
		
	}

	public function test_If_removeDirectory_Deletes_The_Directory_And_Carries_It_To_Directory_Bin() {


		$directory_id = $this->file_repository->saveDirectory(new Directory(
			null, 
			new SubmoduleId(2), 
			null, 
			'New Folder 2',
			null
		));

		$this->file_repository->removeDirectory($directory_id, new SubmoduleId(2));

		$directory_db = self::$db->query("SELECT * FROM directory WHERE id = :id", array(
			':id'=>$directory_id->getId()
		))->row;

		$this->assertEmpty($directory_db);

		$folder = $this->locator->getRootDirectoryFor(new SubmoduleId(2)) . $directory_id->getId(); 

		$this->assertFileDoesNotExist($folder, "./role_root_dir/" . $directory_id->getId());

		$folder_bin = $this->bin_locator->getRootDirectoryFor(new SubmoduleId(2)) . $directory_id->getId();
		$this->assertFileExists($folder_bin, "./role_root_bin_dir/" . $directory_id->getId());

	}


	public function test_If_removeDirectory_Deletes_The_Directory_And_Carries_It_To_Db_Directory_Bin(){

		$directory_id = $this->file_repository->saveDirectory(new Directory(
			null, 
			new SubmoduleId(2), 
			null, 
			'New Folder 2',
			null
		));

		$this->file_repository->removeDirectory($directory_id, new SubmoduleId(2));

		$directory_db = self::$db->query("SELECT * FROM directory WHERE id = :id", array(
			':id'=>$directory_id->getId()
		))->row;

		$this->assertEmpty($directory_db);

		$id = $directory_id->getId();

		$directory_bin_id = self::$db->query("SELECT * FROM directory_bin WHERE id = '$id'")->row['id'];

		$this->assertEquals($directory_bin_id, $directory_id->getId());
	}


	public function test_If_saveFile_Creates_File_On_Db_And_Folder() {


		$file_id = $this->file_repository->saveFile(new File(
			null, 
			new SubmoduleId(1), 
			null, 
			'base64', 
			'New File 1', 
			null
		));

		$file_db_id = self::$db->query("SELECT * FROM file WHERE id = :id", array(
			':id'=>$file_id->getId()
		))->row['id'];

		$this->assertEquals($file_db_id, $file_id->getId());

		$file = $this->locator->getRootDirectoryFor(new SubmoduleId(1)) . $file_id->getId();
		$this->assertEquals($file, './role_root_dir/' . $file_id->getId());

	}

	public function test_If_saveFile_Saved_The_File_With_Parent_Id() {


		$directory_id = $this->file_repository->saveDirectory(new Directory(
			null, 
			new SubmoduleId(2), 
			null, 
			'Parent Directory 2', 
			null
		));

		$file_id = $this->file_repository->saveFile(new File(
			null, 
			new SubmoduleId(2), 
			$directory_id,
			'base64', 
			'Child File', 
			null
		));

	
		$file_db_id = self::$db->query("SELECT * FROM file WHERE id = :id" ,array(
			':id' => $file_id->getId()
		))->row['id'];

		$this->assertEquals($file_id->getId(), $file_db_id);

		$file_db = self::$db->query("SELECT * FROM file WHERE id = :id" ,array(
			':id' => $file_id->getId()
		))->row;

		$this->assertEquals($directory_id->getId(), $file_db['directory_id']);

		$file = $this->locator->getRootDirectoryFor(new SubmoduleId(2)) . $directory_id->getId();

		$this->assertFileExists($file);

		$this->assertEquals($file, './role_root_dir/' . $directory_id->getId());

		return $file_id;
	}

	public function test_If_Remove_File_Deletes_The_File_And_Carries_It_To_Bin_Folder() {


		$file_id = $this->file_repository->saveFile(new File(
			null, 
			new SubmoduleId(2), 
			null, 
			'base64', 
			'test-file.jpg', 
			null
		));

		$this->file_repository->removeFile($file_id, new SubmoduleId(2));

		$file = $this->locator->getRootDirectoryFor(new SubmoduleId(2)) . $file_id->getId();
		$this->assertFileDoesNotExist($file, "./role_root_dir/" . $file_id->getId());


		$file_bin = $this->bin_locator->getRootDirectoryFor(new SubmoduleId(2)) . $file_id->getId();
		$this->assertFileExists("./role_root_bin_dir/" . $file_id->getId() . '.jpg');
	
	}


	public function test_If_Remove_File_Deletes_The_File_And_Carries_It_To_Db_File_Bin(){

		$file_id = $this->file_repository->saveFile(new File(
			null, 
			new SubmoduleId(2), 
			null, 
			'base64', 
			'test-file-2.jpg', 
			null
		));

		$this->file_repository->removeFile($file_id, new SubmoduleId(2));

		$file_removed = self::$db->query("SELECT * FROM file WHERE id = :id", array(
			':id' => $file_id->getId()
		))->row;

		$this->assertEmpty($file_removed);

		$file_bin_id = self::$db->query("SELECT * FROM file_bin WHERE id = :id", array(
			':id' => $file_id->getId()
		))->row['id'];

		$this->assertEquals($file_bin_id, $file_id->getId());
	}


	public function test_Files_Can_Be_Removed_If_The_Extension_Is_Null() {


		$file_id = $this->file_repository->saveFile(new File(
			null, 
			new SubmoduleId(2), 
			null, 
			'base13', 
			'test-null', 
			null
		));

		$this->file_repository->removeFile($file_id, new SubmoduleId(2));

		$file_removed = self::$db->query("SELECT * FROM file WHERE id = :id", array(
			':id' => $file_id->getId()
		))->row;
		
		$this->assertEmpty($file_removed);

		$file_bin_id = self::$db->query("SELECT * FROM file_bin WHERE id = :id" , array(
			':id' => $file_id->getId()
		))->row['id'];

		$this->assertEquals($file_bin_id, $file_id->getId());

		$file = $this->locator->getRootDirectoryFor(new SubmoduleId(2)) . $file_id->getId();

		$this->assertFileDoesNotExist($file);

		$file_bin = $this->bin_locator->getRootDirectoryFor(new SubmoduleId(2)) . $file_id->getId();

		$this->assertFileExists($file_bin);


	}


	public function test_If_move_Method_Changes_Parent_Directory() {


		$directory_1 = $this->file_repository->saveDirectory(new Directory(
			null, 
			new SubmoduleId(1), 
			null, 
			'First Directory', 
			null
		));

		$directory_2 = $this->file_repository->saveDirectory(new Directory(
			null, 
			new SubmoduleId(1), 
			null, 
			'Second Directory', 
			null
		));

		$directory_3 = $this->file_repository->saveDirectory(new Directory(
			null, 
			new SubmoduleId(1), 
			$directory_2, 
			'Third Directory', 
			null
		));

		$d3 = $this->file_repository->findDirectory($directory_3, new SubmoduleId(1));

		$d3->move($directory_1);	/* directory parent moved to directory 1 */

		$this->file_repository->saveDirectory($d3); /* d3 : updated directory_3 */

		$updated_directory_3 = $this->file_repository->findDirectory($directory_3, new SubmoduleId(1));

		$d3_db = self::$db->query("SELECT * FROM directory WHERE id = :id", array(
			':id' => $directory_3->getId()
		))->row;

		$this->assertEquals($d3_db['parent_id'], $directory_1->getId());

		$folder = $this->locator->getRootDirectoryFor(new SubmoduleId(1)) . $directory_1->getId();

		$this->assertFileExists($folder);
	}


	public function test_If_move_Method_Changes_File_Directory() {


		$directory_1 = $this->file_repository->saveDirectory(new Directory(
			null, 
			new SubmoduleId(1), 
			null, 
			'Directory 1', 
			null
		));

		$directory_2 = $this->file_repository->saveDirectory(new Directory(
			null, 
			new SubmoduleId(1), 
			null, 
			'Directory 2', 
			null
		));

		$file = $this->file_repository->saveFile(new File(
			null, 
			new SubmoduleId(1), 
			$directory_2, 
			'base64', 
			'File.png', 
			null
		));

		$return_file = $this->file_repository->findFile($file, new SubmoduleId(1));

		$return_file->move($directory_1);

		$this->file_repository->saveFile($return_file);

		$directory_1_db = self::$db->query("SELECT * FROM directory WHERE id = :id", array(
			':id' => $directory_1->getId()
		))->row;

		$file_db = self::$db->query("SELECT * FROM file WHERE id = :id", array(
			':id' => $file->getId()
		))->row;

		$this->assertEquals($directory_1_db['id'], $file_db['directory_id']);
		

		$file_path = $this->locator->getRootDirectoryFor(new SubmoduleId(1)) . $directory_1->getId();

		$this->assertFileExists($file_path);

		return $directory_1;

	}

	 /**
     * @depends test_If_move_Method_Changes_File_Directory
     */
	public function test_If_Find_Directory_Finds_The_Correct_Directory($directory_1){

		$returned_directory = $this->file_repository->findDirectory($directory_1, new SubmoduleId(1));

		$this->assertEquals($returned_directory->name(), 'Directory 1');
		$this->assertEquals($returned_directory->id()->getId(), $directory_1->getId());
	}


	public function test_Find_Directory_Will_Return_Empty_If_There_Isnt_With_Given_Id(){

		$id = new DirectoryId('this is non existent id');

		$return_folder = $this->file_repository->findDirectory($id, new SubmoduleId(1));

		$this->assertEmpty($return_folder);

	}

	/**
     * @depends test_If_saveFile_Saved_The_File_With_Parent_Id
     */
	public function testIfFindFileFindsTheCorrectFile($file_id) {

		$return_file = $this->file_repository->findfile($file_id, new SubmoduleId(2));

		$this->assertEquals($return_file->id()->getId(), $file_id->getId());
		$this->assertEquals($return_file->name(), 'Child File');

	}

	public function test_Find_File_Will_Return_Empty_If_There_Isnt_A_File_With_Given_Id () {

		$file = new FileId('Non Existent File');

		$return_file = $this->file_repository->findFile($file, new SubmoduleId(1));

		$this->assertEmpty($return_file);

	}

	/**
     * @depends test_If_move_Method_Changes_File_Directory
     */
	public function test_Find_Directory_Cannot_Find_If_Submodule_Id_Is_Different($directory_1) {


		$return_found = $this->file_repository->findDirectory($directory_1, new SubmoduleId(2));
		/* directory_1 returns directory with submoduleid : 1 */

		$this->assertEmpty($return_found);
	}

	/**
     * @depends test_If_saveFile_Saved_The_File_With_Parent_Id
     */
	public function test_Find_File_Cannot_Find_If_Submodule_Id_Is_Different($file_id) {

		$return_found = $this->file_repository->findFile($file_id, new SubmoduleId(1));
		/* file_id returns file with submoduleid : 2 */

		$this->assertEmpty($return_found);

	}

	public function test_Fetch_Directories_Cant_Return_Directory_With_Different_Submodule_Id() {


		$this->file_repository->saveDirectory(new Directory(null, new SubmoduleId(7), null, 'First',null));
		$this->file_repository->saveDirectory(new Directory(null, new SubmoduleId(8), null, 'Second',null));

		$fetch = $this->file_repository->fetchDirectories(new SubmoduleId(7), null, new QueryObject());
		/*	only returns the 'first' directory due to submodule id  */

		$this->assertCount(1, $fetch);
		$this->assertEquals($fetch[0]->submoduleId()->getId(), 7);
		$this->assertEquals($fetch[0]->name(), 'First');

	}

	public function test_Fetch_Files_Cant_Return_File_With_Different_Submodule_Id () {

		$this->file_repository->saveFile(new File(null, new SubmoduleId(4), null, 'base64', 'File X', null));
		$this->file_repository->saveFile(new File(null, new SubmoduleId(4), null, 'base64', 'File Y', null));
		$this->file_repository->saveFile(new File(null, new SubmoduleId(8), null, 'base64', 'File Z', null));

		$fetch = $this->file_repository->fetchFiles(new SubmoduleId(4), null, new QueryObject());
		/* there is no submodule with id:8, will return first 2 file */

		$this->assertEquals(2, count($fetch));
		$this->assertEquals($fetch[0]->name(), 'File X');
		$this->assertEquals($fetch[1]->name(), 'File Y');
	}

	/**
     * @depends test_If_move_Method_Changes_File_Directory
     */
	public function test_If_entityCount_Returns($directory_1){

		$this->file_repository->saveDirectory(new Directory(null, new SubmoduleId(1), $directory_1, 'XYZ',null));

		$entity_count = $this->file_repository->entityCount(new SubmoduleId(1), $directory_1, new QueryObject());
		
		/* directory_1 is the parent of 1 directory and 1 file, this will return 2 as the number of child directory/files of directory 1. */

		$this->assertEquals(2, $entity_count);

	} 

	/**
     * @depends test_If_move_Method_Changes_File_Directory
     */
	public function test_If_directoryCount_Returns_The_Number_Of_Child_Directories_Of_Given_Directory($directory_1){


		$this->file_repository->saveDirectory(new Directory(null, new SubmoduleId(1), $directory_1, 'Second Child',null));

		$number_of_child_directories = $this->file_repository->directoryCount(  /* 2 directories */
			new SubmoduleId(1), $directory_1, new QueryObject());

		$this->assertEquals($number_of_child_directories, 2);
	}


	/**
     * @depends test_If_move_Method_Changes_File_Directory
     */
	public function test_If_fileCount_Returns_The_Number_Of_Child_Files_Of_Given_Directory($directory_1){


		$this->file_repository->saveFile(new File(null, new SubmoduleId(1), $directory_1, 'base64', 'Second Child', null));

		$number_of_child_files = $this->file_repository->fileCount(	 /* 2 files */
			new SubmoduleId(1), $directory_1, new QueryObject());

		$this->assertEquals(2, $number_of_child_files);
	}
}

?>