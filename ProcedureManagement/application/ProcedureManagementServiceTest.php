AttachmentNotFoundException<?php

use \model\ProcedureManagement\application\ProcedureManagementService;
use \model\ProcedureManagement\domain\model\IContainerRepository;
use \model\ProcedureManagement\domain\model\Container;
use \model\ProcedureManagement\domain\model\ContainerId;
use \model\ProcedureManagement\domain\model\ContainerType;
use \model\ProcedureManagement\domain\model\IProcedureRepository;
use \model\ProcedureManagement\domain\model\ICommentRepository;
use \model\ProcedureManagement\domain\model\IAttachmentRepository;
use \model\ProcedureManagement\application\IIdentityProvider;
use \model\ProcedureManagement\domain\model\Comment;
use \model\ProcedureManagement\domain\model\Procedure;
use \model\ProcedureManagement\domain\model\ProcedureId;
use \model\ProcedureManagement\domain\model\InitiatorId;
use \model\ProcedureManagement\domain\model\ProcedureType;
use \model\ProcedureManagement\domain\model\CommentId;
use \model\ProcedureManagement\domain\model\Step;
use \model\ProcedureManagement\domain\model\StepId;
use \model\ProcedureManagement\domain\model\PersonnelId;
use \model\ProcedureManagement\domain\model\Attachment;
use \model\ProcedureManagement\domain\model\AttachmentId;
use \model\ProcedureManagement\application\exception\ProcedureNotFoundException;
use \model\ProcedureManagement\application\exception\ContainerNotFoundException;
use \model\ProcedureManagement\domain\model\exception\ProcedureCannotBeCancelledException;
use \model\ProcedureManagement\domain\model\exception\StepNotFoundException;
use \model\ProcedureManagement\application\exception\CommentNotFoundException;
use \model\ProcedureManagement\domain\model\exception\CommentInsufficientPrivilegeException;
use \model\ProcedureManagement\domain\model\exception\AttachmentInsufficientPrivilegeException;
use \model\ProcedureManagement\application\exception\AttachmentNotFoundException;
use \model\common\ExceptionCollection;

use PHPUnit\Framework\TestCase;


class ProcedureManagementServiceTest extends TestCase{


	public function test_If_startProcedure_Method_Create_A_Procedure_And_Return_Its_Id(){

		$this->expectException(ContainerNotFoundException::class);
		$container = null;

		$steps_arr = array();
		$procedures = array(
 			new Procedure(
			new ProcedureId(1), 
			new InitiatorId(1234567890), 
			'this is the procedure title', 
			$steps_arr, 
			ProcedureType::ConstructionPermit(),
			true)
		);

		$comment = new Comment(
			new CommentId(1),
			new StepId(1), 
			new PersonnelId(1), 
			'this is the comment', 
			new DateTime(), 
			new DateTime()
		);

		$attachment = new Attachment(
			new AttachmentId(1),
			new StepId(1),
			new PersonnelId(1),
			'Attachment name...',
			'base64',
			new DateTime()
		);

		$container_repository = $this->createMock(IContainerRepository::class);
		$container_repository->method('find')->willReturn($container);

		$procedure_repository = $this->createMock(IProcedureRepository::class);
		$procedure_repository->method('proceduresOfContainer')->willReturn($procedures);
		$procedure_repository->method('nextProcedureId')->willReturn(new ProcedureId(1));

		$comment_repository = $this->createMock(ICommentRepository::class);
		$comment_repository->method('find')->willReturn($comment);

		$attachment_repository = $this->createMock(IAttachmentRepository::class);
		$attachment_repository->method('find')->willReturn($attachment);

		$identity_provider = $this->createMock(IIdentityProvider::class);
		$identity_provider->method('identity')->willReturn(1);

		$procedure_management_service = new ProcedureManagementService(
			$container_repository, $procedure_repository, $comment_repository, $attachment_repository, $identity_provider
		);
		
		$returned_procedure_id = $procedure_management_service->startProcedure(1,2);
	}


	public function test_If_Test_Throws_Exception_When_Container_Isnt_Found(){

		$container = new Container(
			new ContainerId(2), 
			ContainerType::Structure()
		);

		$steps_arr = array();
		$procedures = array(
 			new Procedure(
			new ProcedureId(1), 
			new InitiatorId(1234567890), 
			'this is the procedure title', 
			$steps_arr, 
			ProcedureType::ConstructionPermit(),
			true)
		);

		$comment = new Comment(
			new CommentId(1),
			new StepId(1), 
			new PersonnelId(1), 
			'this is the comment', 
			new DateTime(), 
			new DateTime()
		);

		$attachment = new Attachment(
			new AttachmentId(1),
			new StepId(1),
			new PersonnelId(1),
			'Attachment name...',
			'base64',
			new DateTime()
		);

		$container_repository = $this->createMock(IContainerRepository::class);
		$container_repository->method('find')->willReturn($container);

		$procedure_repository = $this->createMock(IProcedureRepository::class);
		$procedure_repository->method('proceduresOfContainer')->willReturn($procedures);
		$procedure_repository->method('nextProcedureId')->willReturn(new ProcedureId(1));

		$comment_repository = $this->createMock(ICommentRepository::class);
		$comment_repository->method('find')->willReturn($comment);

		$attachment_repository = $this->createMock(IAttachmentRepository::class);
		$attachment_repository->method('find')->willReturn($attachment);

		$identity_provider = $this->createMock(IIdentityProvider::class);
		$identity_provider->method('identity')->willReturn(1);

		$procedure_management_service = new ProcedureManagementService(
			$container_repository, $procedure_repository, $comment_repository, $attachment_repository, $identity_provider
		);
		
		$returned_procedure_id = $procedure_management_service->startProcedure(1,2);
		$this->assertEquals($returned_procedure_id, 1);

	}


	public function test_If_Test_Throws_Exception_When_Procedure_Isnt_Found(){

		$this->expectException(ProcedureNotFoundException::class);

		$container = new Container(
			new ContainerId(2), 
			ContainerType::Structure()
		);

		$steps_arr = [
			new Step(new StepId(1),'this is the first title', true,1), 
			new Step(new StepId(2), 'this is the second title', true,2),
			new Step(new StepId(3), 'this is the third title', false,3)
		];
		
		$procedure = null;

		$comment = new Comment(
			new CommentId(1),
			new StepId(1), 
			new PersonnelId(1), 
			'this is the comment', 
			new DateTime(), 
			new DateTime()
		);

		$attachment = new Attachment(
			new AttachmentId(1),
			new StepId(1),
			new PersonnelId(1),
			'Attachment name...',
			'base64',
			new DateTime()
		);

		$container_repository = $this->createMock(IContainerRepository::class);
		$container_repository->method('find')->willReturn($container);

		$procedure_repository = $this->createMock(IProcedureRepository::class);
		$procedure_repository->method('find')->willReturn($procedure);
		$procedure_repository->method('nextProcedureId')->willReturn(new ProcedureId(1));

		$comment_repository = $this->createMock(ICommentRepository::class);
		$comment_repository->method('find')->willReturn($comment);

		$attachment_repository = $this->createMock(IAttachmentRepository::class);
		$attachment_repository->method('find')->willReturn($attachment);

		$identity_provider = $this->createMock(IIdentityProvider::class);
		$identity_provider->method('identity')->willReturn(1);

		$procedure_management_service = new ProcedureManagementService(
			$container_repository, $procedure_repository, $comment_repository, $attachment_repository, $identity_provider
		);
		
		$procedure_management_service->advanceProcedure(2,1);
	}

	public function test_If_cancelProcedure_Removes_Pointed_Procedure(){

		$container = new Container(
			new ContainerId(2), 
			ContainerType::Structure()
		);

		$steps_arr = [
			new Step(new StepId(1),'this is the first title', true,1), 
			new Step(new StepId(2), 'this is the second title', true,2),
			new Step(new StepId(3), 'this is the third title', false,3)
		];
		
		$procedure = new Procedure(
			new ProcedureId(1), 
			new InitiatorId(1234567890), 
			'this is the procedure title', 
			$steps_arr, 
			ProcedureType::ConstructionPermit(),
			true);

		$comment = new Comment(
			new CommentId(1),
			new StepId(1), 
			new PersonnelId(1), 
			'this is the comment', 
			new DateTime(), 
			new DateTime()
		);

		$attachment = new Attachment(
			new AttachmentId(1),
			new StepId(1),
			new PersonnelId(1),
			'Attachment name...',
			'base64',
			new DateTime()
		);

		$container_repository = $this->createMock(IContainerRepository::class);
		$container_repository->method('find')->willReturn($container);

		$procedure_repository = $this->createMock(IProcedureRepository::class);
		$procedure_repository->method('find')->willReturn($procedure);
		$procedure_repository->method('nextProcedureId')->willReturn(new ProcedureId(1));

		$comment_repository = $this->createMock(ICommentRepository::class);
		$comment_repository->method('find')->willReturn($comment);

		$attachment_repository = $this->createMock(IAttachmentRepository::class);
		$attachment_repository->method('find')->willReturn($attachment);

		$identity_provider = $this->createMock(IIdentityProvider::class);
		$identity_provider->method('identity')->willReturn(1);

		$procedure_management_service = new ProcedureManagementService(
			$container_repository, $procedure_repository, $comment_repository, $attachment_repository, $identity_provider
		);

		$procedure_management_service->cancelProcedure(1);
		
		$confirm_false = $procedure->isComplete();
		$this->assertFalse($confirm_false);
	}

	public function test_If_cancelProcedure_Throws_An_Exception_When_Steps_Are_All_Completed(){

		$this->expectException(ProcedureCannotBeCancelledException::class);

		$container = new Container(
			new ContainerId(2), 
			ContainerType::Structure()
		);

		$steps_arr = [
			new Step(new StepId(1),'this is the first title', true,1), 
			new Step(new StepId(2), 'this is the second title', true,2),
			new Step(new StepId(3), 'this is the third title', true,3)
		]; 
		//all steps are completed, this procedure is completed,cannot be cancelled.
		
		$procedure = new Procedure(
			new ProcedureId(1), 
			new InitiatorId(1234567890), 
			'this is the procedure title', 
			$steps_arr, 
			ProcedureType::ConstructionPermit(),
			true);

		$comment = new Comment(
			new CommentId(1),
			new StepId(1), 
			new PersonnelId(1), 
			'this is the comment', 
			new DateTime(), 
			new DateTime()
		);

		$attachment = new Attachment(
			new AttachmentId(1),
			new StepId(1),
			new PersonnelId(1),
			'Attachment name...',
			'base64',
			new DateTime()
		);

		$container_repository = $this->createMock(IContainerRepository::class);
		$container_repository->method('find')->willReturn($container);

		$procedure_repository = $this->createMock(IProcedureRepository::class);
		$procedure_repository->method('find')->willReturn($procedure);
		$procedure_repository->method('nextProcedureId')->willReturn(new ProcedureId(1));

		$comment_repository = $this->createMock(ICommentRepository::class);
		$comment_repository->method('find')->willReturn($comment);

		$attachment_repository = $this->createMock(IAttachmentRepository::class);
		$attachment_repository->method('find')->willReturn($attachment);

		$identity_provider = $this->createMock(IIdentityProvider::class);
		$identity_provider->method('identity')->willReturn(1);

		$procedure_management_service = new ProcedureManagementService(
			$container_repository, $procedure_repository, $comment_repository, $attachment_repository, $identity_provider
		);

		$procedure_management_service->cancelProcedure(1);
	}

	public function test_If_comment_Function_Creates_A_New_Comment_And_Return_Its_Id(){

		$container = new Container(
			new ContainerId(2), 
			ContainerType::Structure()
		);

		$steps_arr = [
			new Step(new StepId(1),'this is the first title', true,1), 
			new Step(new StepId(2), 'this is the second title', true,2),
			new Step(new StepId(3), 'this is the third title', false,3)
		];
		
		$procedure = new Procedure(
			new ProcedureId(1), 
			new InitiatorId(1234567890), 
			'this is the procedure title', 
			$steps_arr, 
			ProcedureType::ConstructionPermit(),
			true);

		$comment = new Comment(
			new CommentId(1),
			new StepId(1), 
			new PersonnelId(1), 
			'this is the comment', 
			new DateTime(), 
			new DateTime()
		);

		$attachment = new Attachment(
			new AttachmentId(1),
			new StepId(1),
			new PersonnelId(1),
			'Attachment name...',
			'base64',
			new DateTime()
		);

		$comment = new Comment(
			new CommentId(1),
			new StepId(1),
			new PersonnelId(1),
			'this is the comment',
			new DateTime(),
			new DateTime
		);

		$container_repository = $this->createMock(IContainerRepository::class);
		$container_repository->method('find')->willReturn($container);

		$procedure_repository = $this->createMock(IProcedureRepository::class);
		$procedure_repository->method('find')->willReturn($procedure);
		$procedure_repository->method('nextProcedureId')->willReturn(new ProcedureId(1));

		$comment_repository = $this->createMock(ICommentRepository::class);
		$comment_repository->method('stepExists')->willReturn(true);
		$comment_repository->method('nextId')->willReturn(new CommentId(1));

		$attachment_repository = $this->createMock(IAttachmentRepository::class);
		$attachment_repository->method('find')->willReturn($attachment);

		$identity_provider = $this->createMock(IIdentityProvider::class);
		$identity_provider->method('identity')->willReturn(1);

		$procedure_management_service = new ProcedureManagementService(
			$container_repository, $procedure_repository, $comment_repository, $attachment_repository, $identity_provider
		);

		$returned_comment_id = $procedure_management_service->comment(1,1,1);
		$this->assertEquals($returned_comment_id, 1);
	}	

	public function test_If_comment_Function_Throws_Exception_When_Step_Isnt_Found(){

		$this->expectException(StepNotFoundException::class);

		$container = new Container(
			new ContainerId(2), 
			ContainerType::Structure()
		);

		$steps_arr = [
			new Step(new StepId(1),'this is the first title', true,1), 
			new Step(new StepId(2), 'this is the second title', true,2),
			new Step(new StepId(3), 'this is the third title', false,3)
		];
		
		$procedure = new Procedure(
			new ProcedureId(1), 
			new InitiatorId(1234567890), 
			'this is the procedure title', 
			$steps_arr, 
			ProcedureType::ConstructionPermit(),
			true);

		$comment = new Comment(
			new CommentId(1),
			new StepId(1), 
			new PersonnelId(1), 
			'this is the comment', 
			new DateTime(), 
			new DateTime()
		);

		$attachment = new Attachment(
			new AttachmentId(1),
			new StepId(1),
			new PersonnelId(1),
			'Attachment name...',
			'base64',
			new DateTime()
		);

		$comment = new Comment(
			new CommentId(1),
			new StepId(1),
			new PersonnelId(1),
			'this is the comment',
			new DateTime(),
			new DateTime
		);

		$container_repository = $this->createMock(IContainerRepository::class);
		$container_repository->method('find')->willReturn($container);

		$procedure_repository = $this->createMock(IProcedureRepository::class);
		$procedure_repository->method('find')->willReturn($procedure);
		$procedure_repository->method('nextProcedureId')->willReturn(new ProcedureId(1));

		$comment_repository = $this->createMock(ICommentRepository::class);
		$comment_repository->method('stepExists')->willReturn(false);
		$comment_repository->method('nextId')->willReturn(new CommentId(1));

		$attachment_repository = $this->createMock(IAttachmentRepository::class);
		$attachment_repository->method('find')->willReturn($attachment);

		$identity_provider = $this->createMock(IIdentityProvider::class);
		$identity_provider->method('identity')->willReturn(1);

		$procedure_management_service = new ProcedureManagementService(
			$container_repository, $procedure_repository, $comment_repository, $attachment_repository, $identity_provider
		);

		$returned_comment_id = $procedure_management_service->comment(1,1,1);
		$this->assertEquals($returned_comment_id, 1);
	}	

	public function test_If_editComment_Throws_An_Exception_When_Step_Isnt_Found(){

		$this->expectException(StepNotFoundException::class);

		$container = new Container(
			new ContainerId(2), 
			ContainerType::Structure()
		);

		$steps_arr = [
			new Step(new StepId(1),'this is the first title', true,1), 
			new Step(new StepId(2), 'this is the second title', true,2),
			new Step(new StepId(3), 'this is the third title', false,3)
		];
		
		$procedure = new Procedure(
			new ProcedureId(1), 
			new InitiatorId(1234567890), 
			'this is the procedure title', 
			$steps_arr, 
			ProcedureType::ConstructionPermit(),
			true);

		$comment = new Comment(
			new CommentId(1),
			new StepId(1), 
			new PersonnelId(1), 
			'this is the comment', 
			new DateTime(), 
			new DateTime()
		);

		$attachment = new Attachment(
			new AttachmentId(1),
			new StepId(1),
			new PersonnelId(1),
			'Attachment name...',
			'base64',
			new DateTime()
		);

		$container_repository = $this->createMock(IContainerRepository::class);
		$container_repository->method('find')->willReturn($container);

		$procedure_repository = $this->createMock(IProcedureRepository::class);
		$procedure_repository->method('find')->willReturn($procedure);
		$procedure_repository->method('nextProcedureId')->willReturn(new ProcedureId(1));

		$comment_repository = $this->createMock(ICommentRepository::class);
		$comment_repository->method('find')->willReturn($comment);
		$comment_repository->method('stepExists')->willReturn(false); // this will trigger the exception.

		$attachment_repository = $this->createMock(IAttachmentRepository::class);
		$attachment_repository->method('find')->willReturn($attachment);

		$identity_provider = $this->createMock(IIdentityProvider::class);
		$identity_provider->method('identity')->willReturn(1);

		$procedure_management_service = new ProcedureManagementService(
			$container_repository, $procedure_repository, $comment_repository, $attachment_repository, $identity_provider
		);

		$procedure_management_service->editComment(1,1, 'this is the comment');

	}

	public function test_If_Test_Throws_An_Exception_When_Comment_Isnt_Found(){

		$this->expectException(CommentNotFoundException::class);

		$container = new Container(
			new ContainerId(2), 
			ContainerType::Structure()
		);

		$steps_arr = [
			new Step(new StepId(1),'this is the first title', true,1), 
			new Step(new StepId(2), 'this is the second title', true,2),
			new Step(new StepId(3), 'this is the third title', false,3)
		];
		
		$procedure = new Procedure(
			new ProcedureId(1), 
			new InitiatorId(1234567890), 
			'this is the procedure title', 
			$steps_arr, 
			ProcedureType::ConstructionPermit(),
			true);

		$comment = null; // this will trigger the exception.

		$attachment = new Attachment(
			new AttachmentId(1),
			new StepId(1),
			new PersonnelId(1),
			'Attachment name...',
			'base64',
			new DateTime()
		);

		$container_repository = $this->createMock(IContainerRepository::class);
		$container_repository->method('find')->willReturn($container);

		$procedure_repository = $this->createMock(IProcedureRepository::class);
		$procedure_repository->method('find')->willReturn($procedure);
		$procedure_repository->method('nextProcedureId')->willReturn(new ProcedureId(1));

		$comment_repository = $this->createMock(ICommentRepository::class);
		$comment_repository->method('find')->willReturn($comment);
		$comment_repository->method('stepExists')->willReturn(true); 

		$attachment_repository = $this->createMock(IAttachmentRepository::class);
		$attachment_repository->method('find')->willReturn($attachment);

		$identity_provider = $this->createMock(IIdentityProvider::class);
		$identity_provider->method('identity')->willReturn(1);

		$procedure_management_service = new ProcedureManagementService(
			$container_repository, $procedure_repository, $comment_repository, $attachment_repository, $identity_provider
		);

		$procedure_management_service->editComment(1,1, 'this is the comment');

	}

	public function test_If_editComment_Throws_Exception_When_Personnel_Has_No_Privilege(){

		$this->expectException(CommentInsufficientPrivilegeException::class);

		$container = new Container(
			new ContainerId(2), 
			ContainerType::Structure()
		);

		$steps_arr = [
			new Step(new StepId(1),'this is the first title', true,1), 
			new Step(new StepId(2), 'this is the second title', true,2),
			new Step(new StepId(3), 'this is the third title', false,3)
		];
		
		$procedure = new Procedure(
			new ProcedureId(1), 
			new InitiatorId(1234567890), 
			'this is the procedure title', 
			$steps_arr, 
			ProcedureType::ConstructionPermit(),
			true);

		$comment = new Comment(
			new CommentId(1),
			new StepId(1), 
			new PersonnelId(2), 	// this will trigger the exception, doesnt match the IIdentity provider return val.
			'this is the comment', 
			new DateTime(), 
			new DateTime()
		);

		$attachment = new Attachment(
			new AttachmentId(1),
			new StepId(1),
			new PersonnelId(1),
			'Attachment name...',
			'base64',
			new DateTime()
		);

		$container_repository = $this->createMock(IContainerRepository::class);
		$container_repository->method('find')->willReturn($container);

		$procedure_repository = $this->createMock(IProcedureRepository::class);
		$procedure_repository->method('find')->willReturn($procedure);
		$procedure_repository->method('nextProcedureId')->willReturn(new ProcedureId(1));

		$comment_repository = $this->createMock(ICommentRepository::class);
		$comment_repository->method('find')->willReturn($comment);
		$comment_repository->method('stepExists')->willReturn(true); 

		$attachment_repository = $this->createMock(IAttachmentRepository::class);
		$attachment_repository->method('find')->willReturn($attachment);

		$identity_provider = $this->createMock(IIdentityProvider::class);
		$identity_provider->method('identity')->willReturn(1);

		$procedure_management_service = new ProcedureManagementService(
			$container_repository, $procedure_repository, $comment_repository, $attachment_repository, $identity_provider
		);

		$procedure_management_service->editComment(1,1, 'this is the comment');

	}

	public function test_If_removeComment_Throws_Exception_When_Personnel_Has_No_Privilege(){

		$this->expectException(CommentInsufficientPrivilegeException::class);

		$container = new Container(
			new ContainerId(2), 
			ContainerType::Structure()
		);

		$steps_arr = [
			new Step(new StepId(1),'this is the first title', true,1), 
			new Step(new StepId(2), 'this is the second title', true,2),
			new Step(new StepId(3), 'this is the third title', false,3)
		];
		
		$procedure = new Procedure(
			new ProcedureId(1), 
			new InitiatorId(1234567890), 
			'this is the procedure title', 
			$steps_arr, 
			ProcedureType::ConstructionPermit(),
			true);

		$comment = new Comment(
			new CommentId(1),
			new StepId(1), 
			new PersonnelId(2), 	// this will trigger the exception, doesnt match the IIdentity provider return val.
			'this is the comment', 
			new DateTime(), 
			new DateTime()
		);

		$attachment = new Attachment(
			new AttachmentId(1),
			new StepId(1),
			new PersonnelId(1),
			'Attachment name...',
			'base64',
			new DateTime()
		);

		$container_repository = $this->createMock(IContainerRepository::class);
		$container_repository->method('find')->willReturn($container);

		$procedure_repository = $this->createMock(IProcedureRepository::class);
		$procedure_repository->method('find')->willReturn($procedure);
		$procedure_repository->method('nextProcedureId')->willReturn(new ProcedureId(1));

		$comment_repository = $this->createMock(ICommentRepository::class);
		$comment_repository->method('find')->willReturn($comment);
		$comment_repository->method('stepExists')->willReturn(true); 

		$attachment_repository = $this->createMock(IAttachmentRepository::class);
		$attachment_repository->method('find')->willReturn($attachment);

		$identity_provider = $this->createMock(IIdentityProvider::class);
		$identity_provider->method('identity')->willReturn(1);

		$procedure_management_service = new ProcedureManagementService(
			$container_repository, $procedure_repository, $comment_repository, $attachment_repository, $identity_provider
		);

		$procedure_management_service->removeComment(1);
	}


	public function test_If_addAttachment_Creates_A_New_Attachment_And_Return_Its_Id(){

		$container = new Container(
			new ContainerId(2), 
			ContainerType::Structure()
		);

		$steps_arr = [
			new Step(new StepId(1),'this is the first title', true,1), 
			new Step(new StepId(2), 'this is the second title', true,2),
			new Step(new StepId(3), 'this is the third title', false,3)
		];
		
		$procedure = new Procedure(
			new ProcedureId(1), 
			new InitiatorId(1234567890), 
			'this is the procedure title', 
			$steps_arr, 
			ProcedureType::ConstructionPermit(),
			true);

		$comment = new Comment(
			new CommentId(1),
			new StepId(1), 
			new PersonnelId(1), 
			'this is the comment', 
			new DateTime(), 
			new DateTime()
		);

		$attachment = new Attachment(
			new AttachmentId(1),
			new StepId(1),
			new PersonnelId(1),
			'Attachment name...',
			'base64',
			new DateTime()
		);

		$comment = new Comment(
			new CommentId(1),
			new StepId(1),
			new PersonnelId(1),
			'this is the comment',
			new DateTime(),
			new DateTime
		);

		$container_repository = $this->createMock(IContainerRepository::class);
		$container_repository->method('find')->willReturn($container);

		$procedure_repository = $this->createMock(IProcedureRepository::class);
		$procedure_repository->method('find')->willReturn($procedure);
		$procedure_repository->method('nextProcedureId')->willReturn(new ProcedureId(1));

		$comment_repository = $this->createMock(ICommentRepository::class);
		$comment_repository->method('stepExists')->willReturn(true);
		$comment_repository->method('nextId')->willReturn(new CommentId(1));

		$attachment_repository = $this->createMock(IAttachmentRepository::class);
		$attachment_repository->method('find')->willReturn($attachment);
		$attachment_repository->method('nextId')->willReturn(new AttachmentId(1));

		$identity_provider = $this->createMock(IIdentityProvider::class);
		$identity_provider->method('identity')->willReturn(1);

		$procedure_management_service = new ProcedureManagementService(
			$container_repository, $procedure_repository, $comment_repository, $attachment_repository, $identity_provider
		);	

		$returned_attachment_id = $procedure_management_service->addAttachment(1,1,'base64', 'attachment_name');
		$this->assertEquals($returned_attachment_id, 1);

	} 


	public function test_If_addAttachment_Throws_An_Exception_When_Step_Isnt_Found(){  

		$this->expectException(StepNotFoundException::class);

		$container = new Container(
			new ContainerId(2), 
			ContainerType::Structure()
		);

		$steps_arr = [
			new Step(new StepId(1),'this is the first title', true,1), 
			new Step(new StepId(2), 'this is the second title', true,2),
			new Step(new StepId(3), 'this is the third title', false,3)
		];
		
		$procedure = new Procedure(
			new ProcedureId(1), 
			new InitiatorId(1234567890), 
			'this is the procedure title', 
			$steps_arr, 
			ProcedureType::ConstructionPermit(),
			true);

		$comment = new Comment(
			new CommentId(1),
			new StepId(1), 
			new PersonnelId(1),
			'this is the comment', 
			new DateTime(), 
			new DateTime()
		);

		$attachment = new Attachment(
			new AttachmentId(1),
			new StepId(1),
			new PersonnelId(1),
			'Attachment name...',
			'base64',
			new DateTime()
		);

		$container_repository = $this->createMock(IContainerRepository::class);
		$container_repository->method('find')->willReturn($container);

		$procedure_repository = $this->createMock(IProcedureRepository::class);
		$procedure_repository->method('find')->willReturn($procedure);
		$procedure_repository->method('nextProcedureId')->willReturn(new ProcedureId(1));

		$comment_repository = $this->createMock(ICommentRepository::class);
		$comment_repository->method('find')->willReturn($comment);
		$comment_repository->method('stepExists')->willReturn(false); 

		$attachment_repository = $this->createMock(IAttachmentRepository::class);
		$attachment_repository->method('find')->willReturn($attachment);

		$identity_provider = $this->createMock(IIdentityProvider::class);
		$identity_provider->method('identity')->willReturn(1);

		$procedure_management_service = new ProcedureManagementService(
			$container_repository, $procedure_repository, $comment_repository, $attachment_repository, $identity_provider
		);
	
		$procedure_management_service->addAttachment(1,1,'base64', 'attachment-name');
	}

	public function test_If_removeAttachment_Throws_Exception_Personnel_Has_No_Privilege(){

		$this->expectException(AttachmentInsufficientPrivilegeException::class);

		$container = new Container(
			new ContainerId(2), 
			ContainerType::Structure()
		);

		$steps_arr = [
			new Step(new StepId(1),'this is the first title', true,1), 
			new Step(new StepId(2), 'this is the second title', true,2),
			new Step(new StepId(3), 'this is the third title', false,3)
		];
		
		$procedure = new Procedure(
			new ProcedureId(1), 
			new InitiatorId(1234567890), 
			'this is the procedure title', 
			$steps_arr, 
			ProcedureType::ConstructionPermit(),
			true);

		$comment = new Comment(
			new CommentId(1),
			new StepId(1), 
			new PersonnelId(1),
			'this is the comment', 
			new DateTime(), 
			new DateTime()
		);

		$attachment = new Attachment(
			new AttachmentId(1),
			new StepId(1),
			null,				// this will trigger the exception, personnelid should have been 1 in this case.
			'Attachment name...',
			'base64',
			new DateTime()
		);

		$container_repository = $this->createMock(IContainerRepository::class);
		$container_repository->method('find')->willReturn($container);

		$procedure_repository = $this->createMock(IProcedureRepository::class);
		$procedure_repository->method('find')->willReturn($procedure);
		$procedure_repository->method('nextProcedureId')->willReturn(new ProcedureId(1));

		$comment_repository = $this->createMock(ICommentRepository::class);
		$comment_repository->method('find')->willReturn($comment);
		$comment_repository->method('stepExists')->willReturn(true); 

		$attachment_repository = $this->createMock(IAttachmentRepository::class);
		$attachment_repository->method('find')->willReturn($attachment);

		$identity_provider = $this->createMock(IIdentityProvider::class);
		$identity_provider->method('identity')->willReturn(1);

		$procedure_management_service = new ProcedureManagementService(
			$container_repository, $procedure_repository, $comment_repository, $attachment_repository, $identity_provider
		);

		$procedure_management_service->removeAttachment(1);
	}

	public function test_If_Test_Throws_An_Exception_When_Attachment_Isnt_Found(){

		$this->expectException(AttachmentNotFoundException::class);

		$container = new Container(
			new ContainerId(2), 
			ContainerType::Structure()
		);

		$steps_arr = [
			new Step(new StepId(1),'this is the first title', true,1), 
			new Step(new StepId(2), 'this is the second title', true,2),
			new Step(new StepId(3), 'this is the third title', false,3)
		];
		
		$procedure = new Procedure(
			new ProcedureId(1), 
			new InitiatorId(1234567890), 
			'this is the procedure title', 
			$steps_arr, 
			ProcedureType::ConstructionPermit(),
			true);

		$comment = new Comment(
			new CommentId(1),
			new StepId(1), 
			new PersonnelId(1),
			'this is the comment', 
			new DateTime(), 
			new DateTime()
		);

		$attachment = null;

		$container_repository = $this->createMock(IContainerRepository::class);
		$container_repository->method('find')->willReturn($container);

		$procedure_repository = $this->createMock(IProcedureRepository::class);
		$procedure_repository->method('find')->willReturn($procedure);
		$procedure_repository->method('nextProcedureId')->willReturn(new ProcedureId(1));

		$comment_repository = $this->createMock(ICommentRepository::class);
		$comment_repository->method('find')->willReturn($comment);
		$comment_repository->method('stepExists')->willReturn(true); 

		$attachment_repository = $this->createMock(IAttachmentRepository::class);
		$attachment_repository->method('find')->willReturn($attachment);

		$identity_provider = $this->createMock(IIdentityProvider::class);
		$identity_provider->method('identity')->willReturn(1);

		$procedure_management_service = new ProcedureManagementService(
			$container_repository, $procedure_repository, $comment_repository, $attachment_repository, $identity_provider
		);

		$procedure_management_service->removeAttachment(1);
	}
}

?>