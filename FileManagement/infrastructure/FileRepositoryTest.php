
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
	private static IFileRepository $file_repository;

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
        self::$db->command("DELETE FROM file");


		
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

	}


	public function testIfSaveDirectoryCreatesDirectoryOnDbAndFolder() { 

		$file_repository = new FileRepository($this->locator, $this->bin_locator);
		
		$directory_id = $file_repository->saveDirectory(new Directory(null, new SubmoduleId(1), null, 'folder-1', null));

		$check_folder_repo = self::$db->query("SELECT * FROM directory WHERE id = :id", array(
			':id'=>$directory_id->getId()
		))->row;

		$this->assertNotEmpty($check_folder_repo);	//checks db not empty.

		$folder = $this->locator->getRootDirectoryFor(new SubmoduleId(1)) . $directory_id->getId(); 

		$folder_exists = file_exists($folder);

		$this->assertNotEmpty($folder);      //checks folders not empty.
		

	}


	public function testIfSaveDirectoryCreatedDirectoryWithParentId () { 

		$file_repository = new FileRepository($this->locator, $this->bin_locator);

		$parent_id = $file_repository->saveDirectory(new Directory(null, new SubmoduleId(1), null, 'parent', null));
		
		$directory_id = $file_repository->saveDirectory(new Directory(null, new SubmoduleId(1), $parent_id, 'folder-1', null));

		$check_folder_repo = self::$db->query("SELECT * FROM directory WHERE id = :id", array(
			':id'=>$directory_id->getId()
		))->row;

		$this->assertNotEmpty($check_folder_repo);	

		$folder = $this->locator->getRootDirectoryFor(new SubmoduleId(1)) . $directory_id->getId(); 

		$folder_exists = file_exists($folder);

		$this->assertNotEmpty($folder);      
		

	}


	public function testIfRemoveDirectoryDeletesTheDirectory() {

		$file_repository = new FileRepository($this->locator, $this->bin_locator);

		$directory_id = $file_repository->saveDirectory(new Directory(null, new SubmoduleId(2), null, 'folder-7', null));

		$file_repository->removeDirectory($directory_id, new SubmoduleId(2));

		$check_folder_repo = self::$db->query("SELECT * FROM directory WHERE id = :id", array(
			':id'=>$directory_id->getId()
		))->row;

		$this->assertEmpty($check_folder_repo);

		$folder = $this->locator->getRootDirectoryFor(new SubmoduleId(2)) . $directory_id->getId(); 

		$folder_deleted = file_exists($folder);

		$this->assertFalse($folder_deleted);


	}


	public function testIfSaveFileCreatesFileOnDbAndFolder() {

		$file_repository = new FileRepository($this->locator, $this->bin_locator);

		$file_id = $file_repository->saveFile(new File(null, new SubmoduleId(1), null, 'base64', 'test-file', null));

		$check_file_repo = self::$db->query("SELECT * FROM file WHERE id = :id", array(
			':id'=>$file_id->getId()
		))->row;

		$this->assertNotEmpty($check_file_repo);

		$file = $this->locator->getRootDirectoryFor(new SubmoduleId(1)) . $file_id->getId();

		$folder_exists = file_exists($file);

		$this->assertNotEmpty($file);



	}

	public function testIfSaveFileSavedTheFileWithParentId () {

		$file_repository = new FileRepository($this->locator, $this->bin_locator);

		$parent_id = $file_repository->saveDirectory(new Directory(null, new SubmoduleId(2), null, 'folderx', null));

		$file_id = $file_repository->saveFile(new File(null, new SubmoduleId(2), $parent_id, 'base64', 'filex', null));
	
		$check_file_repo = self::$db->query("SELECT * FROM file WHERE id = id" ,array(
			':id' => $file_id->getId()
		))->row;

		$this->assertNotEmpty($check_file_repo);

		$file = $this->locator->getRootDirectoryFor(new SubmoduleId(2)) . $file_id->getId();

		$folder_exists = file_exists($file);

		$this->assertNotEmpty($file);

	}

	public function testIfRemoveFileDeletesTheFile() {

		$file_repository = new FileRepository($this->locator, $this->bin_locator);

		$file_id = $file_repository->saveFile(new File(null, new SubmoduleId(3), null, 'base64', 'test-file.jpg', null));

		$file_repository->removeFile($file_id, new SubmoduleId(3));

		$file_check = self::$db->query("SELECT * FROM file WHERE id = :id", array(
			':id' => $file_id->getId()
		))->row;

		$this->assertEmpty($file_check);

		$file = $this->bin_locator->getRootDirectoryFor(new SubmoduleId(3)) . $file_id->getId();

		$file_exists_in_bin = file_exists($file);

		$this->assertNotEmpty($file);
	
	}


	public function testIfSaveDirectoryUpdatedDirectoryLocation() {

		$file_repository = new FileRepository($this->locator, $this->bin_locator);

		$parent_folder = $file_repository->saveDirectory(new Directory(null, new SubmoduleId(1), null, 'first_folder', null));

		$parent_folder2 = $file_repository->saveDirectory(new Directory(null, new SubmoduleId(1), null, 'second_folder', null));

		$folder_id = $file_repository->saveDirectory(new Directory(null, new SubmoduleId(1), $parent_folder, 'child-folder', null));

		$db_check = self::$db->query("SELECT * FROM directory WHERE id = :id", array(
			':id' => $folder_id->getId()
		))->row;

		$this->assertNotEmpty($db_check);

		$return_folder = $file_repository->findDirectory($folder_id, new SubmoduleId(1));

		$return_folder->move($parent_folder2);

		$folder = $this->locator->getRootDirectoryFor(new SubmoduleId(1)) . $folder_id->getId();

		$folder_exists = file_exists($folder);

		$this->assertNotEmpty($folder);

	}


	public function testIfSaveFileUpdatesFileLocation () {


		$file_repository = new FileRepository($this->locator, $this->bin_locator);

		$parent_folder = $file_repository->saveDirectory(new Directory(null, new SubmoduleId(1), null, 'f1', null));

		$parent_folder2 = $file_repository->saveDirectory(new Directory(null, new SubmoduleId(1), null, 'f2', null));

		$child_file = $file_repository->saveFile(new File(null, new SubmoduleId(1), $parent_folder, 'base64', 'dosya.png', null));

		$db_check = self::$db->query("SELECT * FROM file WHERE id = :id", array(
			':id' => $child_file->getId()
		))->row;

		$this->assertNotEmpty($db_check);

		$return_file = $file_repository->findFile($child_file, new SubmoduleId(1));

		$return_file->move($parent_folder2);

		$file = $this->locator->getRootDirectoryFor(new SubmoduleId(1)) . $child_file->getId();

		$file_exists = file_exists($file);

		$this->assertNotEmpty($file);
	
	}


	public function testIfFindDirectoryFindsTheCorrectDirectory() {

		$file_repository = new FileRepository($this->locator, $this->bin_locator);

		$new_folder = $file_repository->saveDirectory(new Directory(null, new SubmoduleId(1), null, 'folder_1',null));

		$return_folder = $file_repository->findDirectory($new_folder, new SubmoduleId(1));

		$tmz = $return_folder->id()->equals($new_folder);

		$this->assertTrue($tmz);
	}



	public function testFindDirectoryWillReturnEmptyIfThereIsntWithGivenId() {

		$file_repository = new FileRepository($this->locator, $this->bin_locator);

		$foo = new DirectoryId('8asdasdadsaıojdajwıdjaoıjdoıawjdoajsdoıajwdo');

		$return_folder = $file_repository->findDirectory($foo, new SubmoduleId(1));

		$this->assertEmpty($return_folder);

	}

	public function testIfFindFileFindsTheCorrectFile() {

		$file_repository = new FileRepository($this->locator, $this->bin_locator);

		$new_file = $file_repository->saveFile(new File(null, new SubmoduleId(1), null, 'base64', 'file.png', null));

		$return_file = $file_repository->findfile($new_file, new SubmoduleId(1));

		$tmt = $return_file->id()->equals($new_file);

		$this->assertTrue($tmt);

	}

	public function testFindFileWillReturnEmptyIfThereIsntWithGivenId () {

		$file_repository = new FileRepository($this->locator, $this->bin_locator);

		$foo = new FileId('zzzzzzzzzzzzzzz');

		$return_file = $file_repository->findFile($foo, new SubmoduleId(1));

		$this->assertEmpty($return_file);

	}


	public function testFindDirectoryCannotFindIfSubmoduleIdIsDifferent() {

		$file_repository = new FileRepository($this->locator, $this->bin_locator);

		$new_directory = $file_repository->saveDirectory(new Directory(null, new SubmoduleId(1), null, 'foldes',null));

		$return_found = $file_repository->findDirectory($new_directory, new SubmoduleId(2));

		$this->assertEmpty($return_found);

	}

	public function testFindFileCannotFindIfSubmoduleIdIsDifferent() {

		$file_repository = new FileRepository($this->locator, $this->bin_locator);

		$new_file = $file_repository->saveFile(new File(null, new SubmoduleId(1), null, 'base64', 'file.png', null));

		$return_found = $file_repository->findFile($new_file, new SubmoduleId(2));

		$this->assertEmpty($return_found);

	}

	public function testFetchDirectoriesCantReturnDirectoryWithDifferentSubmoduleId() {

		$file_repository = new FileRepository($this->locator, $this->bin_locator);

		$first_folder = $file_repository->saveDirectory(new Directory(null, new SubmoduleId(5), null, 'folder1',null));
		$second_folder = $file_repository->saveDirectory(new Directory(null, new SubmoduleId(5), null, 'folder2',null));
		$third_folder = $file_repository->saveDirectory(new Directory(null, new SubmoduleId(6), null, 'folder3',null));

		$fetch_dir = $file_repository->fetchDirectories(new SubmoduleId(5), null, new QueryObject());

        $this->assertEquals(2,count($fetch_dir));


	}


	public function testFetchFilesCantReturnFileWithDifferentSubmoduleId () {

		$file_repository = new FileRepository($this->locator, $this->bin_locator);

		$first_file = $file_repository->saveFile(new File(null, new SubmoduleId(4), null, 'base64', 'file1.png', null));
		$second_file = $file_repository->saveFile(new File(null, new SubmoduleId(4), null, 'base64', 'file2.png', null));
		$third_file = $file_repository->saveFile(new File(null, new SubmoduleId(8), null, 'base64', 'file3.png', null));

		$fetch_file = $file_repository->fetchFiles(new SubmoduleId(4), null, new QueryObject());

		$this->assertEquals(2, count($fetch_file));
	}


}


?>