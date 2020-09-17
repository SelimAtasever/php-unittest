<?php 

use \model\ProcedureManagement\application\ProcedureManagementService;

use \model\ProcedureManagement\infrastructure\ContainerRepository;
use \model\ProcedureManagement\infrastructure\ProcedureRepository;
use \model\ProcedureManagement\infrastructure\CommentRepository;
use \model\ProcedureManagement\infrastructure\AttachmentRepository;

use \model\ProcedureManagement\infrastructure\FileLocator;
use \model\ProcedureManagement\infrastructure\IdentityProvider;

use \model\common\ExceptionCollection;

use PHPUnit\Framework\TestCase;

class ProcedureManagementServiceDbTest extends TestCase{

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
       self::$db->command("DELETE FROM container");
       self::$db->command("DELETE FROM step");
       self::$db->command("DELETE FROM step_comment");
       self::$db->command("DELETE FROM step_attachment");
	}	

	public function test_If_startProcedure_Creates_A_New_Procedure_On_Db_And_Returns_Its_Id(){

		$identity_provider = new IdentityProvider(1);

		$file_locator = new FileLocator('./role_root_dir/');
		$file_bin_locator = new FileLocator('./role_root_bin_dir/');

		$attachment_repository = new AttachmentRepository(
			self::$db, $file_locator , $file_bin_locator , null
		);

		$container_repository = new ContainerRepository(self::$db);
		$procedure_repository = new ProcedureRepository(self::$db, null);
		$comment_repository = new CommentRepository(self::$db, null);

		$procedure_management_service = new ProcedureManagementService(
			$container_repository, $procedure_repository, $comment_repository, $attachment_repository, $identity_provider
		);

		self::$db->insert('container', array(
			'id' => 1,
			'type' => 1
		));

		$returned_id = $procedure_management_service->startProcedure(1,2);	

		$id_from_db = self::$db->query("SELECT * FROM `procedure` WHERE id = :id", array(
			':id' => $returned_id
		))->row['id'];	

		$this->assertEquals($returned_id, $id_from_db);
	}

	public function test_If_advanceProcedure_Completes_The_Procedure_Step(){

		$identity_provider = new IdentityProvider(1);

		$file_locator = new FileLocator('./role_root_dir/');
		$file_bin_locator = new FileLocator('./role_root_bin_dir/');

		$attachment_repository = new AttachmentRepository(
			self::$db, $file_locator , $file_bin_locator , null
		);

		$container_repository = new ContainerRepository(self::$db);
		$procedure_repository = new ProcedureRepository(self::$db, null);
		$comment_repository = new CommentRepository(self::$db, null);

		$procedure_management_service = new ProcedureManagementService(
			$container_repository, $procedure_repository, $comment_repository, $attachment_repository, $identity_provider
		);

		self::$db->insert('procedure', array(
			'id' => 1,
			'container_id' => 1,
			'initiator_id' => null,
			'title' => 'procedure title example',
			'type' => 2,
			'is_repeatable' => 0,
			'date_created' => (new DateTime())->format('Y-m-d H:i:s')
		));

		self::$db->insert('step', array(
			'id' => 1,
			'procedure_id' => 1,
			'title' => 'step title example',
			'is_complete' => 0,	 	// isComplete will be 1 after calling the function
		));

		$procedure_management_service->advanceProcedure(1,1);

		$confirm_iscomplete = self::$db->query("SELECT * FROM step WHERE id=1")->row['is_complete'];
		$this->assertEquals($confirm_iscomplete, 1);
	}

	public function test_If_cancelProcedure_Removes_Procedure_With_Given_Id() {

		$identity_provider = new IdentityProvider(1);

		$file_locator = new FileLocator('./role_root_dir/');
		$file_bin_locator = new FileLocator('./role_root_bin_dir/');

		$attachment_repository = new AttachmentRepository(
			self::$db, $file_locator , $file_bin_locator , null
		);

		$container_repository = new ContainerRepository(self::$db);
		$procedure_repository = new ProcedureRepository(self::$db, null);
		$comment_repository = new CommentRepository(self::$db, null);

		$procedure_management_service = new ProcedureManagementService(
			$container_repository, $procedure_repository, $comment_repository, $attachment_repository, $identity_provider
		);

		self::$db->insert('procedure', array(
			'id' => 2,
			'container_id' => 1,
			'initiator_id' => null,
			'title' => 'procedure to be deleted',
			'type' => 2,
			'is_repeatable' => 0,
			'date_created' => (new DateTime())->format('Y-m-d H:i:s')
		));

		self::$db->insert('step', array(
			'id' => 2,
			'procedure_id' => 2,
			'title' => 'title example',
			'is_complete' => 0,	 	
		));

		$procedure_management_service->cancelProcedure(2);
		$this->assertEmpty(self::$db->query("SELECT * FROM `procedure` WHERE id=2")->row);
	}	

	public function test_If_comment_Method_Creates_A_New_Comment_On_Db_And_Return_Its_Id(){

		$identity_provider = new IdentityProvider(1);

		$file_locator = new FileLocator('./role_root_dir/');
		$file_bin_locator = new FileLocator('./role_root_bin_dir/');

		$attachment_repository = new AttachmentRepository(
			self::$db, $file_locator , $file_bin_locator , null
		);

		$container_repository = new ContainerRepository(self::$db);
		$procedure_repository = new ProcedureRepository(self::$db, null);
		$comment_repository = new CommentRepository(self::$db, null);

		$procedure_management_service = new ProcedureManagementService(
			$container_repository, $procedure_repository, $comment_repository, $attachment_repository, $identity_provider
		);

		$returned_comment_id = $procedure_management_service->comment(1,1, 'this is the testing comment'); /*procedure id, step id*/
		$db_comment_id = self::$db->query("SELECT * FROM step_comment WHERE id = :id", array(
			':id' => $returned_comment_id
		))->row['id']; 

		$this->assertEquals($returned_comment_id, $db_comment_id);
	}

	public function test_If_editComment_Updates_Comment_With_Given_Id(){

		$identity_provider = new IdentityProvider(1);

		$file_locator = new FileLocator('./role_root_dir/');
		$file_bin_locator = new FileLocator('./role_root_bin_dir/');

		$attachment_repository = new AttachmentRepository(
			self::$db, $file_locator , $file_bin_locator , null
		);

		$container_repository = new ContainerRepository(self::$db);
		$procedure_repository = new ProcedureRepository(self::$db, null);
		$comment_repository = new CommentRepository(self::$db, null);

		$procedure_management_service = new ProcedureManagementService(
			$container_repository, $procedure_repository, $comment_repository, $attachment_repository, $identity_provider
		);

		$comment_id = $procedure_management_service->comment(1,1, 'comment to be updated......');
		$procedure_management_service->editComment(1, $comment_id, 'this comment has been updated');

		$confirm_comment_updated = self::$db->query("SELECT * FROM step_comment WHERE id = :id", array(
			':id' => $comment_id
		))->row['message'];

		$this->assertEquals('this comment has been updated', $confirm_comment_updated);
	}	

	public function test_If_removeComment_Removes_Comment_With_Given_Id(){

		$identity_provider = new IdentityProvider(1);

		$file_locator = new FileLocator('./role_root_dir/');
		$file_bin_locator = new FileLocator('./role_root_bin_dir/');

		$attachment_repository = new AttachmentRepository(
			self::$db, $file_locator , $file_bin_locator , null
		);

		$container_repository = new ContainerRepository(self::$db);
		$procedure_repository = new ProcedureRepository(self::$db, null);
		$comment_repository = new CommentRepository(self::$db, null);

		$procedure_management_service = new ProcedureManagementService(
			$container_repository, $procedure_repository, $comment_repository, $attachment_repository, $identity_provider
		);

		$comment_id = $procedure_management_service->comment(1,1, 'comment to be updated......');
		$procedure_management_service->removeComment($comment_id);

		$this->assertEmpty(self::$db->query("SELECT * FROM step_comment WHERE id = :id", array(
			':id' => $comment_id
		))->row);
	}

	public function test_If_addAttachment_Adds_A_New_Attachment_And_Returns_Its_Id(){

		$identity_provider = new IdentityProvider(1);

		$file_locator = new FileLocator('./role_root_dir/');
		$file_bin_locator = new FileLocator('./role_root_bin_dir/');

		$attachment_repository = new AttachmentRepository(
			self::$db, $file_locator , $file_bin_locator , null
		);

		$container_repository = new ContainerRepository(self::$db);
		$procedure_repository = new ProcedureRepository(self::$db, null);
		$comment_repository = new CommentRepository(self::$db, null);

		$procedure_management_service = new ProcedureManagementService(
			$container_repository, $procedure_repository, $comment_repository, $attachment_repository, $identity_provider
		);
		
		self::$db->insert('procedure', array(
			'id' => 2,
			'container_id' => 1,
			'initiator_id' => null,
			'title' => 'procedure title_2',
			'type' => 2,
			'is_repeatable' => 0,
			'date_created' => (new DateTime())->format('Y-m-d H:i:s')
		));

		self::$db->insert('step', array(
			'id' => 2,
			'procedure_id' => 2,
			'title' => 'title example',
			'is_complete' => 0,	 	
		));

		$returned_attachment_id = $procedure_management_service->addAttachment(2,2,'base64','attachment name.....');

		$db_attachment_id = self::$db->query("SELECT * FROM step_attachment WHERE id = :id", array(
			':id' => $returned_attachment_id
		))->row['id'];

		$this->assertEquals($returned_attachment_id, $db_attachment_id);
	
	}

	public function test_If_removeAttachment_Removes_Attachment_With_Given_Id(){

		$identity_provider = new IdentityProvider(1);

		$file_locator = new FileLocator('./role_root_dir/');
		$file_bin_locator = new FileLocator('./role_root_bin_dir/');

		$attachment_repository = new AttachmentRepository(
			self::$db, $file_locator , $file_bin_locator , null
		);

		$container_repository = new ContainerRepository(self::$db);
		$procedure_repository = new ProcedureRepository(self::$db, null);
		$comment_repository = new CommentRepository(self::$db, null);

		$procedure_management_service = new ProcedureManagementService(
			$container_repository, $procedure_repository, $comment_repository, $attachment_repository, $identity_provider
		);
		
		self::$db->insert('procedure', array(
			'id' => 3,
			'container_id' => 1,
			'initiator_id' => null,
			'title' => 'procedure title_3',
			'type' => 2,
			'is_repeatable' => 0,
			'date_created' => (new DateTime())->format('Y-m-d H:i:s')
		));

		self::$db->insert('step', array(
			'id' => 3,
			'procedure_id' => 3,
			'title' => 'title example2',
			'is_complete' => 0,	 	
		));

		$attachment_id = $procedure_management_service->addAttachment(3, 3, 'base64,', 'picture.png');
		$procedure_management_service->removeAttachment($attachment_id);

		$this->assertEmpty(self::$db->query("SELECT * FROM step_attachment WHERE id = :id", array(
			':id' => $attachment_id
		))->row); 
	}
}

?>