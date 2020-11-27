
<?php

use \model\ProcedureManagement\infrastructure\ProcedureRepository;
use \model\ProcedureManagement\domain\model\Procedure;
use \model\ProcedureManagement\domain\model\ProcedureId;
use \model\ProcedureManagement\domain\model\InitiatorId;
use \model\ProcedureManagement\domain\model\ProcedureType;
use \model\ProcedureManagement\domain\model\ContainerId;
use \model\ProcedureManagement\domain\model\ICommentRepository;
use \model\ProcedureManagement\domain\model\IAttachmentRepository;
use \model\ProcedureManagement\domain\model\Step;
use \model\ProcedureManagement\domain\model\StepId;
use \model\ProcedureManagement\domain\model\DepartmentId;

use PHPUnit\Framework\TestCase;

class ProcedureRepositoryTest extends TestCase{
 	private static \DB $db;

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

       self::$db->command("DELETE FROM `procedure`");

	}
	
	public function test_If_save_Method_Adds_A_New_Procedure_To_Db(){

        self::$db->command("DELETE FROM step");

		$procedure_repository = new ProcedureRepository(self::$db);

		$choices_arr = array();

			$steps_arr = [
				new Step(new StepId(1),'this is first title',true, true, $choices_arr, null, 1), 
				new Step(new StepId(2), 'this is second title',true, true, $choices_arr, null, 1),
				new Step(new StepId(3), 'this is third title',true,false, $choices_arr, null, 1) 			
			];

		$procedure_repository->save(

			$procedure = new Procedure(
					new ProcedureId(1), 
					new ContainerId(1), 
					null, 
					'this is the procedure title', 
					$steps_arr, 
					null,
					$steps_arr[2],
					ProcedureType::Numbering(),
					new DepartmentId(1)
				)
		,new ContainerId(1));

		$confirm_saved_on_db = self::$db->query("SELECT * FROM `procedure` WHERE id = 1")->rows;
		$this->assertNotEmpty($confirm_saved_on_db);
	}	

	public function test_If_find_Returns_The_Procedure_With_Given_Id(){

		$procedure_repository = new ProcedureRepository(self::$db);

		$procedure_with_idOne = $procedure_repository->find(new ProcedureId(1));
		$this->assertNotEmpty($procedure_with_idOne);

		$procedure_with_idTwo = $procedure_repository->find(new ProcedureId(2)); //theres no procedure with id=2 on db, should be empty.
		$this->assertEmpty($procedure_with_idTwo);
	}

	public function test_If_proceduresOfContainer_Returns_An_Array_Of_Procedures_With_Given_Id(){

		$procedure_repository = new ProcedureRepository(self::$db);
		
		$choices_arr = array();

		$steps_arr = [
			new Step(new StepId(1),'this is first title',true, true, $choices_arr, null, 1), 
			new Step(new StepId(2), 'this is second title',true, true, $choices_arr, null, 1),
			new Step(new StepId(3), 'this is third title',true,false, $choices_arr, null, 1) 			
		];

		$procedure_repository->save(  

		$procedure = new Procedure(
				new ProcedureId(1), 
				new ContainerId(1), 
				null, 
				'this is the procedure title', 
				$steps_arr, 
				null,
				$steps_arr[0],
				ProcedureType::Numbering(),
				new DepartmentId(1)
			)
		,new ContainerId(1));
	
		$arr_of_procedures = $procedure_repository->proceduresOfContainer(new ContainerId(1));

		$this->assertIsArray($arr_of_procedures);
		$this->assertNotEmpty($arr_of_procedures);
	}


	public function test_If_remove_Method_Carries_Procedure_To_Procedure_Bin(){

		$comment_repository = $this->createMock(ICommentRepository::class);
        $comment_repository->expects($this->any())
                 			->method('removeByStepId')
                 			->willReturn(true);


		$attachment_repository = $this->createMock(IAttachmentRepository::class);
        $attachment_repository->expects($this->any())
                 		      ->method('removeByStepId')
                 			  ->willReturn(true);

		$procedure_repository = new ProcedureRepository(self::$db);

		$choices_arr = array();

			$steps_arr = [
				new Step(new StepId(1),'this is first title',true, true, $choices_arr, null, 1), 
				new Step(new StepId(2), 'this is second title',true, true, $choices_arr, null, 1),
				new Step(new StepId(3), 'this is third title',true,false, $choices_arr, null, 1) 			
			];

		$procedure_repository->save(

			$procedure = new Procedure(
					new ProcedureId(1), 
					new ContainerId(1), 
					null, 
					'this is the procedure title', 
					$steps_arr, 
					null,
					$steps_arr[0],
					ProcedureType::Numbering(),
					new DepartmentId(1)
				)
		,new ContainerId(1) );


		$procedure_repository->remove(new ProcedureId(1), $comment_repository, $attachment_repository);

		$confirm_carried_to_bin = self::$db->query("SELECT * FROM procedure_bin WHERE id = 2");
		$this->assertNotEmpty($confirm_carried_to_bin);
	}

	public function test_If_nextProcedureId_Returns_A_New_Unique_Id(){

		$comment_repository = new ProcedureRepository(self::$db);

		$unique_id = $comment_repository->nextProcedureId();
		$this->assertNotEmpty($unique_id);
	}

	public function test_If_nextStepId_Returns_A_New_Unique_Id(){

		$comment_repository = new ProcedureRepository(self::$db);

		$unique_id = $comment_repository->nextStepId();
		$this->assertNotEmpty($unique_id);
	}
}

?>