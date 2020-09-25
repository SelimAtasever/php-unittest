<?php

use \model\ProcedureManagement\infrastructure\ApplicationRepository;
use \model\common\domain\model\FormData;
use \model\ProcedureManagement\domain\model\ProcedureId;
use \model\ProcedureManagement\domain\model\InitiatorId;
use \model\ProcedureManagement\domain\model\ApplicationId;
use \model\ProcedureManagement\domain\model\InitiatorType;
use \model\ProcedureManagement\infrastructure\IApplicationFileLocator;

use PHPUnit\Framework\TestCase;

class ApplicationRepositoryTest extends TestCase{

	private static \DB $db;
	private IApplicationFileLocator $application_file_locator;
	private IApplicationFileLocator $application_file_bin_locator;

	public static function setUpBeforeClass() : void {
    	
    	global $framework;
        $config = $framework->get('config');

        self::$db = new \DB(
            $config->get('db_procedure_management_type'),
            $config->get('db_procedure_management_hostname'),
            $config->get('db_procedure_management_username'),
            $config->get('db_procedure_management_password'),
            $config->get('db_procedure_management_database'),
            $config->get('db_procedure_management_port')
        );

       self::$db->command("DELETE FROM application");
       self::$db->command("DELETE FROM application_bin");
	}

	protected function setUp() : void {

		$this->application_file_locator = $this->createMock(IApplicationFileLocator::class);
		$this->application_file_locator->method('locate')->willReturn(DIR_REPOSITORY . 'repo/test/folder.txt');

		$this->application_file_bin_locator = $this->createMock(IApplicationFileLocator::class);
		$this->application_file_bin_locator->method('locate')->willReturn(DIR_REPOSITORY . 'repo/test_bin/folder.txt');
	}

	public function test_If_save_Method_Stores_New_Data_To_Step_Application_Table(){

		$application_repository = new ApplicationRepository(
			self::$db, $this->application_file_locator, $this->application_file_bin_locator);

		$files_arr = array();

		$application_id = $application_repository->save(
			new FormData('file', $files_arr), 
			new ProcedureId(1), 
			InitiatorType::Individual(),
			new InitiatorId(1234567890));

		$id = $application_id->getId();

		$application_db = self::$db->query('SELECT * FROM application WHERE id = :id', array(
			':id' => $application_id->getId()
		))->row;

		$this->assertEquals($application_db['id'], $id);	
	}	

	
	public function test_If_remove_Method_Moves_New_Attachment_To_Application_Bin(){

		$application_repository = new ApplicationRepository(
			self::$db, $this->application_file_locator, $this->application_file_bin_locator);

		$files_arr = array();

		$application_id = $application_repository->save(
			new FormData('file', $files_arr), 
			new ProcedureId(2), 
			InitiatorType::Individual(),
			new InitiatorId(1234567890));

		$id = $application_id->getId();

		$application_repository->remove($application_id);

		$application_bin_db = self::$db->query('SELECT * FROM application_bin WHERE id =:id', array(
			':id' => $application_id->getId()
		))->row;

		$this->assertEquals($application_bin_db['id'], $id);
	}	

}

?>