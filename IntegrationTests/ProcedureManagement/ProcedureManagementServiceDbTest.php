<?php 

use \model\ProcedureManagement\application\ProcedureManagementService;

use \model\ProcedureManagement\infrastructure\ContainerRepository;
use \model\ProcedureManagement\infrastructure\ProcedureRepository;
use \model\ProcedureManagement\infrastructure\CommentRepository;
use \model\ProcedureManagement\infrastructure\AttachmentRepository;
use \model\ProcedureManagement\application\IDepartmentProvider;
use \model\ProcedureManagement\application\IContainerValidator;

use \model\ProcedureManagement\infrastructure\FileLocator;
use \model\ProcedureManagement\infrastructure\IdentityProvider;
use \model\ProcedureManagement\domain\model\exception\ProcedureDescriptionLenghtException;

use \model\common\ExceptionCollection;

use PHPUnit\Framework\TestCase;

class ProcedureManagementServiceDbTest extends TestCase{

	private static \DB $db;
	private $procedure_management_service;
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
       self::$db->command("DELETE FROM `subprocedure`");
       self::$db->command("DELETE FROM container");
       self::$db->command("DELETE FROM step");
       self::$db->command("DELETE FROM step_comment");
       self::$db->command("DELETE FROM step_choice");
       self::$db->command("DELETE FROM step_attachment");
	}	

	protected function setUp() : void {
		
		$identity_provider = new IdentityProvider(1);

		$file_locator = new FileLocator('./role_root_dir/');
		$file_bin_locator = new FileLocator('./role_root_bin_dir/');

		$department_provider_service = $this->createMock(IDepartmentProvider::class);
		$department_provider_service->method('department')->willReturn(1);

		$attachment_repository = new AttachmentRepository(
			self::$db, $file_locator , $file_bin_locator , null
		);

		$container_repository = new ContainerRepository(self::$db);

		$container_validator = $this->createMock(IContainerValidator::class);
		$container_validator->method('containerExists')->willReturn(true);

		$procedure_repository = new ProcedureRepository(self::$db, null);
		$comment_repository = new CommentRepository(self::$db, null);

		$this->procedure_management_service = new ProcedureManagementService(
			$container_repository, $container_validator, $procedure_repository, $comment_repository, $attachment_repository, $identity_provider, $department_provider_service
		);
	}

	public function test_If_startProcedure_Creates_A_New_Procedure_On_Db_And_Returns_Its_Id(){

		self::$db->insert('container', array(
			'id' => 1,
			'type' => 1
		));

		$returned_id = $this->procedure_management_service->startProcedure(1,1,1,'description I');	

		$id_from_db = self::$db->query("SELECT * FROM `procedure` WHERE id = :id", array(
			':id' => $returned_id
		))->row['id'];	

		$this->assertEquals($returned_id, $id_from_db);
	}

	public function test_startProcedure_Method_Creates_A_New_Container_With_Given_Id_If_It_Doesnt_Exist_Already(){

		$this->procedure_management_service->startProcedure(12,1,1, 'description II');

		$new_container_id = self::$db->query("SELECT * FROM container WHERE id = 12")->row['id'];

		$this->assertEquals($new_container_id, 12);
	}

	public function test_If_advanceProcedure_Completes_The_Procedure_Step(){

		self::$db->insert('procedure', array(
			'id' => 1,
			'container_id' => 1,
			'container_type' => 1,
			'initiator_id' => null,
			'title' => 'SD',
			'type' => 2,
			'date_created' => (new DateTime())->format('Y-m-d H:i:s'),
			'department' => 1,
			'current_step' => '1'
		) );

		self::$db->insert('step', array(
			'id' => 1,
			'procedure_id' => 1,
			'title' => 'step_1',
			'is_complete' => 0,	 	// isComplete will be 1 after calling the function
			'order' => 4,
			'out_of_scope' => 0,
			'activated_on' => null,
		));

		self::$db->insert('step_choice', array(
			'step_id' => 1,
			'message' => 'MSG',
			'next_step_id' => null,
			'subprocedure_id' => null,
			'type' => 1,
			'number' => 1
		) );

		$this->procedure_management_service->advanceProcedure(1,1,1);


		$confirm_iscomplete = self::$db->query("SELECT * FROM step WHERE id = 1")->row['is_complete'];

		$this->assertEquals($confirm_iscomplete, 1);

	}

	public function test_If_cancelProcedure_Removes_Procedure_With_Given_Id() {

		self::$db->insert('procedure', array(
			'id' => 2,
			'container_id' => 1,
			'container_type' => 1,
			'initiator_id' => null,
			'title' => 'procedure to be deleted',
			'type' => 2,
			'date_created' => (new DateTime())->format('Y-m-d H:i:s'),
			'department' => 1,
			'current_step' => 2
		));

		self::$db->insert('step', array(
			'id' => 2,
			'procedure_id' => 2,
			'title' => 'title example',
			'is_complete' => 0	
		));

		$this->procedure_management_service->cancelProcedure(2);
		$this->assertEmpty(self::$db->query("SELECT * FROM `procedure` WHERE id=2")->row);
	}	

	public function test_If_comment_Method_Creates_A_New_Comment_On_Db_And_Return_Its_Id(){

		$returned_comment_id = $this->procedure_management_service->comment(1,1, 'comment');	/*procedure id, step id*/

		$db_comment_id = self::$db->query("SELECT * FROM step_comment WHERE id = :id", array(
			':id' => $returned_comment_id
		))->row['id']; 

		$this->assertEquals($returned_comment_id, $db_comment_id);
	}

	public function test_If_editComment_Updates_Comment_With_Given_Id(){

		$comment_id = $this->procedure_management_service->comment(1,1, 'comment to be updated......');
		$this->procedure_management_service->editComment(1, $comment_id, 'this comment has been updated');

		$confirm_comment_updated = self::$db->query("SELECT * FROM step_comment WHERE id = :id", array(
			':id' => $comment_id
		))->row['message'];

		$this->assertEquals('this comment has been updated', $confirm_comment_updated);
	}	

	public function test_If_removeComment_Removes_Comment_With_Given_Id(){

		$comment_id = $this->procedure_management_service->comment(1,1, 'comment to be deleted......');
		$this->procedure_management_service->removeComment($comment_id);

		$this->assertEmpty(self::$db->query("SELECT * FROM step_comment WHERE id = :id", array(
			':id' => $comment_id
		))->row);
	}

	public function test_If_addAttachment_Adds_A_New_Attachment_And_Returns_Its_Id(){

		self::$db->insert('procedure', array(
			'id' => 2,
			'container_id' => 1,
			'container_type' => 1,
			'initiator_id' => null,
			'title' => 'procedure title_2',
			'type' => 2,
			'date_created' => (new DateTime())->format('Y-m-d H:i:s'),
			'department' => 1,
			'current_step' => 2
		));

		self::$db->insert('step', array(
			'id' => 2,
			'procedure_id' => 2,
			'title' => 'title example',
			'is_complete' => 0,
			'order' => 5,
			'out_of_scope' => 0,
			'activated_on' => null,

		));

		self::$db->insert('step_choice', array(
			'step_id' => 2,
			'message' => 'exception_preventer',
			'next_step_id' => null,
			'subprocedure_id' => null,
			'type' => 1,
			'number' => 1
		));

		$returned_attachment_id = $this->procedure_management_service->addAttachment(2,2,'base64','attachment name.....');

		$db_attachment_id = self::$db->query("SELECT * FROM step_attachment WHERE id = :id", array(
			':id' => $returned_attachment_id
		))->row['id'];

		$this->assertEquals($returned_attachment_id, $db_attachment_id);
	
	}

	public function test_If_removeAttachment_Removes_Attachment_With_Given_Id(){
		
		self::$db->insert('procedure', array(
			'id' => 3,
			'container_id' => 1,
			'container_type' => 1,
			'initiator_id' => null,
			'title' => 'procedure title_3',
			'type' => 2,
			'date_created' => (new DateTime())->format('Y-m-d H:i:s'),
			'department' => 1,
			'current_step' => 3
		));

		self::$db->insert('step', array(
			'id' => 3,
			'procedure_id' => 3,
			'title' => 'removed procedures step title',
			'is_complete' => 0,	 	
		));

		self::$db->insert('step_choice', array(
			'step_id' => 3,
			'message' => 'final',
			'next_step_id' => null,
			'subprocedure_id' => null,
			'type' => 1,
			'number' => 1
		));

		$attachment_id = $this->procedure_management_service->addAttachment(3, 3, 'base64,', 'picture.png');
		$this->procedure_management_service->removeAttachment($attachment_id);

		$this->assertEmpty(self::$db->query("SELECT * FROM step_attachment WHERE id = :id", array(
			':id' => $attachment_id
		))->row); 
	}

	public function test_If_advanceSubprocedure_Completes_The_Subprocedure_Step(){

		self::$db->insert('procedure', array(
			'id' => 4,
			'container_id' => 1,
			'container_type' => 1,
			'initiator_id' => null,
			'title' => 'finalll',
			'type' => 2,
			'date_created' => (new DateTime())->format('Y-m-d H:i:s'),
			'department' => 1,
			'current_step' => 3
		));

		self::$db->insert('step' , array( 
			'id' => 5,
			'procedure_id' => 4,
			'title' => 'exception_preventer',
			'is_complete' => 0
		));

		self::$db->insert('step_choice', array(
			'step_id' => 5,
			'message' => 'auf',
			'next_step_id' => null,
			'subprocedure_id' => null,
			'type' => 1,
			'number' => 5
		));

		self::$db->insert('step', array(
			'id' => 4,
			'procedure_id' => 7,
			'title' => 'final',
			'is_complete' => 0	 	
		));

		self::$db->insert('subprocedure', array(
			'id' => 7,
			'parent_id' => 4,
			'title' => 'subprocedure_title',
			'current_step' => 4,
			'is_active' => 1
		));

		self::$db->insert('step_choice', array(
			'step_id' => 4,
			'message' => 'final',
			'next_step_id' => null,
			'subprocedure_id' => 7,
			'type' => 1,
			'number' => 4 
		));

		$this->procedure_management_service->advanceSubprocedure(4,7,4); /*containerid, procedureid, subprocedureid, choice */

		$query_result = self::$db->query("SELECT is_complete FROM step WHERE id = 4")->row['is_complete'];
			
		$this->assertEquals(1, $query_result);
		
	}

	public function test_If_changeDescription_Changes_Existing_Procedure_Description_From_Db(){
		$this->procedure_management_service->changeDescription(1, 'NEW DESC');

		$query = self::$db->query("SELECT * FROM `procedure` WHERE id = :id", array(
			':id' => 1

		))->row['description'];
		$this->assertEquals($query, 'NEW DESC');
	}

	public function test_If_changeDescription_Throws_An_Exception_If_Description_Is_Lower_Than_2(){
		$this->expectException(ProcedureDescriptionLenghtException::class);

		try{

			$this->procedure_management_service->changeDescription(2, 'Q');	

		} catch(ExceptionCollection $e){
			$this->throwFromExceptionCollection($e, ProcedureDescriptionLenghtException::class);
		}  
	}

	public function test_If_changeDescription_Throws_An_Exception_If_Description_Is_Longer_Than_256(){
		$this->expectException(ProcedureDescriptionLenghtException::class);

		try{

			$this->procedure_management_service->changeDescription(2, str_repeat('T', 257));	

		} catch(ExceptionCollection $e){
			$this->throwFromExceptionCollection($e, ProcedureDescriptionLenghtException::class);
		}  
	}

	private function throwFromExceptionCollection($exception_collection, $exception) {
		foreach($exception_collection->getExceptions() as $e) {
			if(get_class($e) == $exception) {
				throw new $exception;
			}
		}
	}

}

?>