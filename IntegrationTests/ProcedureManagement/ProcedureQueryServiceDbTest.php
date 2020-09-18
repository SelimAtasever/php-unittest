<?php

use \model\ProcedureManagement\application\ProcedureQueryService;

use \model\ProcedureManagement\infrastructure\FileDirectAccessLinkProvider;
use \model\ProcedureManagement\infrastructure\FileLocator;

use \model\common\QueryObject;
use PHPUnit\Framework\TestCase;

class ProcedureQueryServiceDbTest extends TestCase{

	private static \DB $db;
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

	public function test_If_getProcedure_Returns_Procedure_From_Db(){

		$file_locator = new FileLocator('./role_root_dir/');
		$file_direct_access_link_provider = new FileDirectAccessLinkProvider(
			self::$jwToken, 'https://kant.ist', $file_locator, '0.0.0.0 8.8.4.4'
		);

		$procedure_query_service = new ProcedureQueryService(self::$db, $file_direct_access_link_provider);

		self::$db->insert('procedure', array(
			'id' => 1,
			'container_id' => 1,
			'initiator_id' => null,
			'title' => 'Procedure Title 1',
			'type' => 2,
			'is_repeatable' => 0,
			'date_created' => (new DateTime())->format('Y-m-d H:i:s')
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

		$procedure_dto = $procedure_query_service->getProcedure(1);

		$procedure_as_arr = json_decode(json_encode($procedure_dto), true); // without bool its not an array but stdClass.

		$id = $procedure_as_arr['id'];
		$title = $procedure_as_arr['attributes']['title'];

		$this->assertEquals($id, 1);
		$this->assertEquals($title, 'Procedure Title 1');
	}

	public function test_If_fetchProceduresInProgress_Returns_Procedures_In_Progress(){

		$file_locator = new FileLocator('./role_root_dir/');
		$file_direct_access_link_provider = new FileDirectAccessLinkProvider(
			self::$jwToken, 'https://kant.ist', $file_locator, '0.0.0.0 8.8.4.4'
		);

		$procedure_query_service = new ProcedureQueryService(self::$db, $file_direct_access_link_provider);

		self::$db->insert('procedure', array(
			'id' => 2,
			'container_id' => 1,
			'initiator_id' => null,
			'title' => 'Procedure Title 2',
			'type' => 2,
			'is_repeatable' => 0,
			'date_created' => (new DateTime())->format('Y-m-d H:i:s')
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

		$procedures_dto = $procedure_query_service->fetchProceduresInProgress(new QueryObject());
		$procedures_arr = $procedures_dto->procedures();

		$this->assertIsArray($procedures_arr);
		$this->assertCount(2, $procedures_arr);
	}

	public function test_If_getContainer_Returns_Container_With_Given_Id_From_Db(){

		$file_locator = new FileLocator('./role_root_dir/');
		$file_direct_access_link_provider = new FileDirectAccessLinkProvider(
			self::$jwToken, 'https://kant.ist', $file_locator, '0.0.0.0 8.8.4.4'
		);

		$procedure_query_service = new ProcedureQueryService(self::$db, $file_direct_access_link_provider);

		$container_dto = $procedure_query_service->getContainer(1);
		$container_arr = json_decode(json_encode($container_dto), true);

		$id = $container_arr['id'];
		$type = $container_arr['attributes']['type'];

		$this->assertEquals($id, 1);
		$this->assertEquals($type,1);
	}

	public function test_If_fetchContainers_Returns_Containers_From_Db(){

		$file_locator = new FileLocator('./role_root_dir/');
		$file_direct_access_link_provider = new FileDirectAccessLinkProvider(
			self::$jwToken, 'https://kant.ist', $file_locator, '0.0.0.0 8.8.4.4'
		);

		$procedure_query_service = new ProcedureQueryService(self::$db, $file_direct_access_link_provider);

		$containers_dto = $procedure_query_service->fetchContainers(new QueryObject());
		$containers_arr = $containers_dto->containers();

		$this->assertIsArray($containers_arr);
		$this->assertCount(2, $containers_arr);
	}

	public function test_If_fetchContainerProcedures_Returns_Containers_Procedures(){

		$file_locator = new FileLocator('./role_root_dir/');
		$file_direct_access_link_provider = new FileDirectAccessLinkProvider(
			self::$jwToken, 'https://kant.ist', $file_locator, '0.0.0.0 8.8.4.4'
		);

		$procedure_query_service = new ProcedureQueryService(self::$db, $file_direct_access_link_provider);

		$arr_container_procedures = $procedure_query_service->fetchContainerProcedures(1);

		$this->assertCount(2, $arr_container_procedures);
	}


	public function test_If_getAttachment_Returns_Attachment_From_Db(){

		$file_locator = new FileLocator('./role_root_dir/');
		$file_direct_access_link_provider = new FileDirectAccessLinkProvider(
			self::$jwToken, 'https://kant.ist', $file_locator, '0.0.0.0 8.8.4.4'
		);

		$procedure_query_service = new ProcedureQueryService(self::$db, $file_direct_access_link_provider);

		self::$db->insert('step_attachment', array(
			'id' => 1,
			'step_id' => 1,
			'uploader' => 2,
			'name' => 'Step Attachment 1',
			'prefix' => 'Prefix 1',
			'extension' => '.doc',
			'date_added' => (new DateTime())->format('Y-m-d H:i:s')
		));

		$attachment_dto = $procedure_query_service->getAttachment(1);

		$attachment_arr = json_decode(json_encode($attachment_dto), true);

		$id = $attachment_arr['id'];
		$extension = $attachment_arr['attributes']['extension'];	
		$name = $attachment_arr['attributes']['name'];

		$this->assertEquals($id, 1);
		$this->assertEquals($extension, '.doc');
		$this->assertEquals($name, 'Step Attachment 1');	

	}

	public function test_If_fetchStepAttachments_Returns_Attachments_With_StepId(){

		$file_locator = new FileLocator('./role_root_dir/');
		$file_direct_access_link_provider = new FileDirectAccessLinkProvider(
			self::$jwToken, 'https://kant.ist', $file_locator, '0.0.0.0 8.8.4.4'
		);

		$procedure_query_service = new ProcedureQueryService(self::$db, $file_direct_access_link_provider);

		self::$db->insert('step_attachment', array(
			'id' => 2,
			'step_id' => 1,
			'uploader' => 1,
			'name' => 'Step Attachment 2',
			'prefix' => 'Prefix 2',
			'extension' => '.doc',
			'date_added' => (new DateTime())->format('Y-m-d H:i:s')
		));

		$arr_of_attachment_dtos = $procedure_query_service->fetchStepAttachments(1);
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

		$file_locator = new FileLocator('./role_root_dir/');
		$file_direct_access_link_provider = new FileDirectAccessLinkProvider(
			self::$jwToken, 'https://kant.ist', $file_locator, '0.0.0.0 8.8.4.4'
		);

		$procedure_query_service = new ProcedureQueryService(self::$db, $file_direct_access_link_provider);

		self::$db->insert('step_comment', array(
			'id' => 1,
			'step_id' => 1,
			'commentator' => 1,
			'message' => 'this is the message...',
			'edited_on' => (new DateTime())->format('Y-m-d H:i:s'),
			'commented_on' => (new DateTime())->format('Y-m-d H:i:s')
		));

		$comment_dto = $procedure_query_service->getComment(1);

		$comment_arr = json_decode(json_encode($comment_dto), true);

		$id = $comment_arr['id'];
		$message = $comment_arr['attributes']['message'];

		$this->assertEquals($id, 1);
		$this->assertEquals($message, 'this is the message...');
	}

	public function test_If_getCommentator_Returns_The_Id_Of_Commentator(){

		$file_locator = new FileLocator('./role_root_dir/');
		$file_direct_access_link_provider = new FileDirectAccessLinkProvider(
			self::$jwToken, 'https://kant.ist', $file_locator, '0.0.0.0 8.8.4.4'
		);

		$procedure_query_service = new ProcedureQueryService(self::$db, $file_direct_access_link_provider);

		$commentator_id = $procedure_query_service->getCommentator(1); /* comment id given */
		$this->assertEquals($commentator_id, 1);
	}

	public function test_If_getStep_Returns_Step_From_Db(){

		$file_locator = new FileLocator('./role_root_dir/');
		$file_direct_access_link_provider = new FileDirectAccessLinkProvider(
			self::$jwToken, 'https://kant.ist', $file_locator, '0.0.0.0 8.8.4.4'
		);

		$procedure_query_service = new ProcedureQueryService(self::$db, $file_direct_access_link_provider);

		$step_dto = $procedure_query_service->getStep(2);
		$step_arr = json_decode(json_encode($step_dto), true);

		$id = $step_arr['id'];
		$title = $step_arr['attributes']['title'];

		$this->assertEquals($id, 2);
		$this->assertEquals($title, 'Step Title 2');

	}

	public function test_If_fetchProcedureSteps_Returns_Step_VM_Array_With_Procedure_Id(){

		$file_locator = new FileLocator('./role_root_dir/');
		$file_direct_access_link_provider = new FileDirectAccessLinkProvider(
			self::$jwToken, 'https://kant.ist', $file_locator, '0.0.0.0 8.8.4.4'
		);
		$procedure_query_service = new ProcedureQueryService(self::$db, $file_direct_access_link_provider);

		$steps_vm_arr = $procedure_query_service->fetchProcedureSteps(1); /* procedure id */
		$steps_vm = $steps_vm_arr[0];

		$steps_arr = json_decode(json_encode($steps_vm), true);

		$id = $steps_arr['id'];
		$title = $steps_arr['attributes']['title'];

		$this->assertEquals($id, 1);
		$this->assertEquals($title, 'Step Title 1');
	}

	public function test_If_fetchStepComments_Returns_Comment_VM_Array_With_Procedure_Id(){

		$file_locator = new FileLocator('./role_root_dir/');
		$file_direct_access_link_provider = new FileDirectAccessLinkProvider(
			self::$jwToken, 'https://kant.ist', $file_locator, '0.0.0.0 8.8.4.4'
		);
		$procedure_query_service = new ProcedureQueryService(self::$db, $file_direct_access_link_provider);

		$comment_vm_arr = $procedure_query_service->fetchStepComments(1);
		$comment_vm = $comment_vm_arr[0];

		$steps_arr = json_decode(json_encode($comment_vm), true);

		$id = $steps_arr['id'];
		$message = $steps_arr['attributes']['message'];

		$this->assertEquals($id, 1);
		$this->assertEquals($message, 'this is the message...');
	}

	public function test_If_getUploader_Returns_Uploader_Id_From_Db(){

		$file_locator = new FileLocator('./role_root_dir/');
		$file_direct_access_link_provider = new FileDirectAccessLinkProvider(
			self::$jwToken, 'https://kant.ist', $file_locator, '0.0.0.0 8.8.4.4'
		);
		$procedure_query_service = new ProcedureQueryService(self::$db, $file_direct_access_link_provider);

		$uploader_id = $procedure_query_service->getUploader(1); /* step_attachment id */
		$this->assertEquals($uploader_id, 2);
	}

	public function test_If_getApplication_Returns_Application_VM_From_Db(){

		$file_locator = new FileLocator('./role_root_dir/');
		$file_direct_access_link_provider = new FileDirectAccessLinkProvider(
			self::$jwToken, 'https://kant.ist', $file_locator, '0.0.0.0 8.8.4.4'
		);
		$procedure_query_service = new ProcedureQueryService(self::$db, $file_direct_access_link_provider);

		self::$db->insert('application', array(
			'id' => 1,
			'procedure_id' => 1,
			'form_data' => 'form data 2',
			'initiator_identifier' => 1122334400,
			'initiator_type' => 1,
			'applied_on' => (new DateTime())->format('Y-m-d H:i:s')
		));

		$attachment_vm = $procedure_query_service->getApplication(1);

		$attachment_arr = json_decode(json_encode($attachment_vm), true);

		$id = $attachment_arr['id'];
		$form_data = $attachment_arr['attributes']['form_data'];

		$this->assertEquals($id, 1);
		$this->assertEquals($form_data, 'form data 2');
	}
}

?>