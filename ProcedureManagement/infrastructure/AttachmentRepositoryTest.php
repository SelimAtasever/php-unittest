<?php

use model\ProcedureManagement\infrastructure\AttachmentRepository;
use model\ProcedureManagement\domain\model\Attachment;
use model\ProcedureManagement\domain\model\AttachmentId;
use model\ProcedureManagement\domain\model\StepId;
use model\ProcedureManagement\domain\model\PersonnelId;
use model\ProcedureManagement\infrastructure\IFileLocator;

use PHPUnit\Framework\TestCase;

class AttachmentRepositoryTest extends TestCase{

	private static \DB $db;
	private IFileLocator $file_locator;
	private IFileLocator $bin_locator;

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

       self::$db->command("DELETE FROM step_attachment");
       self::$db->command("DELETE FROM step_attachment_bin");
	}

	protected function setUp() : void {

        $this->file_locator = $this->createMock(IFileLocator::class);
        $this->file_locator->method('getFilePath')->willReturn(DIR_REPOSITORY . 'repo/test/dosya.txt');

        $this->file_bin_locator = $this->createMock(IFileLocator::class);
        $this->file_bin_locator->method('getFilePath')->willReturn(DIR_REPOSITORY . 'repo/test_bin/dosya.txt');

	}

	public function test_If_save_Method_Stores_New_Data_To_Step_Attachment_Table(){


		$attachment_repository = new AttachmentRepository(self::$db, $this->file_locator, $this->file_bin_locator);

		$attachment_repository->save(new Attachment(
			new AttachmentId(1),
			new StepId(1),
			new PersonnelId(1),
			'attachment name',
			'prefix,base64',
			new DateTime()
		));

		$step_attachment = self::$db->query('SELECT * FROM step_attachment WHERE id = 1');
		$this->assertNotEmpty($step_attachment);

	}

	public function test_If_find_Method_Returns_A_New_Attachment(){


		$attachment_repository = new AttachmentRepository(self::$db, $this->file_locator, $this->file_bin_locator);

		$attachment_from_db = $attachment_repository->find(new AttachmentId(1));
		$this->assertNotEmpty($attachment_repository);
	}

	public function test_If_remove_Method_Carries_New_Attachment_To_Step_Attachment_Bin(){


		$attachment_repository = new AttachmentRepository(self::$db, $this->file_locator, $this->file_bin_locator);

		$attachment_repository->save(new Attachment(
			new AttachmentId(2),
			new StepId(1),
			new PersonnelId(1),
			'attachment name',
			'prefix,base64',
			new DateTime()
		));

		$attachment_repository->remove(new AttachmentId(2));

		$step_attachment_bin = self::$db->query('SELECT * FROM step_attachment_bin WHERE id = 2');
		$this->assertNotEmpty($step_attachment_bin);

	}

	public function test_If_removeByStepId_Carries_Attachment_To_Step_Attachment_Bin(){

		$attachment_repository = new AttachmentRepository(self::$db, $this->file_locator, $this->file_bin_locator);

		$attachment_repository->save(
			new Attachment(
			new AttachmentId(3),
			new StepId(3),
			new PersonnelId(1),
			'attachment name',
			'prefix,base64',
			new DateTime()
		));

		$attachment_repository->removeByStepId(new StepId(3));
		
		$step_attachment_bin = self::$db->query("SELECT * FROM step_attachment_bin WHERE id=3");
		$this->assertNotEmpty($step_attachment_bin);
	}

	public function test_If_nextId_Returns_A_New_Unique_Id(){

		$attachment_repository = new AttachmentRepository(self::$db, $this->file_locator, $this->file_bin_locator);
		$new_id = $attachment_repository->nextId(new AttachmentId(1));
		$this->assertNotEmpty($new_id);
	}

}

?>
