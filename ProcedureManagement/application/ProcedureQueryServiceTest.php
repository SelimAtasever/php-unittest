<?php

use \model\ProcedureManagement\application\ProcedureQueryService;
use \model\ProcedureManagement\application\IFileDirectAccessLinkProvider;

use \model\ProcedureManagement\application\exception\StepNotFoundException;
use \model\ProcedureManagement\application\exception\ContainerNotFoundException;
use \model\ProcedureManagement\application\exception\ProcedureNotFoundException;
use \model\ProcedureManagement\application\exception\CommentNotFoundException;
use \model\ProcedureManagement\application\exception\AttachmentNotFoundException;

use \model\common\QueryObject;
use PHPUnit\Framework\TestCase;

class ProcedureQueryServiceTest extends TestCase{

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

       self::$db->command("DELETE FROM step");
       self::$db->command("DELETE FROM container");
       self::$db->command("DELETE FROM `procedure`");
       self::$db->command("DELETE FROM step_comment");
       self::$db->command("DELETE FROM step_attachment");

	}	

	public function test_If_fetchProceduresInProgress_Returns_Procedure_DTO(){

		$file_direct_access_link_provider = $this->createMock(IFileDirectAccessLinkProvider::class);
		$file_direct_access_link_provider->method('getLink')->willReturn('path string...');

		$procedure_query_service = new ProcedureQueryService(self::$db, $file_direct_access_link_provider);

		self::$db->command("INSERT INTO step(id,procedure_id,title,is_complete,`order`) VALUES (1,1,'first_title',1,1)");

		$step_dto = $procedure_query_service->fetchProceduresInProgress(new QueryObject());
		$this->assertNotEmpty($step_dto);

	}

	public function test_If_fetchContainers_Retunrs_Procedure_DTO(){

		$file_direct_access_link_provider = $this->createMock(IFileDirectAccessLinkProvider::class);
		$file_direct_access_link_provider->method('getLink')->willReturn('path string...');

		$procedure_query_service = new ProcedureQueryService(self::$db, $file_direct_access_link_provider);

		self::$db->command("INSERT INTO container(id,type) VALUES(1,1)");

		$container_dto = $procedure_query_service->fetchContainers(new QueryObject());
		$this->assertNotEmpty($container_dto);
	}

	public function test_If_getContainer_Returns_The_Container_DTO(){

		$file_direct_access_link_provider = $this->createMock(IFileDirectAccessLinkProvider::class);
		$file_direct_access_link_provider->method('getLink')->willReturn('path string...');

		$procedure_query_service = new ProcedureQueryService(self::$db, $file_direct_access_link_provider);

		$container_dto = $procedure_query_service->getContainer(1);
		$this->assertNotEmpty($container_dto);
	}

	public function test_getContainer_Throws_An_Exception_If_Container_Isnt_Found(){

		$this->expectException(ContainerNotFoundException::class);
		
		$file_direct_access_link_provider = $this->createMock(IFileDirectAccessLinkProvider::class);
		$file_direct_access_link_provider->method('getLink')->willReturn('path string');

		$procedure_query_service = new ProcedureQueryService(self::$db, $file_direct_access_link_provider);
		$procedure_query_service->getContainer(99); //this will throw excp. theres no container with id:99
	}

	public function test_If_fetchContainerProcedures_Returns_Procedures_With_ContainerId(){

		$file_direct_access_link_provider = $this->createMock(IFileDirectAccessLinkProvider::class);
		$file_direct_access_link_provider->method('getLink')->willReturn('path string..');

		$procedure_query_service = new ProcedureQueryService(self::$db, $file_direct_access_link_provider);

		self::$db->insert('procedure', array( 	//kant framework

			'id' => 1,
			'container_id' => 1,
			'initiator_id' => 1234567890,
			'title' => 'procedure title',
			'type' => 1,
			'is_repeatable' => 1,
			'date_created' => (new \DateTime())->format('Y-m-d H:i:s')
		));

		$fetched_procedures = $procedure_query_service->fetchContainerProcedures(1);
		$this->assertNotEmpty($fetched_procedures);
	}

	public function test_If_getProcedure_Returns_The_Procedure_With_Called_Id(){

		$file_direct_access_link_provider = $this->createMock(IFileDirectAccessLinkProvider::class);
		$file_direct_access_link_provider->method('getLink')->willReturn('path string.....');

		$procedure_query_service = new ProcedureQueryService(self::$db, $file_direct_access_link_provider);

		$returned_procedure_dto = $procedure_query_service->getProcedure(1);
		$this->assertNotEmpty($returned_procedure_dto);
	}	

	public function test_If_getProcedure_Throws_Exception_When_Procedure_Isnt_Found(){

		$this->expectException(ProcedureNotFoundException::class); 

		$file_direct_access_link_provider = $this->createMock(IFileDirectAccessLinkProvider::class);
		$file_direct_access_link_provider->method
		('getLink')->willReturn('path string....');

		$procedure_query_service = new ProcedureQueryService(self::$db, $file_direct_access_link_provider);

		$procedure_query_service->getProcedure(2);
	
	}

	public function test_If_fetchProcedureSteps_Returns_Steps_With_Procedure_Id(){

		$file_direct_access_link_provider = $this->createMock(IFileDirectAccessLinkProvider::class);
		$file_direct_access_link_provider->method('getLink')->willReturn('path string....');

		$procedure_query_service = new ProcedureQueryService(self::$db, $file_direct_access_link_provider);

		self::$db->insert('step', array(
			'id' => 2,
			'procedure_id' => 1,
			'title' => 'procedure_title',
			'is_complete' => 1,
			'order' =>2
		));

		$returned_steps = $procedure_query_service->fetchProcedureSteps(1);
		
		$this->assertIsArray($returned_steps);
		$this->assertEquals(count($returned_steps), 2);
	}	

	public function test_fetchProcedureSteps_Throws_Exception_If_Procedure_Isnt_Found(){

		$this->expectException(ProcedureNotFoundException::class);

		$file_direct_access_link_provider = $this->createMock(IFileDirectAccessLinkProvider::class);
		$file_direct_access_link_provider->method('getLink')->willReturn('path string...');

		$procedure_query_service = new ProcedureQueryService(self::$db, $file_direct_access_link_provider);

		$procedure_query_service->fetchProcedureSteps(22); // no procedureid:22, throws excp.
	}


	public function test_If_getStep_Returns_The_Step_View_Model(){

		$file_direct_access_link_provider = $this->createMock(IFileDirectAccessLinkProvider::class);
		$file_direct_access_link_provider->method('getLink')->willReturn('path string....');

		$procedure_query_service = new ProcedureQueryService(self::$db, $file_direct_access_link_provider);

		$returned_step_VM = $procedure_query_service->getStep(1);
		$this->assertNotEmpty($returned_step_VM);
	}

	public function test_getStep_Throws_Exception_If_Step_Isnt_Found(){

		$this->expectException(StepNotFoundException::class);

		$file_direct_access_link_provider = $this->createMock(IFileDirectAccessLinkProvider::class);
		$file_direct_access_link_provider->method('getLink')->willReturn('path string...');

		$procedure_query_service = new ProcedureQueryService(self::$db, $file_direct_access_link_provider);

		$procedure_query_service->getStep(12);

	}

	public function test_If_fetchStepComments_Returns_Comments(){

		$file_direct_access_link_provider = $this->createMock(IFileDirectAccessLinkProvider::class);
		$file_direct_access_link_provider->method('getLink')->willReturn('path string....');

		self::$db->insert('step_comment', array(
			'id' => 1,
			'step_id' => 1,
			'commentator' =>3,
			'message' => '1st comment message',
			'edited_on' => (new DateTime())->format('Y-m-d H:i:s'),
			'commented_on' => (new DateTime())->format('Y-m-d H:i:s')
		));

		self::$db->insert('step_comment', array(
			'id' => 2,
			'step_id' => 1,
			'commentator' =>1,
			'message' => '2nd comment message',
			'edited_on' => (new DateTime())->format('Y-m-d H:i:s'),
			'commented_on' => (new DateTime())->format('Y-m-d H:i:s')
		));

		$procedure_query_service = new ProcedureQueryService(self::$db, $file_direct_access_link_provider);
		$returned_comments = $procedure_query_service->fetchStepComments(1);

		$this->assertIsArray($returned_comments);
		$this->assertEquals(count($returned_comments), 2);
	}

	public function test_fetchStepComments_Throws_Exception_If_Step_Isnt_Found(){

		$this->expectException(StepNotFoundException::class);

		$file_direct_access_link_provider = $this->createMock(IFileDirectAccessLinkProvider::class);
		$file_direct_access_link_provider->method('getLink')->willReturn('path string ...');

		$procedure_query_service = new ProcedureQueryService(self::$db, $file_direct_access_link_provider);

		$procedure_query_service->fetchStepComments(32);
	}

	public function test_If_getComment_Returns_The_Comment_View_Model(){

		$file_direct_access_link_provider = $this->createMock(IFileDirectAccessLinkProvider::class);
		$file_direct_access_link_provider->method('getLink')->willReturn('path to string..');

		$procedure_query_service = new ProcedureQueryService(self::$db, $file_direct_access_link_provider);

		$returned_comment_VM = $procedure_query_service->getComment(1);
		$this->assertNotEmpty($returned_comment_VM);
	}

	public function test_getComment_Throws_Exception_When_If_Comment_Isnt_Found(){

		$this->expectException(CommentNotFoundException::class);

		$file_direct_access_link_provider = $this->createMock(IFileDirectAccessLinkProvider::class);
		$file_direct_access_link_provider->method('getLink')->willReturn('path string..');

		$procedure_query_service = new ProcedureQueryService(self::$db, $file_direct_access_link_provider);
		$procedure_query_service->getComment(21);
	}


	public function test_If_getCommentator_Returns_The_Commentator_Id(){

		$file_direct_access_link_provider = $this->createMock(IFileDirectAccessLinkProvider::class);
		$file_direct_access_link_provider->method('getLink')->willReturn('path string ...');

		$procedure_query_service = new ProcedureQueryService(self::$db, $file_direct_access_link_provider);

		$id_of_commentators = $procedure_query_service->getCommentator(1);
		$this->assertEquals($id_of_commentators, 3);
	}

	public function test_getCommentator_Throws_Exception_When_Comment_Isnt_Found(){

		$this->expectException(CommentNotFoundException::class);

		$file_direct_access_link_provider = $this->createMock(IFileDirectAccessLinkProvider::class);
		$file_direct_access_link_provider->method('getLink')->willReturn('path string....');

		$procedure_query_service = new ProcedureQueryService(self::$db, $file_direct_access_link_provider);

		$procedure_query_service->getCommentator(9);
	}

	public function test_If_fetchStepAttachments_Returns_Step_Attachments(){

		$file_direct_access_link_provider = $this->createMock(IFileDirectAccessLinkProvider::class);
		$file_direct_access_link_provider->method('getLink')->willReturn('path to string...');

		$procedure_query_service = new ProcedureQueryService(self::$db, $file_direct_access_link_provider);

		self::$db->insert('step_attachment', array(
			'id' => 1,
			'step_id' =>1,
			'uploader'=>1,
			'name' => '1st attachment name',
			'prefix' => '1st prefix',
			'extension' => null,
			'date_added'=> (new DateTime())->format('Y-m-d H:i:s')
		));

		self::$db->insert('step_attachment', array(
			'id' => 2,
			'step_id' =>1,
			'uploader'=>2,
			'name' => '2nd attachment name',
			'prefix' => '2nd prefix',
			'extension' => null,
			'date_added'=> (new DateTime())->format('Y-m-d H:i:s')
		));

		$number_of_attachments = $procedure_query_service->fetchStepAttachments(1);

		$this->assertIsArray($number_of_attachments);
		$this->assertEquals(count($number_of_attachments), 2);
	}

	public function test_stepAttachments_Throws_Exception_If_Step_Isnt_Found(){

		$this->expectException(StepNotFoundException::class);

		$file_direct_access_link_provider = $this->createMock(IFileDirectAccessLinkProvider::class);
		$file_direct_access_link_provider->method('getLink')->willReturn('path to string....');

		$procedure_query_service = new ProcedureQueryService(self::$db, $file_direct_access_link_provider);
		$procedure_query_service->fetchStepAttachments(33);
	}

	public function test_If_getAttachment_Returns_The_Attachment_View_Model(){

		$file_direct_access_link_provider = $this->createMock(IFileDirectAccessLinkProvider::class);
		$file_direct_access_link_provider->method('getLink')->willReturn('path as string...');

		$procedure_query_service = new ProcedureQueryService(self::$db, $file_direct_access_link_provider);

		$returned_attachment_VM = $procedure_query_service->getAttachment(1);
		$this->assertNotEmpty($returned_attachment_VM);
	}

	public function test_getAttachment_Throws_Exception_If_Attachment_Isnt_Found(){

		$this->expectException(AttachmentNotFoundException::class);

		$file_direct_access_link_provider = $this->createMock(IFileDirectAccessLinkProvider::class);
		$file_direct_access_link_provider->method('getLink')->willReturn('path as string....');

		$procedure_query_service = new ProcedureQueryService(self::$db, $file_direct_access_link_provider);
		$procedure_query_service->getAttachment(33);
	}

	public function test_If_getUploader_Returns_Uploaders_Id(){

		$file_direct_access_link_provider = $this->createMock(IFileDirectAccessLinkProvider::class);
		$file_direct_access_link_provider->method('getLink')->willReturn('path as string...');

		$procedure_query_service = new ProcedureQueryService(self::$db, $file_direct_access_link_provider);

		$uploader_id = $procedure_query_service->getUploader(2);
		$this->assertEquals($uploader_id, 2);
	}

	public function test_getUploader_Throws_Exception_If_Attachment_Isnt_Found(){

		$this->expectException(AttachmentNotFoundException::class);

		$file_direct_access_link_provider = $this->createMock(IFileDirectAccessLinkProvider::class);
		$file_direct_access_link_provider->method('getLink')->willReturn('path as string.....');

		$procedure_query_service = new ProcedureQueryService(self::$db, $file_direct_access_link_provider);

		$procedure_query_service->getUploader(21);
	}

	public function test_If_getApplication_Returns_The_Application_View_Model(){

		$file_direct_access_link_provider = $this->createMock(IFileDirectAccessLinkProvider::class);
		$file_direct_access_link_provider->method('getLink')->willReturn('path to string...');

		$procedure_query_service = new ProcedureQueryService(self::$db, $file_direct_access_link_provider);

		$returned_application_VM = $procedure_query_service->getApplication(1);
		$this->assertNotEmpty($returned_application_VM);
	}
}

?>