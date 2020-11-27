<?php

use \model\ProcedureManagement\application\ProcedureQueryService;

use \model\ProcedureManagement\application\IApplicationFileDirectAccessLinkProvider;
use \model\ProcedureManagement\application\IFileDirectAccessLinkProvider;
use \model\ProcedureManagement\application\IIdentityProvider;
use \model\ProcedureManagement\application\IDepartmentProvider;

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

		$application_file_direct_access_link_provider = $this->createMock(IApplicationFileDirectAccessLinkProvider::class);
		$application_file_direct_access_link_provider->method('getLink')->willReturn('second_path_string');

		$iidentity_provider = $this->createMock(IIdentityProvider::class);
		$iidentity_provider->method('identity')->willReturn(1);

		$department_provider = $this->createMock(IDepartmentProvider::class);
		$department_provider->method('department')->willReturn(1);

		$procedure_query_service = new ProcedureQueryService(
			self::$db, $file_direct_access_link_provider, $application_file_direct_access_link_provider, $iidentity_provider, $department_provider);

		self::$db->command("INSERT INTO step(id,procedure_id,title,is_complete,`order`) VALUES (1,1,'first_title',1,1)");

		$step_dto = $procedure_query_service->fetchProceduresInProgress(new QueryObject());
		$this->assertNotEmpty($step_dto);

	}

	// public function test_If_fetchContainers_Retunrs_Procedure_DTO(){

	// 	$file_direct_access_link_provider = $this->createMock(IFileDirectAccessLinkProvider::class);
	// 	$file_direct_access_link_provider->method('getLink')->willReturn('path string...');

	// 	$application_file_direct_access_link_provider = $this->createMock(IApplicationFileDirectAccessLinkProvider::class);
	// 	$application_file_direct_access_link_provider->method('getLink')->willReturn('second_path_string');

	// 	$iidentity_provider = $this->createMock(IIdentityProvider::class);
	// 	$iidentity_provider->method('identity')->willReturn(1);

	// 	$department_provider = $this->createMock(IDepartmentProvider::class);
	// 	$department_provider->method('department')->willReturn(1);

	// 	$procedure_query_service = new ProcedureQueryService(
	// 		self::$db, $file_direct_access_link_provider, $application_file_direct_access_link_provider, $iidentity_provider, $department_provider);

	// 	self::$db->command("INSERT INTO container(id,type) VALUES(1,1)");

	// 	$container_dto = $procedure_query_service->fetchContainers(new QueryObject());
	// 	$this->assertNotEmpty($container_dto);
	// }

	public function test_If_getContainer_Returns_The_Container_From_Db(){

		self::$db->command("INSERT INTO container(id, type) VALUES('1', 1)");

		$file_direct_access_link_provider = $this->createMock(IFileDirectAccessLinkProvider::class);
		$file_direct_access_link_provider->method('getLink')->willReturn('path string...');

		$application_file_direct_access_link_provider = $this->createMock(IApplicationFileDirectAccessLinkProvider::class);
		$application_file_direct_access_link_provider->method('getLink')->willReturn('second_path_string');

		$iidentity_provider = $this->createMock(IIdentityProvider::class);
		$iidentity_provider->method('identity')->willReturn(1);

		$department_provider = $this->createMock(IDepartmentProvider::class);
		$department_provider->method('department')->willReturn(1);

		$procedure_query_service = new ProcedureQueryService(
			self::$db, $file_direct_access_link_provider, $application_file_direct_access_link_provider, $iidentity_provider, $department_provider);

		$container_dto = $procedure_query_service->getContainer('1');

		$container_arr = json_decode(json_encode($container_dto), true);

		$this->assertEquals($container_arr['id'], 1);
		$this->assertEquals($container_arr['attributes']['type'], 1);

	}

	public function test_getContainer_Throws_An_Exception_If_Container_Isnt_Found(){

		$this->expectException(ContainerNotFoundException::class);
		
		$file_direct_access_link_provider = $this->createMock(IFileDirectAccessLinkProvider::class);
		$file_direct_access_link_provider->method('getLink')->willReturn('path string');

		$application_file_direct_access_link_provider = $this->createMock(IApplicationFileDirectAccessLinkProvider::class);
		$application_file_direct_access_link_provider->method('getLink')->willReturn('second_path_string');

		$iidentity_provider = $this->createMock(IIdentityProvider::class);
		$iidentity_provider->method('identity')->willReturn(1);

		$department_provider = $this->createMock(IDepartmentProvider::class);
		$department_provider->method('department')->willReturn(1);

		$procedure_query_service = new ProcedureQueryService(
			self::$db, $file_direct_access_link_provider, $application_file_direct_access_link_provider, $iidentity_provider, $department_provider);

		$procedure_query_service->getContainer(99); //this will throw excp. theres no container with id:99
	}

	public function test_If_fetchContainerProcedures_Returns_Procedures_With_ContainerId(){

		$file_direct_access_link_provider = $this->createMock(IFileDirectAccessLinkProvider::class);
		$file_direct_access_link_provider->method('getLink')->willReturn('path string..');

		$application_file_direct_access_link_provider = $this->createMock(IApplicationFileDirectAccessLinkProvider::class);
		$application_file_direct_access_link_provider->method('getLink')->willReturn('second_path_string');

		$iidentity_provider = $this->createMock(IIdentityProvider::class);
		$iidentity_provider->method('identity')->willReturn(1);

		$department_provider = $this->createMock(IDepartmentProvider::class);
		$department_provider->method('department')->willReturn(1);

		$procedure_query_service = new ProcedureQueryService(
			self::$db, $file_direct_access_link_provider, $application_file_direct_access_link_provider, $iidentity_provider, $department_provider);

		self::$db->insert('procedure', array( 	

			'id' => 1,
			'container_id' => 1,
			'initiator_id' => 1234567890,
			'title' => 'unique title',
			'type' => 1,
			'date_created' => (new \DateTime())->format('Y-m-d H:i:s'),
			'department' => 1
		));

		$procedure_dtos = $procedure_query_service->fetchContainerProcedures(1);
		$this->assertNotEmpty($procedure_dtos);

	}

	public function test_If_getProcedure_Returns_The_Procedure_With_Called_Id(){

		$file_direct_access_link_provider = $this->createMock(IFileDirectAccessLinkProvider::class);
		$file_direct_access_link_provider->method('getLink')->willReturn('path string.....');

		$application_file_direct_access_link_provider = $this->createMock(IApplicationFileDirectAccessLinkProvider::class);
		$application_file_direct_access_link_provider->method('getLink')->willReturn('second_path_string');

		$iidentity_provider = $this->createMock(IIdentityProvider::class);
		$iidentity_provider->method('identity')->willReturn(1);

		$department_provider = $this->createMock(IDepartmentProvider::class);
		$department_provider->method('department')->willReturn(1);

		$procedure_query_service = new ProcedureQueryService(
			self::$db, $file_direct_access_link_provider, $application_file_direct_access_link_provider, $iidentity_provider, $department_provider);

		$returned_procedure_dto = $procedure_query_service->getProcedure(1);
		$this->assertNotEmpty($returned_procedure_dto);
	}	

	public function test_If_getProcedure_Throws_Exception_When_Procedure_Isnt_Found(){

		$this->expectException(ProcedureNotFoundException::class); 

		$file_direct_access_link_provider = $this->createMock(IFileDirectAccessLinkProvider::class);
		$file_direct_access_link_provider->method('getLink')->willReturn('path string....');

		$application_file_direct_access_link_provider = $this->createMock(IApplicationFileDirectAccessLinkProvider::class);
		$application_file_direct_access_link_provider->method('getLink')->willReturn('second_path_string');

		$iidentity_provider = $this->createMock(IIdentityProvider::class);
		$iidentity_provider->method('identity')->willReturn(1);

		$department_provider = $this->createMock(IDepartmentProvider::class);
		$department_provider->method('department')->willReturn(1);

		$procedure_query_service = new ProcedureQueryService(
			self::$db, $file_direct_access_link_provider, $application_file_direct_access_link_provider, $iidentity_provider, $department_provider);

		$procedure_query_service->getProcedure(2);
	
	}

	public function test_If_fetchProcedureSteps_Returns_Steps_From_Db(){

		$file_direct_access_link_provider = $this->createMock(IFileDirectAccessLinkProvider::class);
		$file_direct_access_link_provider->method('getLink')->willReturn('path string....');

		$application_file_direct_access_link_provider = $this->createMock(IApplicationFileDirectAccessLinkProvider::class);
		$application_file_direct_access_link_provider->method('getLink')->willReturn('second_path_string');

		$iidentity_provider = $this->createMock(IIdentityProvider::class);
		$iidentity_provider->method('identity')->willReturn(1);

		$department_provider = $this->createMock(IDepartmentProvider::class);
		$department_provider->method('department')->willReturn(1);

		$procedure_query_service = new ProcedureQueryService(
			self::$db, $file_direct_access_link_provider, $application_file_direct_access_link_provider, $iidentity_provider, $department_provider);

		self::$db->insert('step', array(
			'id' => 2,
			'procedure_id' => 1,
			'title' => 'procedure_title',
			'is_complete' => 1,
			'order' =>2
		));

		$returned_steps = $procedure_query_service->fetchProcedureSteps(1);	
		$this->assertNotEmpty($returned_steps);
	}	

	public function test_fetchProcedureSteps_Throws_Exception_If_Procedure_Isnt_Found(){

		$this->expectException(ProcedureNotFoundException::class);

		$file_direct_access_link_provider = $this->createMock(IFileDirectAccessLinkProvider::class);
		$file_direct_access_link_provider->method('getLink')->willReturn('path string...');

		$application_file_direct_access_link_provider = $this->createMock(IApplicationFileDirectAccessLinkProvider::class);
		$application_file_direct_access_link_provider->method('getLink')->willReturn('second_path_string');

		$iidentity_provider = $this->createMock(IIdentityProvider::class);
		$iidentity_provider->method('identity')->willReturn(1);

		$department_provider = $this->createMock(IDepartmentProvider::class);
		$department_provider->method('department')->willReturn(1);

		$procedure_query_service = new ProcedureQueryService(
			self::$db, $file_direct_access_link_provider, $application_file_direct_access_link_provider, $iidentity_provider, $department_provider);

		$procedure_query_service->fetchProcedureSteps(22); // no procedureid:22, throws excp.
	}


	public function test_If_getStep_Returns_The_Step_View_Model(){

		$file_direct_access_link_provider = $this->createMock(IFileDirectAccessLinkProvider::class);
		$file_direct_access_link_provider->method('getLink')->willReturn('path string....');

		$application_file_direct_access_link_provider = $this->createMock(IApplicationFileDirectAccessLinkProvider::class);
		$application_file_direct_access_link_provider->method('getLink')->willReturn('second_path_string');

		$iidentity_provider = $this->createMock(IIdentityProvider::class);
		$iidentity_provider->method('identity')->willReturn(1);

		$department_provider = $this->createMock(IDepartmentProvider::class);
		$department_provider->method('department')->willReturn(1);

		$procedure_query_service = new ProcedureQueryService(
			self::$db, $file_direct_access_link_provider, $application_file_direct_access_link_provider, $iidentity_provider, $department_provider);

		$returned_step_VM = $procedure_query_service->getStep(1);
		$this->assertNotEmpty($returned_step_VM);
	}

	public function test_getStep_Throws_Exception_If_Step_Isnt_Found(){

		$this->expectException(ProcedureNotFoundException::class);

		$file_direct_access_link_provider = $this->createMock(IFileDirectAccessLinkProvider::class);
		$file_direct_access_link_provider->method('getLink')->willReturn('path string...');

		$application_file_direct_access_link_provider = $this->createMock(IApplicationFileDirectAccessLinkProvider::class);
		$application_file_direct_access_link_provider->method('getLink')->willReturn('second_path_string');

		$iidentity_provider = $this->createMock(IIdentityProvider::class);
		$iidentity_provider->method('identity')->willReturn(1);

		$department_provider = $this->createMock(IDepartmentProvider::class);
		$department_provider->method('department')->willReturn(1);

		$procedure_query_service = new ProcedureQueryService(
			self::$db, $file_direct_access_link_provider, $application_file_direct_access_link_provider, $iidentity_provider, $department_provider);

		$procedure_query_service->getStep(666);

	}

	public function test_If_fetchStepComments_Returns_Comments(){

		$file_direct_access_link_provider = $this->createMock(IFileDirectAccessLinkProvider::class);
		$file_direct_access_link_provider->method('getLink')->willReturn('path string....');

		$application_file_direct_access_link_provider = $this->createMock(IApplicationFileDirectAccessLinkProvider::class);
		$application_file_direct_access_link_provider->method('getLink')->willReturn('second_path_string');

		$iidentity_provider = $this->createMock(IIdentityProvider::class);
		$iidentity_provider->method('identity')->willReturn(1);

		$department_provider = $this->createMock(IDepartmentProvider::class);
		$department_provider->method('department')->willReturn(1);

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

		$procedure_query_service = new ProcedureQueryService(
			self::$db, $file_direct_access_link_provider, $application_file_direct_access_link_provider, $iidentity_provider, $department_provider);
		$returned_comments = $procedure_query_service->fetchStepComments(1);

		$this->assertIsArray($returned_comments);
		$this->assertCount(2, $returned_comments);
	}

	public function test_If_fetchStepChoices_Comments_With_Given_StepId_From_Db(){

		$file_direct_access_link_provider = $this->createMock(IFileDirectAccessLinkProvider::class);
		$file_direct_access_link_provider->method('getLink')->willReturn('path string ...');

		$application_file_direct_access_link_provider = $this->createMock(IApplicationFileDirectAccessLinkProvider::class);
		$application_file_direct_access_link_provider->method('getLink')->willReturn('second_path_string');

		$iidentity_provider = $this->createMock(IIdentityProvider::class);
		$iidentity_provider->method('identity')->willReturn(1);

		$department_provider = $this->createMock(IDepartmentProvider::class);
		$department_provider->method('department')->willReturn(1);

		$procedure_query_service = new ProcedureQueryService(
			self::$db, $file_direct_access_link_provider, $application_file_direct_access_link_provider, $iidentity_provider, $department_provider);

		$comments_vm = $procedure_query_service->fetchStepChoices(1);
		$comments_arr = json_decode(json_encode($comments_vm), true);

		$third_comment_message = $comments_arr[2]['attributes']['message'];
		$this->assertEquals($third_comment_message, 'msg');

		$this->assertCount(3, $comments_arr); 
		/* 3 comment vm returned from db. */
	}


	public function test_fetchStepComments_Throws_Exception_If_Step_Isnt_Found(){

		$this->expectException(ProcedureNotFoundException::class);

		$file_direct_access_link_provider = $this->createMock(IFileDirectAccessLinkProvider::class);
		$file_direct_access_link_provider->method('getLink')->willReturn('path string ...');

		$application_file_direct_access_link_provider = $this->createMock(IApplicationFileDirectAccessLinkProvider::class);
		$application_file_direct_access_link_provider->method('getLink')->willReturn('second_path_string');

		$iidentity_provider = $this->createMock(IIdentityProvider::class);
		$iidentity_provider->method('identity')->willReturn(1);

		$department_provider = $this->createMock(IDepartmentProvider::class);
		$department_provider->method('department')->willReturn(1);

		$procedure_query_service = new ProcedureQueryService(
			self::$db, $file_direct_access_link_provider, $application_file_direct_access_link_provider, $iidentity_provider, $department_provider);

		$procedure_query_service->fetchStepComments(32);
	}

	public function test_If_getComment_Returns_The_Comment_View_Model(){

		$file_direct_access_link_provider = $this->createMock(IFileDirectAccessLinkProvider::class);
		$file_direct_access_link_provider->method('getLink')->willReturn('path to string..');

		$application_file_direct_access_link_provider = $this->createMock(IApplicationFileDirectAccessLinkProvider::class);
		$application_file_direct_access_link_provider->method('getLink')->willReturn('second_path_string');

		$iidentity_provider = $this->createMock(IIdentityProvider::class);
		$iidentity_provider->method('identity')->willReturn(1);

		$department_provider = $this->createMock(IDepartmentProvider::class);
		$department_provider->method('department')->willReturn(1);

		$procedure_query_service = new ProcedureQueryService(
			self::$db, $file_direct_access_link_provider, $application_file_direct_access_link_provider, $iidentity_provider, $department_provider);

		$returned_comment_VM = $procedure_query_service->getComment(1);
		$this->assertNotEmpty($returned_comment_VM);
	}

	public function test_getComment_Throws_Exception_When_If_Comment_Isnt_Found(){

		$this->expectException(CommentNotFoundException::class);

		$file_direct_access_link_provider = $this->createMock(IFileDirectAccessLinkProvider::class);
		$file_direct_access_link_provider->method('getLink')->willReturn('path string..');

		$application_file_direct_access_link_provider = $this->createMock(IApplicationFileDirectAccessLinkProvider::class);
		$application_file_direct_access_link_provider->method('getLink')->willReturn('second_path_string');

		$iidentity_provider = $this->createMock(IIdentityProvider::class);
		$iidentity_provider->method('identity')->willReturn(1);

		$department_provider = $this->createMock(IDepartmentProvider::class);
		$department_provider->method('department')->willReturn(1);

		$procedure_query_service = new ProcedureQueryService(
			self::$db, $file_direct_access_link_provider, $application_file_direct_access_link_provider, $iidentity_provider, $department_provider);

		$procedure_query_service->getComment(21);
	}


	public function test_If_getCommentator_Returns_The_Commentator_Id(){

		$file_direct_access_link_provider = $this->createMock(IFileDirectAccessLinkProvider::class);
		$file_direct_access_link_provider->method('getLink')->willReturn('path string ...');

		$application_file_direct_access_link_provider = $this->createMock(IApplicationFileDirectAccessLinkProvider::class);
		$application_file_direct_access_link_provider->method('getLink')->willReturn('second_path_string');

		$iidentity_provider = $this->createMock(IIdentityProvider::class);
		$iidentity_provider->method('identity')->willReturn(1);

		$department_provider = $this->createMock(IDepartmentProvider::class);
		$department_provider->method('department')->willReturn(1);

		$procedure_query_service = new ProcedureQueryService(
			self::$db, $file_direct_access_link_provider, $application_file_direct_access_link_provider, $iidentity_provider, $department_provider);

		$id_of_commentators = $procedure_query_service->getCommentator(1);
		$this->assertEquals($id_of_commentators, 3);
	}

	public function test_getCommentator_Throws_Exception_When_Comment_Isnt_Found(){

		$this->expectException(CommentNotFoundException::class);

		$file_direct_access_link_provider = $this->createMock(IFileDirectAccessLinkProvider::class);
		$file_direct_access_link_provider->method('getLink')->willReturn('path string....');

		$application_file_direct_access_link_provider = $this->createMock(IApplicationFileDirectAccessLinkProvider::class);
		$application_file_direct_access_link_provider->method('getLink')->willReturn('second_path_string');

		$iidentity_provider = $this->createMock(IIdentityProvider::class);
		$iidentity_provider->method('identity')->willReturn(1);

		$department_provider = $this->createMock(IDepartmentProvider::class);
		$department_provider->method('department')->willReturn(1);

		$procedure_query_service = new ProcedureQueryService(
			self::$db, $file_direct_access_link_provider, $application_file_direct_access_link_provider, $iidentity_provider, $department_provider);

		$procedure_query_service->getCommentator(9);
	}

	public function test_If_fetchStepAttachments_Returns_Step_Attachments(){

		$file_direct_access_link_provider = $this->createMock(IFileDirectAccessLinkProvider::class);
		$file_direct_access_link_provider->method('getLink')->willReturn('path to string...');

		$application_file_direct_access_link_provider = $this->createMock(IApplicationFileDirectAccessLinkProvider::class);
		$application_file_direct_access_link_provider->method('getLink')->willReturn('second_path_string');

		$iidentity_provider = $this->createMock(IIdentityProvider::class);
		$iidentity_provider->method('identity')->willReturn(1);

		$department_provider = $this->createMock(IDepartmentProvider::class);
		$department_provider->method('department')->willReturn(1);

		$procedure_query_service = new ProcedureQueryService(
			self::$db, $file_direct_access_link_provider, $application_file_direct_access_link_provider, $iidentity_provider, $department_provider);

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

		$this->expectException(ProcedureNotFoundException::class);

		$file_direct_access_link_provider = $this->createMock(IFileDirectAccessLinkProvider::class);
		$file_direct_access_link_provider->method('getLink')->willReturn('path to string....');

		$application_file_direct_access_link_provider = $this->createMock(IApplicationFileDirectAccessLinkProvider::class);
		$application_file_direct_access_link_provider->method('getLink')->willReturn('second_path_string');

		$iidentity_provider = $this->createMock(IIdentityProvider::class);
		$iidentity_provider->method('identity')->willReturn(1);

		$department_provider = $this->createMock(IDepartmentProvider::class);
		$department_provider->method('department')->willReturn(1);

		$procedure_query_service = new ProcedureQueryService(
			self::$db, $file_direct_access_link_provider, $application_file_direct_access_link_provider, $iidentity_provider, $department_provider);

		$procedure_query_service->fetchStepAttachments(33);
	}

	public function test_If_getAttachment_Returns_The_Attachment_View_Model(){

		$file_direct_access_link_provider = $this->createMock(IFileDirectAccessLinkProvider::class);
		$file_direct_access_link_provider->method('getLink')->willReturn('path as string...');

		$application_file_direct_access_link_provider = $this->createMock(IApplicationFileDirectAccessLinkProvider::class);
		$application_file_direct_access_link_provider->method('getLink')->willReturn('second_path_string');

		$iidentity_provider = $this->createMock(IIdentityProvider::class);
		$iidentity_provider->method('identity')->willReturn(1);

		$department_provider = $this->createMock(IDepartmentProvider::class);
		$department_provider->method('department')->willReturn(1);

		$procedure_query_service = new ProcedureQueryService(self::$db, $file_direct_access_link_provider, $application_file_direct_access_link_provider, $iidentity_provider, $department_provider);

		$returned_attachment_VM = $procedure_query_service->getAttachment(1);
		$this->assertNotEmpty($returned_attachment_VM);
	}

	public function test_getAttachment_Throws_Exception_If_Attachment_Isnt_Found(){

		$this->expectException(AttachmentNotFoundException::class);

		$file_direct_access_link_provider = $this->createMock(IFileDirectAccessLinkProvider::class);
		$file_direct_access_link_provider->method('getLink')->willReturn('path as string....');

		$application_file_direct_access_link_provider = $this->createMock(IApplicationFileDirectAccessLinkProvider::class);
		$application_file_direct_access_link_provider->method('getLink')->willReturn('second_path_string');

		$iidentity_provider = $this->createMock(IIdentityProvider::class);
		$iidentity_provider->method('identity')->willReturn(1);

		$department_provider = $this->createMock(IDepartmentProvider::class);
		$department_provider->method('department')->willReturn(1);

		$procedure_query_service = new ProcedureQueryService(
			self::$db, $file_direct_access_link_provider, $application_file_direct_access_link_provider, $iidentity_provider, $department_provider);

		$procedure_query_service->getAttachment(33);
	}

	public function test_If_getUploader_Returns_Uploaders_Id(){

		$file_direct_access_link_provider = $this->createMock(IFileDirectAccessLinkProvider::class);
		$file_direct_access_link_provider->method('getLink')->willReturn('path as string...');

		$application_file_direct_access_link_provider = $this->createMock(IApplicationFileDirectAccessLinkProvider::class);
		$application_file_direct_access_link_provider->method('getLink')->willReturn('second_path_string');

		$iidentity_provider = $this->createMock(IIdentityProvider::class);
		$iidentity_provider->method('identity')->willReturn(1);

		$department_provider = $this->createMock(IDepartmentProvider::class);
		$department_provider->method('department')->willReturn(1);

		$procedure_query_service = new ProcedureQueryService(
			self::$db, $file_direct_access_link_provider, $application_file_direct_access_link_provider, $iidentity_provider, $department_provider);

		$uploader_id = $procedure_query_service->getUploader(2);
		$this->assertEquals($uploader_id, 2);
	}

	public function test_getUploader_Throws_Exception_If_Attachment_Isnt_Found(){

		$this->expectException(AttachmentNotFoundException::class);

		$file_direct_access_link_provider = $this->createMock(IFileDirectAccessLinkProvider::class);
		$file_direct_access_link_provider->method('getLink')->willReturn('path as string.....');

		$application_file_direct_access_link_provider = $this->createMock(IApplicationFileDirectAccessLinkProvider::class);
		$application_file_direct_access_link_provider->method('getLink')->willReturn('second_path_string');

		$iidentity_provider = $this->createMock(IIdentityProvider::class);
		$iidentity_provider->method('identity')->willReturn(1);

		$department_provider = $this->createMock(IDepartmentProvider::class);
		$department_provider->method('department')->willReturn(1);

		$procedure_query_service = new ProcedureQueryService(
			self::$db, $file_direct_access_link_provider, $application_file_direct_access_link_provider, $iidentity_provider, $department_provider);

		$procedure_query_service->getUploader(21);
	}

	public function test_If_getApplication_Returns_The_Application_View_Model(){

		$file_direct_access_link_provider = $this->createMock(IFileDirectAccessLinkProvider::class);
		$file_direct_access_link_provider->method('getLink')->willReturn('path to string...');

		$application_file_direct_access_link_provider = $this->createMock(IApplicationFileDirectAccessLinkProvider::class);
		$application_file_direct_access_link_provider->method('getLink')->willReturn('second_path_string');

		$iidentity_provider = $this->createMock(IIdentityProvider::class);
		$iidentity_provider->method('identity')->willReturn(1);

		$department_provider = $this->createMock(IDepartmentProvider::class);
		$department_provider->method('department')->willReturn(1);

		$procedure_query_service = new ProcedureQueryService(
			self::$db, $file_direct_access_link_provider, $application_file_direct_access_link_provider, $iidentity_provider, $department_provider);

		$returned_application_VM = $procedure_query_service->getApplication(1);
		$this->assertNotEmpty($returned_application_VM);
	}

	public function test_If_fetchProcedureSubprocedures_Returns_Subprocedure_In_An_Array_From_Database(){

		$file_direct_access_link_provider = $this->createMock(IFileDirectAccessLinkProvider::class);
		$file_direct_access_link_provider->method('getLink')->willReturn('path to string...');

		$application_file_direct_access_link_provider = $this->createMock(IApplicationFileDirectAccessLinkProvider::class);
		$application_file_direct_access_link_provider->method('getLink')->willReturn('second_path_string');

		$iidentity_provider = $this->createMock(IIdentityProvider::class);
		$iidentity_provider->method('identity')->willReturn(1);

		$department_provider = $this->createMock(IDepartmentProvider::class);
		$department_provider->method('department')->willReturn(1);

		$procedure_query_service = new ProcedureQueryService(
			self::$db, $file_direct_access_link_provider, $application_file_direct_access_link_provider, $iidentity_provider, $department_provider);

		$returned_procedure_vm = $procedure_query_service->fetchProcedureSubprocedures(1);
		$arr = json_decode(json_encode($returned_procedure_vm[0]), true);

		$this->assertEquals(($arr['id']), 1 );
		$this->assertEquals(($arr['attributes']['title']), 'subprocedure_tite' );
	}

	public function test_If_getSubprocedure_Returns_Subprocedure_From_Database(){

		$file_direct_access_link_provider = $this->createMock(IFileDirectAccessLinkProvider::class);
		$file_direct_access_link_provider->method('getLink')->willReturn('path to string...');

		$application_file_direct_access_link_provider = $this->createMock(IApplicationFileDirectAccessLinkProvider::class);
		$application_file_direct_access_link_provider->method('getLink')->willReturn('second_path_string');

		$iidentity_provider = $this->createMock(IIdentityProvider::class);
		$iidentity_provider->method('identity')->willReturn(1);

		$department_provider = $this->createMock(IDepartmentProvider::class);
		$department_provider->method('department')->willReturn(1);

		$procedure_query_service = new ProcedureQueryService(
			self::$db, $file_direct_access_link_provider, $application_file_direct_access_link_provider, $iidentity_provider, $department_provider);

		$subprocedure_vm = $procedure_query_service->getSubprocedure(1);
		$subprocedure_arr = json_decode(json_encode($subprocedure_vm), true);

		$this->assertEquals($subprocedure_arr['id'], 1);
		$this->assertEquals(($subprocedure_arr['attributes']['title']), 'subprocedure_tite' );
	}

	public function test_If_An_Exception_Is_Thrown_When_Subprocedure_Isnt_Found(){

		$this->expectException(ProcedureNotFoundException::class);

		$file_direct_access_link_provider = $this->createMock(IFileDirectAccessLinkProvider::class);
		$file_direct_access_link_provider->method('getLink')->willReturn('path to string...');

		$application_file_direct_access_link_provider = $this->createMock(IApplicationFileDirectAccessLinkProvider::class);
		$application_file_direct_access_link_provider->method('getLink')->willReturn('second_path_string');

		$iidentity_provider = $this->createMock(IIdentityProvider::class);
		$iidentity_provider->method('identity')->willReturn(1);

		$department_provider = $this->createMock(IDepartmentProvider::class);
		$department_provider->method('department')->willReturn(1);

		$procedure_query_service = new ProcedureQueryService(
			self::$db, $file_direct_access_link_provider, $application_file_direct_access_link_provider, $iidentity_provider, $department_provider);

		$procedure_query_service->getSubprocedure(99);
	}

	public function test_If_fetchSubprocedureSteps_Returns_An_Array_Of_Step_From_Db(){

		$file_direct_access_link_provider = $this->createMock(IFileDirectAccessLinkProvider::class);
		$file_direct_access_link_provider->method('getLink')->willReturn('path to string...');

		$application_file_direct_access_link_provider = $this->createMock(IApplicationFileDirectAccessLinkProvider::class);
		$application_file_direct_access_link_provider->method('getLink')->willReturn('second_path_string');

		$iidentity_provider = $this->createMock(IIdentityProvider::class);
		$iidentity_provider->method('identity')->willReturn(1);

		$department_provider = $this->createMock(IDepartmentProvider::class);
		$department_provider->method('department')->willReturn(1);

		$procedure_query_service = new ProcedureQueryService(
			self::$db, $file_direct_access_link_provider, $application_file_direct_access_link_provider, $iidentity_provider, $department_provider);

		$steps_vm = $procedure_query_service->fetchSubprocedureSteps(1);
		$steps_arr = json_decode(json_encode($steps_vm), true);

		$this->assertEquals($steps_arr[0]['id'], 1);
		$this->assertEquals($steps_arr[0]['attributes']['title'], 'first_title');

	}

	public function test_If_getSubprocedureOfChoice_Returns_Subprocedure_From_Db(){

		$file_direct_access_link_provider = $this->createMock(IFileDirectAccessLinkProvider::class);
		$file_direct_access_link_provider->method('getLink')->willReturn('path to string...');

		$application_file_direct_access_link_provider = $this->createMock(IApplicationFileDirectAccessLinkProvider::class);
		$application_file_direct_access_link_provider->method('getLink')->willReturn('second_path_string');

		$iidentity_provider = $this->createMock(IIdentityProvider::class);
		$iidentity_provider->method('identity')->willReturn(1);

		$department_provider = $this->createMock(IDepartmentProvider::class);
		$department_provider->method('department')->willReturn(1);

		$procedure_query_service = new ProcedureQueryService(
			self::$db, $file_direct_access_link_provider, $application_file_direct_access_link_provider, $iidentity_provider, $department_provider);	

		$subprocedure_vm = $procedure_query_service->getSubprocedureOfChoice(1,1);  /* step_id <=> number */
		$subprocedure_arr = json_decode(json_encode($subprocedure_vm), true);

		$this->assertEquals($subprocedure_arr['id'], 1);
		$this->assertEquals($subprocedure_arr['attributes']['title'], 'subprocedure_tite');
	}

	public function test_If_getApplicationApplicant_Returns_Applicant_Information_Correctly(){

		$file_direct_access_link_provider = $this->createMock(IFileDirectAccessLinkProvider::class);
		$file_direct_access_link_provider->method('getLink')->willReturn('path to string...');

		$application_file_direct_access_link_provider = $this->createMock(IApplicationFileDirectAccessLinkProvider::class);
		$application_file_direct_access_link_provider->method('getLink')->willReturn('second_path_string');

		$iidentity_provider = $this->createMock(IIdentityProvider::class);
		$iidentity_provider->method('identity')->willReturn(1);

		$department_provider = $this->createMock(IDepartmentProvider::class);
		$department_provider->method('department')->willReturn(1);

		$procedure_query_service = new ProcedureQueryService(
			self::$db, $file_direct_access_link_provider, $application_file_direct_access_link_provider, $iidentity_provider, $department_provider);

		$applicant_info = $procedure_query_service->getApplicationApplicant(1);

		$this->assertEquals($applicant_info['id'], 11445566770);
		$this->assertEquals($applicant_info['type'], 1);

	}

	public function test_If_getNextStepOfChoice_Returns_Step_From_Db(){

		$file_direct_access_link_provider = $this->createMock(IFileDirectAccessLinkProvider::class);
		$file_direct_access_link_provider->method('getLink')->willReturn('path to string...');

		$application_file_direct_access_link_provider = $this->createMock(IApplicationFileDirectAccessLinkProvider::class);
		$application_file_direct_access_link_provider->method('getLink')->willReturn('second_path_string');

		$iidentity_provider = $this->createMock(IIdentityProvider::class);
		$iidentity_provider->method('identity')->willReturn(1);

		$department_provider = $this->createMock(IDepartmentProvider::class);
		$department_provider->method('department')->willReturn(1);

		$procedure_query_service = new ProcedureQueryService(
			self::$db, $file_direct_access_link_provider, $application_file_direct_access_link_provider, $iidentity_provider, $department_provider);

		$step_vm = $procedure_query_service->getNextStepOfChoice('1',2);
		$step_arr = json_decode(json_encode($step_vm), true);

		$this->assertEquals($step_arr['id'], 2);
		$this->assertTrue($step_arr['attributes']['is_complete']); /* is_complete bool */
	}

}

?>