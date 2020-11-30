<?php

use \model\ProcedureManagement\application\ProcedureQueryService;

use \model\ProcedureManagement\infrastructure\FileDirectAccessLinkProvider;
use \model\ProcedureManagement\infrastructure\ApplicationFileDirectAccessLinkProvider;
use \model\ProcedureManagement\infrastructure\IdentityProvider;
use \model\ProcedureManagement\application\IDepartmentProvider;

use \model\ProcedureManagement\infrastructure\IApplicationFileLocator;
use \model\ProcedureManagement\infrastructure\FileLocator;

use \model\common\QueryObject;
use PHPUnit\Framework\TestCase;

class ProcedureQueryServiceDbTest extends TestCase {

	private static \DB $db;
	private $procedure_query_service;
	private static $jwToken;

 	public static function setUpBeforeClass() : void {
    	
    	global $framework;

    	self::$jwToken = $framework->get('jwt');
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

	protected function setUp() : void {

		$file_locator = new FileLocator('./role_root_dir/');
		$file_direct_access_link_provider = new FileDirectAccessLinkProvider(
			self::$jwToken,'https://kant.ist', $file_locator, '0.0.0.0 8.8.4.4'
		);

		$application_file_locator = $this->createMock(IApplicationFileLocator::class);
		$application_file_locator->method('locate')->willReturn('path');

		$application_link_provider = new ApplicationFileDirectAccessLinkProvider(
			self::$jwToken, 'https://kant.ist', $application_file_locator, '8.8.4.4');

		$identiy_provider = new IdentityProvider(1);

		$department_provider = $this->createMock(IDepartmentProvider::class);
		$department_provider->method('department')->willReturn(1);

		$this->procedure_query_service = new ProcedureQueryService(
			self::$db, $file_direct_access_link_provider, $application_link_provider, $identiy_provider, $department_provider
		);
	}


	public function test_If_getProcedure_Returns_Procedure_From_Db(){
		self::$db->insert('procedure', array(
			'id' => 1,
			'container_id' => 1,
			'initiator_id' => null,
			'title' => 'Procedure Title 1',
			'type' => 2,
			'date_created' => (new DateTime())->format('Y-m-d H:i:s'),
			'department' => 1,
			'current_step' => null
		));

		self::$db->insert('step', array(
			'id' => 1,
			'procedure_id' => 1,
			'title' => 'Step Title 1',
			'is_complete' => 0,
			'order' => 1

		));

		self::$db->insert('container', array(
			'id' => 1,
			'type' => 1
		));


		$procedure_dto = $this->procedure_query_service->getProcedure(1);


		$procedure_as_arr = json_decode(json_encode($procedure_dto), true); // without bool its not an array but stdClass.

		$id = $procedure_as_arr['id'];
		$title = $procedure_as_arr['attributes']['title'];

		$this->assertEquals($id, 1);
		$this->assertEquals($title, 'Procedure Title 1');
	}

	public function test_If_fetchProceduresInProgress_Returns_Procedures_In_Progress(){
		self::$db->insert('procedure', array(
			'id' => 2,
			'container_id' => 1,
			'initiator_id' => null,
			'title' => 'Procedure Title 2',
			'type' => 2,
			'date_created' => (new DateTime())->format('Y-m-d H:i:s'),
			'department' => 1,
			'current_step' => null
		));

		self::$db->insert('step', array(
			'id' => 2,
			'procedure_id' => 2,
			'title' => 'Step Title 2',
			'is_complete' => 0,
			'order' => 2
		));

		self::$db->insert('container', array(
			'id' => 2,
			'type' => 1
		));

		$procedures_dto = $this->procedure_query_service->fetchProceduresInProgress(new QueryObject());
		$procedures_arr = $procedures_dto->procedures();

		$this->assertIsArray($procedures_arr);
		$this->assertCount(2, $procedures_arr);
	}

	public function test_If_getContainer_Returns_Container_With_Given_Id_From_Db(){

		$container_dto = $this->procedure_query_service->getContainer(1);
		$container_arr = json_decode(json_encode($container_dto), true);

		$id = $container_arr['id'];
		$type = $container_arr['attributes']['type'];

		$this->assertEquals($id, 1);
		$this->assertEquals($type,1);
	}

	// public function test_If_fetchContainers_Returns_Containers_From_Db(){

	// 	$containers_dto = $this->procedure_query_service->fetchContainers(new QueryObject());
	// 	$containers_arr = $containers_dto->containers();

	// 	$this->assertIsArray($containers_arr);
	// 	$this->assertCount(2, $containers_arr);
	// }

	public function test_If_fetchContainerProcedures_Returns_Containers_Procedures(){

		$arr_container_procedures = $this->procedure_query_service->fetchContainerProcedures(1);
		$this->assertCount(2, $arr_container_procedures);
	}


	public function test_If_getAttachment_Returns_Attachment_From_Db(){

		self::$db->insert('step_attachment', array(
			'id' => 1,
			'step_id' => 1,
			'uploader' => 2,
			'name' => 'Step Attachment 1',
			'prefix' => 'Prefix 1',
			'extension' => '.doc',
			'date_added' => (new DateTime())->format('Y-m-d H:i:s')
		));

		$attachment_dto = $this->procedure_query_service->getAttachment(1);

		$attachment_arr = json_decode(json_encode($attachment_dto), true);

		$id = $attachment_arr['id'];
		$extension = $attachment_arr['attributes']['extension'];	
		$name = $attachment_arr['attributes']['name'];

		$this->assertEquals($id, 1);
		$this->assertEquals($extension, '.doc');
		$this->assertEquals($name, 'Step Attachment 1');	

	}

	public function test_If_fetchStepAttachments_Returns_Attachments_With_StepId(){

		self::$db->insert('step_attachment', array(
			'id' => 2,
			'step_id' => 1,
			'uploader' => 1,
			'name' => 'Step Attachment 2',
			'prefix' => 'Prefix 2',
			'extension' => '.doc',
			'date_added' => (new DateTime())->format('Y-m-d H:i:s')
		));

		$arr_of_attachment_dtos = $this->procedure_query_service->fetchStepAttachments(1);
		$first_attachment_dto = $arr_of_attachment_dtos[0];
		$first_attachment_arr = json_decode(json_encode($first_attachment_dto), true);

		$id = $first_attachment_arr['id'];
		$name = $first_attachment_arr['attributes']['name'];

		$this->assertEquals($id, 1);
		$this->assertEquals($name, 'Step Attachment 1');

		$second_attachment_dto = $arr_of_attachment_dtos[1];
		$second_attachment_arr = json_decode(json_encode($second_attachment_dto), true);

		$id_2 = $second_attachment_arr['id'];
		$name_2 = $second_attachment_arr['attributes']['name'];

		$this->assertEquals($id_2, 2);
		$this->assertEquals($name_2, 'Step Attachment 2');
	}

	public function test_If_getComment_Returns_Comment_From_Db(){

		self::$db->insert('step_comment', array(
			'id' => 1,
			'step_id' => 1,
			'commentator' => 1,
			'message' => 'this is the message...',
			'edited_on' => (new DateTime())->format('Y-m-d H:i:s'),
			'commented_on' => (new DateTime())->format('Y-m-d H:i:s')
		));

		$comment_dto = $this->procedure_query_service->getComment(1);

		$comment_arr = json_decode(json_encode($comment_dto), true);

		$id = $comment_arr['id'];
		$message = $comment_arr['attributes']['message'];

		$this->assertEquals($id, 1);
		$this->assertEquals($message, 'this is the message...');
	}

	public function test_If_getCommentator_Returns_The_Id_Of_Commentator(){

		$commentator_id = $this->procedure_query_service->getCommentator(1); /* comment id given */
		$this->assertEquals($commentator_id, 1);
	}

	public function test_If_getStep_Returns_Step_From_Db(){

		$step_dto = $this->procedure_query_service->getStep(2);
		$step_arr = json_decode(json_encode($step_dto), true);

		$id = $step_arr['id'];
		$title = $step_arr['attributes']['title'];

		$this->assertEquals($id, 2);
		$this->assertEquals($title, 'Step Title 2');

	}

	public function test_If_fetchProcedureSteps_Returns_Step_VM_Array_With_Procedure_Id(){

		self::$db->insert('procedure', array(
			'id' => 5,
			'container_id' => 1,
			'initiator_id' => null,
			'title' => 'Deneme',
			'type' => 1,
			'date_created' => (new DateTime())->format('Y-m-d H:i:s'),
			'department' => 1,
			'current_step' => null
		) );

		self::$db->insert('step', array(
			'id' => 3,
			'procedure_id' => 5,
			'title' => 'Step Title 3',
			'is_complete' => 1,
			'order' => 3,
			'out_of_scope' => 0,
			'activated_on' => null
		) );

		$steps_vm_arr = $this->procedure_query_service->fetchProcedureSteps(5); /* procedure id */
		$steps_vm = $steps_vm_arr[0];

		$steps_arr = json_decode(json_encode($steps_vm), true);

		$id = $steps_arr['id'];
		$title = $steps_arr['attributes']['title'];

		$this->assertEquals($id, 3);
		$this->assertEquals($title, 'Step Title 3');
	}

	public function test_If_fetchStepComments_Returns_Comment_VM_Array_With_Procedure_Id(){

		$comment_vm_arr = $this->procedure_query_service->fetchStepComments(1);
		$comment_vm = $comment_vm_arr[0];

		$steps_arr = json_decode(json_encode($comment_vm), true);

		$id = $steps_arr['id'];
		$message = $steps_arr['attributes']['message'];

		$this->assertEquals($id, 1);
		$this->assertEquals($message, 'this is the message...');
	}

	public function test_If_getUploader_Returns_Uploader_Id_From_Db(){

		$uploader_id = $this->procedure_query_service->getUploader(1); /* step_attachment id */
		$this->assertEquals($uploader_id, 2);
	}

	public function test_If_getApplication_Returns_Application_VM_From_Db(){

		self::$db->insert('application', array(
			'id' => 8,
			'procedure_id' => 1,
			'form_data' => null,
			'initiator_identifier' => 1122334400,
			'initiator_type' => 1,
			'applied_on' => (new DateTime())->format('Y-m-d H:i:s')
		));

		$attachment_vm = $this->procedure_query_service->getApplication(1);
		$attachment_arr = json_decode(json_encode($attachment_vm), true);

		$id = $attachment_arr['id'];
		$form_data = $attachment_arr['attributes']['form_data'];

		$this->assertEquals($id, 8);
	}

	public function test_If_containerExists_Returns_True_When_Container_Exists(){

		$this->assertTrue( $this->procedure_query_service->containerExists(1) );
		$this->assertTrue( $this->procedure_query_service->containerExists(2) );
		$this->assertFalse( $this->procedure_query_service->containerExists(3) ); /* no container w/ id: 3, this assertion is false */

	}

	public function test_If_getSubprocedure_Returns_Subprocedure_VM_From_Db(){

		self::$db->insert('subprocedure', array(
			'id' => 3,
			'parent_id' => 1,
			'title' => 'sub',
			'current_step' => null,
			'is_active' => 1
		) );

		$subprocedure_vm = $this->procedure_query_service->getSubprocedure(3);
		$subprocedure_arr = json_decode(json_encode($subprocedure_vm), true);

		$this->assertEquals($subprocedure_arr['id'], 3);
		$this->assertEquals($subprocedure_arr['attributes']['title'], 'sub');
	}

	public function test_If_getApplicationApplicant_Returns_Applicant_Array_From_Db(){
		
		$applicants_of_application = $this->procedure_query_service->getApplicationApplicant(8);

		$this->assertEquals($applicants_of_application['id'], 1122334400);
		$this->assertEquals($applicants_of_application['type'], 1);
	}
}

?>