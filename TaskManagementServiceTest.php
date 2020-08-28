s<?php

use model\TaskManagement\application\TaskManagementService;
use \model\TaskManagement\domain\model\TaskId;
use \model\TaskManagement\domain\model\Task;
use \model\TaskManagement\domain\model\SubtaskId;
use \model\TaskManagement\domain\model\Subtask;
use \model\TaskManagement\domain\model\Attachment;
use \model\TaskManagement\domain\model\AttachmentId;
use \model\TaskManagement\domain\model\Event;
use \model\TaskManagement\domain\model\EventId;
use \model\TaskManagement\domain\model\EventType;
use \model\TaskManagement\domain\model\PersonnelId;
use \model\TaskManagement\domain\model\ITaskRepository;
use \model\TaskManagement\application\IIdentityProvider;
use \model\TaskManagement\application\IPersonnelValidator;
use \model\TaskManagement\application\IPersonnelAccessResolver;
use \model\TaskManagement\application\IFileDirectAccessLinkProvider;
use model\TaskManagement\domain\model\exception\TaskRemovePrivilegeException;
use \model\TaskManagement\application\exception\PersonnelNotReachableException;
use \model\TaskManagement\application\exception\PersonnelNotFoundException;
use \model\TaskManagement\application\exception\TaskNotFoundException;
use \model\TaskManagement\domain\model\exception\TaskSubtaskNotFoundException;
use \model\TaskManagement\domain\model\exception\TaskCreateSubtaskPrivilegeException;
use model\TaskManagement\domain\model\exception\TaskRemoveSubtaskPrivilegeException;
use \model\TaskManagement\application\exception\SubtaskNotFoundException;
use \model\TaskManagement\domain\model\exception\CommentNotFoundException;
use model\TaskManagement\domain\model\Comment;
use \model\TaskManagement\domain\model\CommentId;

use model\common\QueryObject;
use model\common\ExceptionCollection;

use PHPUnit\Framework\TestCase;

class TaskManagementServiceTest extends TestCase {

private  TaskManagementService $task_management_service;
	
	public function test_If_createTask_Returns_Created_Tasks_Id(){

		$task = new Task(
			null,
			'TASK-TEST TITLE', 
			new PersonnelId(1), 
			null, 
			'ETHEREUM',
			null, 
			null, 
			null, 
			null, 
			null,
			null, 
			null, 
			null, 
			null, 
			null, 
			null);


		$task_repository = $this->createMock(ITaskRepository::class);
		
		$task_repository->method('save')->willReturn(new TaskId(1));
		
		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  //returns integer, can be null. 

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturnCallback(
			function($per_id) use ($task) {

				$personnel_id = $per_id->getId();
				if($personnel_id == 1)
					return true;

				else if(null == $personnel_id)
					return false;
			}
		);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);
			

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class);
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$id_stored_as_integer = $this->task_management_service->createTask('title', null, null, null, null, null, null, null);

		$this->assertEquals(1, $id_stored_as_integer);

	}


	public function test_If_createTask_Throws_Exception_When_Source_Personnel_Has_No_Access(){


		$this->expectException(PersonnelNotReachableException::class);
		
		try{

		$assignee_arr = array(new PersonnelId(1));
		$assignee_arr_number = array(1);

		$task = new Task( null, 'TASK-TEST TITLE', new PersonnelId(1), $assignee_arr, 'ETHEREUM' ,null, null, null, null, null,null, 	null, 	null, 	null, null, null);

		$task_repository = $this->createMock(ITaskRepository::class);
		
		$task_repository->method('save')->willReturn(new TaskId(1));

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  //returns integer, can be null. 

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(false);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class);
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$this->task_management_service->createTask('title', $assignee_arr_number, null, null, null, null, null, null);
			
		} catch (ExceptionCollection $e){

		$this->throwFromExceptionCollection($e, PersonnelNotReachableException::class);
		
		}
	}


	public function test_If_createTask_Throws_Exception_When_Source_Personnel_Is_Null(){


		$this->expectException(PersonnelNotReachableException::class);
		
		try{

		$assignee_arr = array(new PersonnelId(1));
		$assignee_arr_number = array(1);

		$task = new Task( null, 'TASK-TEST TITLE', null, $assignee_arr, 'ETHEREUM' ,null, null, null, null, null,null, 	null, 	null, 	null, null, null);

		$task_repository = $this->createMock(ITaskRepository::class);
		
		$task_repository->method('save')->willReturn(new TaskId(1));
		
		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  //returns integer, can be null. 

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(false);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class);
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$this->task_management_service->createTask('title', $assignee_arr_number, null, null, null, null, null, null);
			
		} catch (ExceptionCollection $e){

		$this->throwFromExceptionCollection($e, PersonnelNotFoundException::class);
		
		}
	}


	public function test_If_removeTask_Removes_The_Task(){

		$task = new Task(
		new TaskId(1),
		'TASK-TEST TITLE', 
		new PersonnelId(1), 
		null, 
		'ETHEREUM',
		null, 
		null, 
		null, 
		null, 
		null,
		null, 
		null, 
		null, 
		null, 
		null, 
		null);

		$task_repository = $this->createMock(ITaskRepository::class);
		
		$task_repository->method('find')->willReturnCallback(
			function($t_id) use ($task) {

				$task_id = $t_id->getId();
				if($task_id == 1){
					return $task;
				}

				else{return null;}
			}
		);

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturnCallback(
			function($per_id) use ($task) {

				$personnel_id = $per_id->getId();
				if($personnel_id == 1)
					return true;

				else if(null == $personnel_id)
					return false;
			}
		);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);


		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class);
		$file_access_provider->method('getLink')->willReturn('this is a link string');


		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$task_removed = $this->task_management_service->removeTask(1);

		$this->assertEmpty($task_removed);

	}


	public function test_If_removeTask_Throws_Exception_When_Personnel_Has_No_Privilege(){

		$this->expectException(TaskRemovePrivilegeException::class);

		try{

		$task = new Task(
		new TaskId(1),
		'TASK-TEST TITLE', 
		null, // should assigner(personnel_id) be null, exception must be thrown. 
		null, 
		'ETHEREUM',
		null, 
		null, 
		null, 
		null, 
		null,
		null, 
		null, 
		null, 
		null, 
		null, 
		null);

	
		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('find')->willReturnCallback(
			function($t_id) use ($task) {

				$task_id = $t_id->getId();
				if($task_id == 1){
					return $task;
				}

				else{return null;}
			}
		);

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  


		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturnCallback(
			function($per_id) use ($task) {

				$personnel_id = $per_id->getId();
				if($personnel_id == 1)
					return true;

				else if(null == $personnel_id)
					return false;
			}
		);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);


		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class);
		$file_access_provider->method('getLink')->willReturn('this is a link string');


		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$this->task_management_service->removeTask(1);

		} catch(ExceptionCollection $w){

			$this->throwFromExceptionCollection($w, TaskRemovePrivilegeException::class);

		}
	}

	public function test_If_assignTaskTo_Throws_Exception_When_Personnel_Has_No_Access(){

		$this->expectException(PersonnelNotReachableException::class);

		try{

		$task = new Task(
		null,
		'TASK-TEST TITLE', 
		new PersonnelId(1),
		null, 
		'ETHEREUM',
		null, 
		null, 
		null, 
		null, 
		null,
		null, 
		null, 
		null, 
		null, 
		null, 
		null);

		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('find')->willReturn($task);
		
		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  //returns integer, can be null. 

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(false); // this bool decides whether exception will be thrown or not

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$this->task_management_service->assignTaskTo(1,1); // task_id & personnel_id
 		
		} catch(ExceptionCollection $e) {

			$this->throwFromExceptionCollection($e, PersonnelNotReachableException::class);
		}

	}


	public function test_If_assignTaskTo_Throws_Exception_When_Task_Isnt_Found(){

		$this->expectException(TaskNotFoundException::class);

		try{

		$task = new Task(
		null,
		'TASK-TEST TITLE', 
		new PersonnelId(1),
		null, 
		'ETHEREUM',
		null, 
		null, 
		null, 
		null, 
		null,
		null, 
		null, 
		null, 
		null, 
		null, 
		null);

		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('find')->willReturnCallback(
			function($t_id) use ($task) {

				$task_id = $t_id->getId();
				if($task_id == 2){ //taskid is 1, this should throw exc.
					return $task;
				}

				else{return null;}
			}
		);		

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  //returns integer, can be null. 

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);
			

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$this->task_management_service->assignTaskTo(1,1); // task_id & personnel_id
 		
		} catch(ExceptionCollection $e) {

			$this->throwFromExceptionCollection($e, TaskNotFoundException::class);
		}

	}


	public function test_If_deassignTaskFrom_Throws_Exception_When_Personnel_Has_No_Access(){

		$this->expectException(PersonnelNotReachableException::class);	
		
		try{

		$task = new Task(
		null,
		'TASK-TEST TITLE', 
		new PersonnelId(1),
		null, 
		'ETHEREUM',
		null, 
		null, 
		null, 
		null, 
		null,
		null, 
		null, 
		null, 
		null, 
		null, 
		null);

		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('find')->willReturn($task);
		
		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  //returns integer, can be null. 

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(false);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$this->task_management_service->deassignTaskFrom(1,1);
		
		} catch(ExceptionCollection $e){

			$this->throwFromExceptionCollection($e, PersonnelNotReachableException::class);
		}
	}


	public function test_If_deassignTaskFrom_Throws_Exception_When_Task_Isnt_Found(){

		$this->expectException(TaskNotFoundException::class);	
		
		try{

		$task = new Task(
		null,
		'TASK-TEST TITLE', 
		new PersonnelId(1),
		null, 
		'ETHEREUM',
		null, 
		null, 
		null, 
		null, 
		null,
		null, 
		null, 
		null, 
		null, 
		null, 
		null);

		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('find')->willReturnCallback(
			function($t_id) use ($task) {

				$task_id = $t_id->getId();
				if($task_id == 2){ //taskid is 1, this should throw exc.
					return $task;
				}

				else{return null;}
			}
		);	

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  //returns integer, can be null. 

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$this->task_management_service->deassignTaskFrom(1,1);
		
		} catch(ExceptionCollection $e){

			$this->throwFromExceptionCollection($e, TaskNotFoundException::class);
		}
	}

	public function test_If_addComment_Throws_Exception_When_Task_Isnt_Found(){

		$this->expectException(TaskNotFoundException::class);

		try{

		$task = new Task(
		null,
		'TASK-TEST TITLE', 
		new PersonnelId(1),
		null, 
		'ETHEREUM',
		null, 
		null, 
		null, 
		null, 
		null,
		null, 
		null, 
		null, 
		null, 
		null, 
		null);


		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('find')->willReturnCallback(
			function($t_id) use ($task) {

				$task_id = $t_id->getId();
				if($task_id == 2){ 
					return $task;
				}

				else{return null;}
			}
		);	

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  //returns integer, can be null. 

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$this->task_management_service->addComment(1, 'this is the comment');

		} catch(ExceptionCollection $e) {

			$this->throwFromExceptionCollection($e, TaskNotFoundException::class);
		}	 
	}

	public function test_If_editComment_Throws_Exception_When_Task_Isnt_Found(){

		$this->expectException(TaskNotFoundException::class);

		try{

		$task = new Task(
		null,
		'TASK-TEST TITLE', 
		new PersonnelId(1),
		null, 
		'ETHEREUM',
		null, 
		null, 
		null, 
		null, 
		null,
		null, 
		null, 
		null, 
		null, 
		null, 
		null);


		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('find')->willReturnCallback(
			function($t_id) use ($task) {

				$task_id = $t_id->getId();
				if($task_id == 2){ 
					return $task;
				}

				else{return null;}
			}
		);	

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  //returns integer, can be null. 

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$this->task_management_service->editComment(1,1, 'this is the comment');

		} catch(ExceptionCollection $e) {

			$this->throwFromExceptionCollection($e, TaskNotFoundException::class);
		}	 
	}


	public function test_If_removeComment_Throws_Exception_When_Task_Isnt_Found(){

		$this->expectException(TaskNotFoundException::class);

		try{

		$task = new Task(
		null,
		'TASK-TEST TITLE', 
		new PersonnelId(1),
		null, 
		'ETHEREUM',
		null, 
		null, 
		null, 
		null, 
		null,
		null, 
		null, 
		null, 
		null, 
		null, 
		null);


		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('find')->willReturnCallback(
			function($t_id) use ($task) {

				$task_id = $t_id->getId();
				if($task_id == 2){ 
					return $task;
				}

				else{return null;}
			}
		);	

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  //returns integer, can be null. 

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$this->task_management_service->removeComment(1, 1);

		} catch(ExceptionCollection $e) {

			$this->throwFromExceptionCollection($e, TaskNotFoundException::class);
		}	 
	}


	public function test_If_addTaskAttachment_Throws_Exception_When_Task_Isnt_Found(){

		$this->expectException(TaskNotFoundException::class);

		try{

		$task = new Task(
		null,
		'TASK-TEST TITLE', 
		new PersonnelId(1),
		null, 
		'ETHEREUM',
		null, 
		null, 
		null, 
		null, 
		null,
		null, 
		null, 
		null, 
		null, 
		null, 
		null);


		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('find')->willReturnCallback(
			function($t_id) use ($task) {

				$task_id = $t_id->getId();
				if($task_id == 2){ 
					return $task;
				}

				else{return null;}
			}
		);	

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  //returns integer, can be null. 

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$this->task_management_service->addTaskAttachment(1, 'base64' , 'name');

		} catch(ExceptionCollection $e) {

			$this->throwFromExceptionCollection($e, TaskNotFoundException::class);
		}	 
	}


	public function test_If_removeTaskAttachment_Throws_Exception_When_Task_Isnt_Found(){

		$this->expectException(TaskNotFoundException::class);

		try{

		$task = new Task(
		null,
		'TASK-TEST TITLE', 
		new PersonnelId(1),
		null, 
		'ETHEREUM',
		null, 
		null, 
		null, 
		null, 
		null,
		null, 
		null, 
		null, 
		null, 
		null, 
		null);


		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('find')->willReturnCallback(
			function($t_id) use ($task) {

				$task_id = $t_id->getId();
				if($task_id == 2){ 
					return $task;
				}

				else{return null;}
			}
		);	

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  //returns integer, can be null. 

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$this->task_management_service->removeTaskAttachment(1,1);

		} catch(ExceptionCollection $e) {

			$this->throwFromExceptionCollection($e, TaskNotFoundException::class);
		}	 
	}

	public function test_If_batchUpdateTask_Throws_Exception_When_Task_Isnt_Found(){

		$this->expectException(TaskNotFoundException::class);

		try{
		
		$task = new Task(
		null,
		'TASK-TEST TITLE', 
		new PersonnelId(1),
		null, 
		'ETHEREUM',
		null, 
		null, 
		null, 
		null, 
		null,
		null, 
		null, 
		null, 
		null, 
		null, 
		null);


		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('find')->willReturnCallback(
			function($t_id) use ($task) {

				$task_id = $t_id->getId();
				if($task_id == 2){ 
					return $task;
				}

				else{return null;}
			}
		);	

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  //returns integer, can be null. 

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$this->task_management_service->batchUpdateTask(1, 'title', 'description' , null, null, null, null, null);

		} catch(ExceptionCollection $e) {

			$this->throwFromExceptionCollection($e , TaskNotFoundException::class);
		}
	}

	public function test_If_createSubtask_Throws_Exception_When_Assigner_And_Assignee_Ids_Are_Null(){

		$this->expectException(TaskCreateSubtaskPrivilegeException::class);

		try{

		$task = new Task(
		null,
		'TASK-TEST TITLE', 
		null, 
		null, 
		'ETHEREUM',
		null, 
		null, 
		null, 
		null, 
		null,
		null, 
		null, 
		null, 
		null, 
		null, 
		null);


		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('find')->willReturn($task);

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  //returns integer, can be null. 

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$this->task_management_service->createSubtask(1, 'title', null , 'description', null, null, null, null, null);

		} catch(ExceptionCollection $e) {

			$this->throwFromExceptionCollection($e, TaskCreateSubtaskPrivilegeException::class);
		}
	

	}


	public function test_If_createSubtask_Throws_Exception_When_Source_Personnel_Is_Null(){

		$this->expectException(PersonnelNotFoundException::class);

		try{
		
		$task = new Task(
		null,
		'TASK-TEST TITLE', 
		new PersonnelId(1), 
		null, 
		'ETHEREUM',
		null, 
		null, 
		null, 
		null, 
		null,
		null, 
		null, 
		null, 
		null, 
		null, 
		null);


		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('find')->willReturn($task);

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  //returns integer, can be null. 

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturnCallback(
			function($per_id) use ($task) {

				$personnel_id = $per_id->getId();
				if($personnel_id == 2) 
					return true;

				else if(null == $personnel_id)
					return false;

				return false;
			}
		);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$this->task_management_service->createSubtask(1, 'title', null , 'description', null, null, null, null, null);


		} catch(ExceptionCollection $e) {

			$this->throwFromExceptionCollection(PersonnelNotFoundException::class);
		}

	}

	public function test_If_createSubtask_Throws_Exception_When_Source_Personnel_Has_No_Access(){

		$this->expectException(PersonnelNotReachableException::class);

		try{
	
		$task = new Task(
		null,
		'TASK-TEST TITLE', 
		new PersonnelId(1), 
		null, 
		'ETHEREUM',
		null, 
		null, 
		null, 
		null, 
		null,
		null, 
		null, 
		null, 
		null, 
		null, 
		null);


		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('find')->willReturn($task);

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(false);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$assignee_arr = array(1);
		$this->task_management_service->createSubtask(1, 'title', $assignee_arr , 'description', null, null, null, null, null);

		} catch(ExceptionCollection $e) {

			$this->throwFromExceptionCollection($e, PersonnelNotReachableException::class);
		}
	}


	public function test_If_removeSubtask_Throws_Exception_When_Subtask_Isnt_Found(){

		$this->expectException(TaskSubtaskNotFoundException::class);

		try{

		$subtask_arr = array(
			new Subtask(new SubtaskId(2), null, 'title1', null,null,null,null,null,null, null,null,null,null,null, new DateTime('now'))); // subtask id was supposed to be 1, this will throw an exception.
		
		$task = new Task(
		null,
		'TASK-TEST TITLE', 
		new PersonnelId(1),  
		null, 
		'ETHEREUM',
		null, 
		null, 
		null, 
		$subtask_arr, 
		null,
		null, 
		null, 
		null, 
		null, 
		null, 
		null);


		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('findBySubtask')->willReturn($task);

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$this->task_management_service->removeSubtask(1);

		} catch(ExceptionCollection $e) {

			$this->throwFromExceptionCollection($e, TaskSubtaskNotFoundException::class);
		}
	}

	public function test_If_removeSubtask_Throws_Exception_When_Personnel_Has_No_Access(){

		$this->expectException(TaskRemoveSubtaskPrivilegeException::class);

		try{

		$subtask_arr = array(
			new Subtask(new SubtaskId(1), null, 'title1', null,null,null,null,null,null, null,null,null,null,null, new DateTime('now')));
		
		$task = new Task(
		null,
		'CHANGED TASK-TEST TITLE', 
		null,   	// assigner (personnel_id) is null given, this should trigger the exception.
		null, 
		'ETHEREUM',
		null, 
		null, 
		null, 
		$subtask_arr, 
		null,
		null, 
		null, 
		null, 
		null, 
		null, 
		null);


		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('findBySubtask')->willReturn($task);

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(false);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$this->task_management_service->removeSubtask(1);

		} catch(ExceptionCollection $e) {

			$this->throwFromExceptionCollection($e, TaskRemoveSubtaskPrivilegeException::class);
		}
	}

	public function test_If_assignSubtaskTo_Throws_Exception_When_Source_Personnel_Has_No_Access(){

		$this->expectException(PersonnelNotReachableException::class);

		try{

		$task = new Task(
		null,
		'CHANGED TASK-TEST TITLE', 
		new PersonnelId(1),   	
		null, 
		'ETHEREUM',
		null, 
		null, 
		null, 
		null, 
		null,
		null, 
		null, 
		null, 
		null, 
		null, 
		null);


		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('findBySubtask')->willReturn($task);

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(false);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$this->task_management_service->assignSubtaskTo(1,1); // assigner, assignee ids

		} catch(ExceptionCollection $e) {

			$this->throwFromExceptionCollection($e, PersonnelNotReachableException::class);
		}
	}

	public function test_If_assignSubtaskTo_Throws_Exception_When_Subtask_Isnt_Found(){

		$this->expectException(TaskNotFoundException::class);

		try{

		$task = new Task(
		null,
		'CHANGED TASK-TEST TITLE', 
		new PersonnelId(1),   	
		null, 
		'ETHEREUM',
		null, 
		null, 
		null, 
		null, 
		null,
		null, 
		null, 
		null, 
		null, 
		null, 
		null);


		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('find')->willReturnCallback(
			function($t_id) use ($task) {

				$task_id = $t_id->getId();
				if($task_id == 2){
					return $task;
				}

				else{return null;}
			}
		);

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$this->task_management_service->assignSubtaskTo(1,1); // assigner, assignee ids
		} catch(ExceptionCollection $e) {

			$this->throwFromExceptionCollection($e, TaskNotFoundException::class);
		}
	}


	public function test_If_deassignSubtaskFrom_Throws_Exception_When_Source_Personnel_Has_No_Access() {

		$this->expectException(PersonnelNotReachableException::class);

		try{
		
		$task = new Task(
		null,
		'CHANGED TASK-TEST TITLE', 
		new PersonnelId(1),   	
		null, 
		'ETHEREUM',
		null, 
		null, 
		null, 
		null, 
		null,
		null, 
		null, 
		null, 
		null, 
		null, 
		null);


		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('findBySubtask')->willReturn($task);

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(false);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$this->task_management_service->deassignSubtaskFrom(1,1);

		} catch(ExceptionCollection $w) {
			$this->throwFromExceptionCollection($w, PersonnelNotReachableException::class);
		}
	}

	public function test_If_deassignSubtaskFrom_Throws_Exception_When_Subtask_Isnt_Found(){

		$this->expectException(TaskSubtaskNotFoundException::class);

		try{
		
		$task = new Task(
		null,
		'CHANGED TASK-TEST TITLE', 
		null,   	
		null, 
		'ETHEREUM',
		null, 
		null, 
		null, 
		null, 
		null,
		null, 
		null, 
		null, 
		null, 
		null, 
		null);


		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('findBySubtask')->willReturn($task);

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$this->task_management_service->deassignSubtaskFrom(1,1);

		} catch(ExceptionCollection $e){

			$this->throwFromExceptionCollection($e, TaskSubtaskNotFoundException::class);
		}
	}


	public function test_If_commentOnSubtask_Throws_Exception_When_Source_Subtask_Isnt_Found(){

		$this->expectException(TaskSubtaskNotFoundException::class);

		try{

		$subtask_arr = array(
			new Subtask(new SubtaskId(2), null, 'title1', null,null,null,null,null,null, null,null,null,null,null, new DateTime('now')));

		$task = new Task(
		null,
		'CHANGED TASK-TEST TITLE', 
		new PersonnelId(1),   	
		null, 
		'ETHEREUM',
		null, 
		null, 
		null, 
		$subtask_arr, 
		null,
		null, 
		null, 
		null, 
		null, 
		null, 
		null);


		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('findBySubtask')->willReturn($task);

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);


		$this->task_management_service->commentOnSubtask(1, 'this is the comment');

		} catch(ExceptionCollection $w) {
			$this->throwFromExceptionCollection($w, TaskSubtaskNotFoundException::class);
		}
	}


	public function test_If_editSubtaskComment_Throws_Exception_When_Source_Subtask_Isnt_Found(){

		$this->expectException(TaskSubtaskNotFoundException::class);

		try{

		$subtask_arr = array(
			new Subtask(new SubtaskId(2), null, 'title1', null,null,null,null,null,null, null,null,null,null,null, new DateTime('now')));

		$task = new Task(
		null,
		'CHANGED TASK-TEST TITLE', 
		new PersonnelId(1),   	
		null, 
		'ETHEREUM',
		null, 
		null, 
		null, 
		$subtask_arr, 
		null,
		null, 
		null, 
		null, 
		null, 
		null, 
		null);


		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('findBySubtask')->willReturn($task);

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);


		$this->task_management_service->commentOnSubtask(1, 1, 'this is the comment');

		} catch(ExceptionCollection $w) {
			$this->throwFromExceptionCollection($w, TaskSubtaskNotFoundException::class);
		}
	}


	public function test_If_removeSubtaskComment_Throws_Exception_When_Source_Subtask_Isnt_Found(){

		$this->expectException(TaskSubtaskNotFoundException::class);

		try{

		$subtask_arr = array(
			new Subtask(new SubtaskId(2), null, 'title1', null,null,null,null,null,null, null,null,null,null,null, new DateTime('now')));

		$task = new Task(
		null,
		'CHANGED TASK-TEST TITLE', 
		new PersonnelId(1),   	
		null, 
		'ETHEREUM',
		null, 
		null, 
		null, 
		$subtask_arr, 
		null,
		null, 
		null, 
		null, 
		null, 
		null, 
		null);


		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('findBySubtask')->willReturn($task);

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);


		$this->task_management_service->removeSubtaskComment(1, 1);

		} catch(ExceptionCollection $w) {
			$this->throwFromExceptionCollection($w, TaskSubtaskNotFoundException::class);
		}
	}


	public function test_If_addSubtaskAttachment_Throws_Exception_When_Source_Subtask_Isnt_Found(){

		$this->expectException(TaskSubtaskNotFoundException::class);

		try{

		$subtask_arr = array(
			new Subtask(new SubtaskId(2), null, 'title1', null,null,null,null,null,null, null,null,null,null,null, new DateTime('now')));

		$task = new Task(
		null,
		'CHANGED TASK-TEST TITLE', 
		new PersonnelId(1),   	
		null, 
		'ETHEREUM',
		null, 
		null, 
		null, 
		$subtask_arr, 
		null,
		null, 
		null, 
		null, 
		null, 
		null, 
		null);


		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('findBySubtask')->willReturn($task);

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);


		$this->task_management_service->addSubtaskAttachment(1, 1, 'base64', 'this is attachment name');

		} catch(ExceptionCollection $w) {
			$this->throwFromExceptionCollection($w, TaskSubtaskNotFoundException::class);
		}
	}


	public function test_If_removeSubtaskAttachment_Throws_Exception_When_Source_Subtask_Isnt_Found(){

		$this->expectException(TaskSubtaskNotFoundException::class);

		try{

		$subtask_arr = array(
			new Subtask(new SubtaskId(2), null, 'title1', null,null,null,null,null,null, null,null,null,null,null, new DateTime('now')));

		$task = new Task(
		null,
		'CHANGED TASK-TEST TITLE', 
		new PersonnelId(1),   	
		null, 
		'ETHEREUM',
		null, 
		null, 
		null, 
		$subtask_arr, 
		null,
		null, 
		null, 
		null, 
		null, 
		null, 
		null);


		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('findBySubtask')->willReturn($task);

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);


		$this->task_management_service->removeSubtaskAttachment(1, 1, 1);

		} catch(ExceptionCollection $w) {
			$this->throwFromExceptionCollection($w, TaskSubtaskNotFoundException::class);
		}
	}


	public function test_If_batchUpdateSubtask_Throws_Exception_When_Task_Isnt_Found(){

		$this->expectException(TaskSubtaskNotFoundException::class);

		try{

		$subtask_arr = array(
			new Subtask(new SubtaskId(2), null, 'title1', null,null,null,null,null,null, null,null,null,null,null, new DateTime('now')));

		$task = new Task(
		null,
		'CHANGED TASK-TEST TITLE', 
		new PersonnelId(1),   	
		null, 
		'ETHEREUM',
		null, 
		null, 
		null, 
		$subtask_arr, 
		null,
		null, 
		null, 
		null, 
		null, 
		null, 
		null);


		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('findBySubtask')->willReturn($task);

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);


		$this->task_management_service->batchUpdateSubtask(1, null, null, null, null, null, null, null);

		} catch(ExceptionCollection $w) {
			$this->throwFromExceptionCollection($w, TaskSubtaskNotFoundException::class);
		}
	}

	public function test_If_getSelfOwnedTasks_Returns_Related_Tasks_DTO(){

		$task = new Task(
		null,
		'CHANGED TASK-TEST TITLE', 
		new PersonnelId(1),   	
		null, 
		'ETHEREUM',
		null, 
		null, 
		null, 
		null, 
		null,
		null, 
		null, 
		null, 
		null, 
		null, 
		null);


		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('find')->willReturn($task);

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$return_dto = $this->task_management_service->getSelfOwnedTasks(new QueryObject());

		$this->assertNotEmpty($return_dto);
	}


	public function test_If_getSingleSelfOwnedTask_Returns_Task_View_Model(){

		$task = new Task(
		new TaskId(1),
		'CHANGED TASK-TEST TITLE', 
		new PersonnelId(1),   	
		null, 
		'ETHEREUM',
		null, 
		null, 
		null, 
		null, 
		null,
		null, 
		null, 
		null, 
		null, 
		null, 
		new DateTime());


		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('find')->willReturn($task);

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$return_view_model = $this->task_management_service->getSingleSelfOwnedTask(1);

		$this->assertNotEmpty($return_view_model);

	}


	public function test_If_getSingleSelfOwnedTask_Throws_Exception_When_Task_Isnt_Found(){

		$this->expectException(TaskNotFoundException::class);

		try{

		$task = new Task(
		new TaskId(1),
		'CHANGED TASK-TEST TITLE', 
		new PersonnelId(1),   	
		null, 
		'ETHEREUM',
		null, 
		null, 
		null, 
		null, 
		null,
		null, 
		null, 
		null, 
		null, 
		null, 
		new DateTime());


		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('find')->willReturnCallback(
			function($t_id) use ($task) {

				$task_id = $t_id->getId();
				if($task_id == 2){ 
					return $task;
				}

				else{return null;}
			}
		);	

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$return_view_model = $this->task_management_service->getSingleSelfOwnedTask(1);

		} catch(ExceptionCollection $e) {

			$this->throwFromExceptionCollection($e, TaskNotFoundException::class);
		}

	}

	public function testIf_getSelfOwnedSubtasksOfTask_Returns_Subtasks_View_Model_Array(){


		$subtask_arr = array(
			new Subtask(new SubtaskId(2), null, 'CHANGED TITLE!!!!', null,null,null,null,null,null, null,null,null,null,null, new DateTime('now')));

		$task = new Task(
		new TaskId(1),
		'CHANGED TASK-TEST TITLE', 
		new PersonnelId(1),   	
		null, 
		'ETHEREUM',
		null, 
		null, 
		null, 
		$subtask_arr, 
		null,
		null, 
		null, 
		null, 
		null, 
		null, 
		new DateTime());


		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('find')->willReturn($task);

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$subtasks_vm_arr = $this->task_management_service->getSelfOwnedSubtasksOfTask(1);
		$this->assertNotEmpty($subtasks_vm_arr);

	}


	public function test_If_getSelfOwnedSubtasksOfTask_Throws_Exception_When_Task_Isnt_Found(){

		$this->expectException(TaskNotFoundException::class);

		try{
		
		$subtask_arr = array(
			new Subtask(new SubtaskId(2), null, 'CHANGED TITLE!!!!', null,null,null,null,null,null, null,null,null,null,null, new DateTime('now')));

		$task = new Task(
		new TaskId(1),
		'CHANGED TASK-TEST TITLE', 
		new PersonnelId(1),   	
		null, 
		'ETHEREUM',
		null, 
		null, 
		null, 
		$subtask_arr, 
		null,
		null, 
		null, 
		null, 
		null, 
		null, 
		new DateTime());


		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('find')->willReturnCallback(
			function($t_id) use ($task) {

				$task_id = $t_id->getId();
				if($task_id == 2){ 
					return $task;
				}

				else{return null;}
			}
		);	
		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$subtasks_vm_arr = $this->task_management_service->getSelfOwnedSubtasksOfTask(1);
		$this->assertNotEmpty($subtasks_vm_arr);

		} catch(ExceptionCollection $e) {

			$this->throwFromExceptionCollection($e, TaskNotFoundException::class);
		}

	}

	
	public function test_If_getSingleSelfOwnedSubtask_Returns_Related_Subtasks_DTO(){

		$subtask_arr = array(
			new Subtask(new SubtaskId(1), null, 'New Title', null,null,null,null,null,null, null,null,null,null,null, new DateTime('now')));

		$task = new Task(
		new TaskId(1),
		'CHANGED TASK-TEST TITLE', 
		new PersonnelId(1),   	
		null, 
		'ETHEREUM',
		null, 
		null, 
		null, 
		$subtask_arr, 
		null,
		null, 
		null, 
		null, 
		null, 
		null, 
		new DateTime());


		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('findBySubtask')->willReturn($task);

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$subtask_dto = $this->task_management_service->getSingleSelfOwnedSubtask(1);
		$this->assertNotEmpty($subtask_dto);
	}


	public function test_If_getSingleSelfOwnedSubtask_Throws_Exception_When_Task_Isnt_Found() {

		$this->expectException(TaskNotFoundException::class);

		try{

		$subtask_arr = array(
			new Subtask(new SubtaskId(1), null, 'New Title', null,null,null,null,null,null, null,null,null,null,null, new DateTime('now')));

		$task = new Task(
		new TaskId(1),
		'CHANGED TASK-TEST TITLE', 
		new PersonnelId(1),   	
		null, 
		'ETHEREUM',
		null, 
		null, 
		null, 
		$subtask_arr, 
		null,
		null, 
		null, 
		null, 
		null, 
		null, 
		new DateTime());


		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('find')->willReturnCallback(
			function($t_id) use ($task) {

				$task_id = $t_id->getId();
				if($task_id == 2){ 
					return $task;
				}

				else{return null;}
			}
		);

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$this->task_management_service->getSingleSelfOwnedSubtask(1);

		} catch(ExceptionCollection $w) {

			$this->throwFromExceptionCollection($w, TaskNotFoundException::class);
		}
	}


	public function test_If_getSingleSelfOwnedSubtask_Throws_Exception_When_Subtask_Isnt_Found() {

		$this->expectException(SubtaskNotFoundException::class);

		try{

		$task = new Task(
		new TaskId(1),
		'CHANGED TASK-TEST TITLE', 
		new PersonnelId(1),   	
		null, 
		'BTC',
		null, 
		null, 
		null, 
		null, 
		null,
		null, 
		null, 
		null, 
		null, 
		null, 
		new DateTime());


		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('findBySubtask')->willReturn($task);
		
		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$this->task_management_service->getSingleSelfOwnedSubtask(1);

		} catch(ExceptionCollection $w) {

			$this->throwFromExceptionCollection($w, SubtaskNotFoundException::class);
		}
	}
	public function testIf_getLastCreatedSelfOwnedSubtask_Returns_Related_Subtasks_DTO(){

		$subtask_arr = array(
			new Subtask(new SubtaskId(1), null, 'New Title', null,null,null,null,null,null, null,null,null,null,null, new DateTime('now')));

		$task = new Task(
		new TaskId(1),
		'CHANGED TASK-TEST TITLE', 
		new PersonnelId(1),   	
		null, 
		'BTC',
		null, 
		null, 
		null, 
		$subtask_arr, 
		null,
		null, 
		null, 
		null, 
		null, 
		null, 
		new DateTime());


		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('find')->willReturn($task);
		
		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$subtask_dto = $this->task_management_service->getLastCreatedSelfOwnedSubtask(1);
		$this->assertNotEmpty($subtask_dto);
	}


	public function test_If_getLastCreatedSelfOwnedSubtask_Throws_Exception_When_Subtask_Isnt_Found(){

		$this->expectException(TaskNotFoundException::class);

		try{

		$task = new Task(
		new TaskId(1),
		'CHANGED TASK-TEST TITLE', 
		new PersonnelId(1),   	
		null, 
		'BTC',
		null, 
		null, 
		null, 
		null, 
		null,
		null, 
		null, 
		null, 
		null, 
		null, 
		new DateTime());


		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('find')->willReturnCallback(
			function($t_id) use ($task) {

				$task_id = $t_id->getId();
				if($task_id == 2){ 
					return $task;
				}

				else{return null;}
			}
		);	

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$this->task_management_service->getLastCreatedSelfOwnedSubtask(1);

		} catch(ExceptionCollection $e) {

			$this->throwFromExceptionCollection($e, TaskNotFoundException::class);
		}
	}


	public function test_If_getSelfOwnedTaskAssignees_Returns_Assignee_Id_Array(){


		$assignee_arr = array(new PersonnelId(1));

		$task = new Task(

		new TaskId(1),
		'CHANGED TASK-TEST TITLE', 
		new PersonnelId(1),   	
		$assignee_arr, 
		'BTC',
		null, 
		null, 
		null, 
		null, 
		null,
		null, 
		null, 
		null, 
		null, 
		null, 
		new DateTime());


		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('find')->willReturn($task);

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$stored_assignee_arrays = $this->task_management_service->getSelfOwnedTaskAssignees(1);
		$this->assertNotEmpty($stored_assignee_arrays);
	}

	public function test_If_getSelfOwnedTaskAssignees_Throws_Exception_When_Personnel_Has_No_Access(){

		$this->expectException(TaskNotFoundException::class);

		try{

		$assignee_arr = array(new PersonnelId(1));
		$task = new Task(

		new TaskId(1),
		'CHANGED TASK-TEST TITLE', 
		null,   	
		null, 
		'BTC',
		null, 
		null, 
		null, 
		null, 
		null,
		null, 
		null, 
		null, 
		null, 
		null, 
		new DateTime());


		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('find')->willReturn($task);

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(false);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$this->task_management_service->getSelfOwnedTaskAssignees(1);
		
		} catch(ExceptionCollection $e) {

			$this->throwFromExceptionCollection($e, TaskNotFoundException::class);
		}
	}

	public function test_If_getSelfOwnedSubtaskAssignees_Returns_Assignee_Id_Array(){

		$subtask_assignee_arr = array(new PersonnelId(1));
		$subtask_arr = array(
			new Subtask(new SubtaskId(1), null, 'title1', null,$subtask_assignee_arr,null,null,null,null, null,null,null,null,null, new DateTime()));
		$task = new Task(

		new TaskId(1),
		'CHANGED TASK-TEST TITLE', 
		new PersonnelId(1),   	
		null, 
		'BTC',
		null, 
		null, 
		null, 
		$subtask_arr, 
		null,
		null, 
		null, 
		null, 
		null, 
		null, 
		new DateTime());


		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('findBySubtask')->willReturn($task);

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$stored_assignee_arrays = $this->task_management_service->getSelfOwnedSubtaskAssignees(1,1);
		$this->assertNotEmpty($stored_assignee_arrays);

 }


 	public function test_If_getSelfOwnedSubtaskAssignees_Throws_Exception_When_Personnel_Has_No_Access(){

 		$this->expectException(TaskNotFoundException::class);

 		try{
		
		$task = new Task( // assigner and assignee is null, should throw exception

		new TaskId(1),
		'CHANGED TASK-TEST TITLE', 
		null,   	
		null, 
		'BTC',
		null, 
		null, 
		null, 
		null, 
		null,
		null, 
		null, 
		null, 
		null, 
		null, 
		new DateTime());

		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('findBySubtask')->willReturn($task);

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$this->task_management_service->getSelfOwnedSubtaskAssignees(1,1);

 		} catch(ExceptionCollection $w){

 			$this->throwFromExceptionCollection($w, TaskNotFoundException::class);
 		}
 	}


 	public function test_If_getSelfOwnedTaskComments_Returns_Comment_View_Model_Array(){

 		$comments_arr = array(new Comment(new CommentId(1), new PersonnelId(1), 'this is the comment', new DateTime(), new DateTime()));
 		$task = new Task( 

		new TaskId(1),
		'CHANGED TASK-TEST TITLE', 
		new PersonnelId(1),   	
		null, 
		'BTC',
		null, 
		null, 
		null, 
		null, 
		null,
		null, 
		null, 
		$comments_arr, 
		null, 
		null, 
		new DateTime());

		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('find')->willReturn($task);

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$stored_comment_vm = $this->task_management_service->getSelfOwnedTaskComments(1);
		$this->assertNotEmpty($stored_comment_vm);
 	}


 	public function test_If_getSelfOwnedTaskComments_Throws_Exception_When_Personnel_Has_No_Access(){

 		$this->expectException(TaskNotFoundException::class);

 		try{

 		$task = new Task( 
		new TaskId(1),
		'CHANGED TASK-TEST TITLE', 
		null,   	
		null, 
		'BTC',
		null, 
		null, 
		null, 
		null, 
		null,
		null, 
		null, 
		null, 
		null, 
		null, 
		new DateTime());

		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('find')->willReturn($task);

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$stored_comment_vm = $this->task_management_service->getSelfOwnedTaskComments(1);

 		} catch(ExceptionCollection $e){

 			$this->throwFromExceptionCollection($e, TaskNotFoundException::class);
 		}
 	}


 	public function test_If_getSelfOwnedTaskCommentCommentator_Returns_The_Number_Of_Commentators(){

 		
 		$comments_arr = array(new Comment(new CommentId(1), new PersonnelId(1), 'this is the comment', new DateTime(), new DateTime()));
 		$task = new Task( 

		new TaskId(1),
		'CHANGED TASK-TEST TITLE', 
		new PersonnelId(1),   	
		null, 
		'BTC',
		null, 
		null, 
		null, 
		null, 
		null,
		null, 
		null, 
		$comments_arr, 
		null, 
		null, 
		new DateTime());

		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('find')->willReturn($task);

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$number_of_commentators = $this->task_management_service->getSelfOwnedTaskCommentCommentator(1,1); // this one returns the number of commentators

		$this->assertEquals(1, $number_of_commentators);
 	}


 	public function test_If_getSelfOwnedTaskCommentCommentator_Throws_Exception_When_Personnel_Has_No_Access(){

 		$this->expectException(TaskNotFoundException::class);

 		try{
		
		$comments_arr = array(new Comment(new CommentId(1), new PersonnelId(1), 'this is the comment', new DateTime(), new DateTime()));
 		$task = new Task( 

		new TaskId(1),
		'CHANGED TASK-TEST TITLE', 
		null,   	
		null, 
		'BTC',
		null, 
		null, 
		null, 
		null, 
		null,
		null, 
		null, 
		$comments_arr, 
		null, 
		null, 
		new DateTime());

		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('find')->willReturn($task);

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$this->task_management_service->getSelfOwnedTaskCommentCommentator(1,1);

 		} catch(ExceptionCollection $e){

 			$this->throwFromExceptionCollection($e, TaskNotFoundException::class);
 		}
 	}


 	public function test_If_getSelfOwnedTaskCommentCommentator_Throws_Exception_When_Comment_Array_Is_Null(){

 		$this->expectException(CommentNotFoundException::class);

 		try{

		$task = new Task( 

		new TaskId(1),
		'CHANGED TASK-TEST TITLE', 
		new PersonnelId(1),   	
		null, 
		'BTC',
		null, 
		null, 
		null, 
		null, 
		null,
		null, 
		null, 
		null, 
		null, 
		null, 
		new DateTime());

		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('find')->willReturn($task);

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$this->task_management_service->getSelfOwnedTaskCommentCommentator(1,1);

 		} catch(ExceptionCollection $w) {

 			$this->throwFromExceptionCollection($w, CommentNotFoundException::class);
 		}
 	}


 	public function test_If_getTaskMostRecentSelfOwnedComment_Returns_Comment_View_Model(){

 		$comments_arr = array(new Comment(new CommentId(1), new PersonnelId(1), 'this is the comment', new DateTime(), new DateTime()));
		$task = new Task( 

		new TaskId(1),
		'CHANGED TASK-TEST TITLE', 
		new PersonnelId(1),   	
		null, 
		'BTC',
		null, 
		null, 
		null, 
		null, 
		null,
		null, 
		null, 
		$comments_arr, 
		null, 
		null, 
		new DateTime());

		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('find')->willReturn($task);

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$stored_comment_vm = $this->task_management_service->getTaskMostRecentSelfOwnedComment(1);
		$this->assertNotEmpty($stored_comment_vm); 		
 	}


 	public function test_If_getTaskMostRecentSelfOwnedComment_Throws_Exception_When_Personnel_Has_No_Access(){

 		$this->expectException(TaskNotFoundException::class);

 		try{

 		$comments_arr = array(new Comment(new CommentId(1), new PersonnelId(1), 'this is the comment', new DateTime(), new DateTime()));
		$task = new Task( 

		new TaskId(1),
		'CHANGED TASK-TEST TITLE', 
		null,   	
		null, 
		'BTC',
		null, 
		null, 
		null, 
		null, 
		null,
		null, 
		null, 
		$comments_arr, 
		null, 
		null, 
		new DateTime());

		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('find')->willReturn($task);

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$this->task_management_service->getTaskMostRecentSelfOwnedComment(1);

 		} catch(ExceptionCollection $e){

 			$this->throwFromExceptionCollection($e, TaskNotFoundException::class);
 		}
 	}

 	public function test_If_getTaskMostRecentEditedSelfOwnedComment_Returns_Comment_View_Model(){

 		$comments_arr = array(new Comment(new CommentId(1), new PersonnelId(1), 'this is the comment', new DateTime(), new DateTime()));

		$task = new Task( 

		new TaskId(1),
		'CHANGED TASK-TEST TITLE', 
		new PersonnelId(1),   	
		null, 
		'BTC',
		null, 
		null, 
		null, 
		null, 
		null,
		null, 
		null, 
		$comments_arr, 
		null, 
		null, 
		new DateTime());

		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('find')->willReturn($task);

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$stored_comment_vm = $this->task_management_service->getTaskMostRecentEditedSelfOwnedComment(1);
		$this->assertNotEmpty($stored_comment_vm);
 	}

 	public function test_If_test_If_getTaskMostRecentEditedSelfOwnedComment_Throws_Exception_When_Personnel_Has_No_Access(){

 		$this->expectException(TaskNotFoundException::class);
 		try{

 		$task = new Task( 

		new TaskId(1),
		'CHANGED TASK-TEST TITLE', 
		null,  	
		null, 
		'BTC',
		null, 
		null, 
		null, 
		null, 
		null,
		null, 
		null, 
		null, 
		null, 
		null, 
		new DateTime());

		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('find')->willReturn($task);

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$stored_comment_vm = $this->task_management_service->getTaskMostRecentEditedSelfOwnedComment(1);

 		} catch(ExceptionCollection $e){

 			$this->throwFromExceptionCollection($e, TaskNotFoundException::class);
 		}
 	}

 	public function test_If_getSelfOwnedSubtaskComments_Returns_The_Comment_View_Model_Array(){

 		$comments_arr = array(new Comment(new CommentId(1), new PersonnelId(1), 'this is the comment', new DateTime(), new DateTime()));

 		$subtask_arr = array(
			new Subtask(new SubtaskId(1), null, 'title1', null,null,null,null,null,null, null,null,$comments_arr,null,null, new DateTime('now')));

 		$task = new Task( 

		new TaskId(1),
		'CHANGED TASK-TEST TITLE', 
		new PersonnelId(1),  	
		null, 
		'BTC',
		null, 
		null, 
		null, 
		$subtask_arr, 
		null,
		null, 
		null, 
		null, 
		null, 
		null, 
		new DateTime());

		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('findBySubtask')->willReturn($task);

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$stored_comment_vm = $this->task_management_service->getSelfOwnedSubtaskComments(1);
		$this->assertIsArray($stored_comment_vm);
		$this->assertNotEmpty($stored_comment_vm);

 	}

 	public function test_If_getTaskMostRecentEditedSelfOwnedComment_Throws_Exception_When_Personnel_Has_No_Access() {

 		$this->expectException(TaskNotFoundException::class);

 		try{

 		$comments_arr = array(new Comment(new CommentId(1), new PersonnelId(1), 'this is the comment', new DateTime(), new DateTime()));

 		$subtask_arr = array(
			new Subtask(new SubtaskId(1), null, 'title1', null,null,null,null,null,null, null,null,$comments_arr,null,null, new DateTime('now')));

 		$task = new Task( 
		new TaskId(1),
		'CHANGED TASK-TEST TITLE', 
		null,
		null, 
		'BTC',
		null, 
		null, 
		null, 
		$subtask_arr, 
		null,
		null, 
		null, 
		null, 
		null, 
		null, 
		new DateTime());

		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('findBySubtask')->willReturn($task);

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$stored_comment_vm = $this->task_management_service->getSelfOwnedSubtaskComments(1);

 		} catch(ExceptionCollection $w){

 			$this->throwFromExceptionCollection($w, TaskNotFoundException::class);
 		}
 	}


 	public function test_If_getSelfOwnedSubtaskCommentCommentator_Returns_The_Number_Of_Commentators(){

 		$comments_arr = array(new Comment(new CommentId(1), new PersonnelId(1), 'this is the comment', new DateTime(), new DateTime()));

 		$subtask_arr = array(
			new Subtask(new SubtaskId(1), null, 'title1', null,null,null,null,null,null, null,null,$comments_arr,null,null, new DateTime('now')));

 		$task = new Task( 
		new TaskId(1),
		'CHANGED TASK-TEST TITLE', 
		new PersonnelId(1),
		null, 
		'BTC',
		null, 
		null, 
		null, 
		$subtask_arr, 
		null,
		null, 
		null, 
		null, 
		null, 
		null, 
		new DateTime());

		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('findBySubtask')->willReturn($task);

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$number_of_commentators = $this->task_management_service->getSelfOwnedSubtaskCommentCommentator(1,1,1);
		$this->assertEquals(1, $number_of_commentators);
 	}

 	public function test_If_getSelfOwnedSubtaskCommentCommentator_Throws_Exception_When_Personnel_Has_No_Access(){

 		$this->expectException(TaskNotFoundException::class);

 		try{

 		$comments_arr = array(new Comment(new CommentId(1), new PersonnelId(1), 'this is the comment', new DateTime(), new DateTime()));

 		$subtask_arr = array(
			new Subtask(new SubtaskId(1), null, 'title1', null,null,null,null,null,null, null,null,$comments_arr,null,null, new DateTime('now')));

 		$task = new Task( 
		new TaskId(1),
		'CHANGED TASK-TEST TITLE', 
		null,
		null, 
		'BTC',
		null, 
		null, 
		null, 
		$subtask_arr, 
		null,
		null, 
		null, 
		null, 
		null, 
		null, 
		new DateTime());

		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('findBySubtask')->willReturn($task);

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$number_of_commentators = $this->task_management_service->getSelfOwnedSubtaskCommentCommentator(1,1,1);

 		} catch(ExceptionCollection $e) {

 			$this->throwFromExceptionCollection($e, TaskNotFoundException::class);
 		}
 	}

 	public function test_If_getSelfOwnedSubtaskCommentCommentator_Throws_Exception_When_Comment_Isnt_Found(){

 		$this->expectException(CommentNotFoundException::class);

 		try{

 		// comment array given null on subtask_arr, this will trigger the exception

 		$subtask_arr = array(
			new Subtask(new SubtaskId(1), null, 'title1', null,null,null,null,null,null, null,null,null,null,null, new DateTime('now')));

 		$task = new Task( 
		new TaskId(1),
		'CHANGED TASK-TEST TITLE', 
		new PersonnelId(1),
		null, 
		'BTC',
		null, 
		null, 
		null, 
		$subtask_arr, 
		null,
		null, 
		null, 
		null, 
		null, 
		null, 
		new DateTime());

		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('findBySubtask')->willReturn($task);

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$number_of_commentators = $this->task_management_service->getSelfOwnedSubtaskCommentCommentator(1,1,1);

 		} catch(ExceptionCollection $w) {

 			$this->throwFromExceptionCollection($w, CommentNotFoundException::class);
 		}
 	}


 	public function test_If_getSubtaskMostRecentSelfOwnedComment_Returns_Comment_View_Model(){

 		$comments_arr = array(new Comment(new CommentId(1), new PersonnelId(1), 'this is the comment', new DateTime(), new DateTime()));

 		$subtask_arr = array(
			new Subtask(new SubtaskId(1), null, 'title1', null,null,null,null,null,null, null,null,$comments_arr,null,null, new DateTime('now')));

 		$task = new Task( 

		new TaskId(1),
		'CHANGED TASK-TEST TITLE', 
		new PersonnelId(1),
		null, 
		'BTC',
		null, 
		null, 
		null, 
		$subtask_arr, 
		null,
		null, 
		null, 
		null, 
		null, 
		null, 
		new DateTime());

		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('findBySubtask')->willReturn($task);

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$stored_comment_vm = $this->task_management_service->getSubtaskMostRecentSelfOwnedComment(1,1);
		$this->assertNotEmpty($stored_comment_vm);
 	}


 	public function test_If_getSubtaskMostRecentSelfOwnedComment_Throws_Exception_When_Personnel_Has_No_Access(){

 		$this->expectException(TaskNotFoundException::class);

 		try{

 		$comments_arr = array(new Comment(new CommentId(1), new PersonnelId(1), 'this is the comment', new DateTime(), new DateTime()));

 		$subtask_arr = array(
			new Subtask(new SubtaskId(1), null, 'title1', null,null,null,null,null,null, null,null,$comments_arr,null,null, new DateTime('now')));

 		$task = new Task( 

		new TaskId(1),
		'CHANGED TASK-TEST TITLE', 
		null,
		null, 
		'BTC',
		null, 
		null, 
		null, 
		$subtask_arr, 
		null,
		null, 
		null, 
		null, 
		null, 
		null, 
		new DateTime());

		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('findBySubtask')->willReturn($task);

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$this->task_management_service->getSubtaskMostRecentSelfOwnedComment(1,1);

 		} catch(ExceptionCollection $w){

 			$this->throwFromExceptionCollection($w, TaskNotFoundException::class);
 		}
 	}


 	public function test_If_getSubtaskMostRecentEditedSelfOwnedComment_Returns_Comment_View_Model(){

 		$comments_arr = array(new Comment(new CommentId(1), new PersonnelId(1), 'this is the comment', new DateTime(), new DateTime()));

 		$subtask_arr = array(
			new Subtask(new SubtaskId(1), null, 'title1', null,null,null,null,null,null, null,null,$comments_arr,null,null, new DateTime('now')));

 		$task = new Task( 

		new TaskId(1),
		'CHANGED TASK-TEST TITLE', 
		new PersonnelId(1),
		null, 
		'BTC',
		null, 
		null, 
		null, 
		$subtask_arr, 
		null,
		null, 
		null, 
		null, 
		null, 
		null, 
		new DateTime());

		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('findBySubtask')->willReturn($task);

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$stored_comment_vm = $this->task_management_service->getSubtaskMostRecentEditedSelfOwnedComment(1,1);
		$this->assertNotEmpty($stored_comment_vm);	
 	}


 	public function test_If_getSubtaskMostRecentEditedSelfOwnedComment_Throws_Exception_When_Personnel_Has_No_Access(){

 		$this->expectException(TaskNotFoundException::class);

 		try{

 		$comments_arr = array(new Comment(new CommentId(1), new PersonnelId(1), 'this is the comment', new DateTime(), new DateTime()));

 		$subtask_arr = array(
			new Subtask(new SubtaskId(1), null, 'title1', null,null,null,null,null,null, null,null,$comments_arr,null,null, new DateTime('now')));

 		$task = new Task( 

		new TaskId(1),
		'CHANGED TASK-TEST TITLE', 
		null,
		null, 
		'BTC',
		null, 
		null, 
		null, 
		$subtask_arr, 
		null,
		null, 
		null, 
		null, 
		null, 
		null, 
		new DateTime());

		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('findBySubtask')->willReturn($task);

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$stored_comment_vm = $this->task_management_service->getSubtaskMostRecentEditedSelfOwnedComment(1,1);

 		} catch(ExceptionCollection $w){

 			$this->throwFromExceptionCollection($w, TaskNotFoundException::class);
 		}
 	}


 	public function test_If_getSelfOwnedTaskEvents_Returns_Events_View_Model_Array(){

 		$event_arr = array(new Event(
 			new EventId(1) , new PersonnelId(1), EventType::StatusChanged() , null, new DateTime())
 		);

 		$task = new Task( 

		new TaskId(1),
		'CHANGED TASK-TEST TITLE', 
		new PersonnelId(1),
		null, 
		'BTC',
		null, 
		null, 
		null, 
		null, 
		null,
		null, 
		null, 
		null, 
		$event_arr, 
		null, 
		new DateTime());

		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('find')->willReturn($task);

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$stored_event_vm = $this->task_management_service->getSelfOwnedTaskEvents(1);
		$this->assertIsArray($stored_event_vm);
		$this->assertNotEmpty($stored_event_vm);
 	}


 	public function test_If_getSelfOwnedTaskEventEnabler_Returns_The_Number_Of_Event_Enablers(){


 		$event_arr = array(new Event(
 			new EventId(1) , new PersonnelId(1), EventType::StatusChanged() , null, new DateTime())
 		);

 		$task = new Task( 

		new TaskId(1),
		'CHANGED TASK-TEST TITLE', 
		new PersonnelId(1),
		null, 
		'BTC',
		null, 
		null, 
		null, 
		null, 
		null,
		null, 
		null, 
		null, 
		$event_arr, 
		null, 
		new DateTime());

		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('find')->willReturn($task);

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$number_of_event_enablers = $this->task_management_service->getSelfOwnedTaskEventEnabler(1,1);
		$this->assertEquals(1, $number_of_event_enablers);

 	}


 	public function test_If_getSelfOwnedTaskEventEnabler_Throws_Exception_When_Personnel_Has_No_Access(){

 		$this->expectException(TaskNotFoundException::class);

 		try{

 		$event_arr = array(new Event(
 			new EventId(1) , new PersonnelId(1), EventType::StatusChanged() , null, new DateTime())
 		);

 		$task = new Task( 

		new TaskId(1),
		'CHANGED TASK-TEST TITLE', 
		null,
		null, 
		'BTC',
		null, 
		null, 
		null, 
		null, 
		null,
		null, 
		null, 
		null, 
		$event_arr, 
		null, 
		new DateTime());

		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('find')->willReturn($task);

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$this->task_management_service->getSelfOwnedTaskEventEnabler(1,1);

 		} catch(ExceptionCollection $e) {

 			$this->throwFromExceptionCollection($e, TaskNotFoundException::class);
 		}
 	}


 	public function test_If_getSelfOwnedSubtaskEvents_Returns_Events_View_Model_Array(){

 		$event_arr = array(new Event(
 			new EventId(1) , new PersonnelId(1), EventType::StatusChanged() , null, new DateTime())
 		);

 		$subtask_arr = array(
			new Subtask(new SubtaskId(1), null, 'title1', null,null,null,null,null,null, null,null,null,$event_arr, null, new DateTime('now')));

 		$task = new Task( 

		new TaskId(1),
		'CHANGED TASK-TEST TITLE', 
		new PersonnelId(1),
		null, 
		'BTC',
		null, 
		null, 
		null, 
		$subtask_arr, 
		null,
		null, 
		null, 
		null, 
		null, 
		null, 
		new DateTime());

		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('findBySubtask')->willReturn($task);

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$stored_event_vm = $this->task_management_service->getSelfOwnedSubtaskEvents(1);
		$this->assertIsArray($stored_event_vm);
		$this->assertNotEmpty($stored_event_vm);
 	}


 	public function test_If_getSelfOwnedSubtaskEvents_Throws_Exception_When_Personnel_Has_No_Access(){

 		$this->expectException(TaskNotFoundException::class);

 		try{

 		$event_arr = array(new Event(
 			new EventId(1) , new PersonnelId(1), EventType::StatusChanged() , null, new DateTime())
 		);

 		$subtask_arr = array(
			new Subtask(new SubtaskId(1), null, 'title1', null,null,null,null,null,null, null,null,null,$event_arr, null, new DateTime('now')));

 		$task = new Task( 

		new TaskId(1),
		'CHANGED TASK-TEST TITLE', 
		null,
		null, 
		'BTC',
		null, 
		null, 
		null, 
		$subtask_arr, 
		null,
		null, 
		null, 
		null, 
		null, 
		null, 
		new DateTime());

		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('findBySubtask')->willReturn($task);

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$this->task_management_service->getSelfOwnedSubtaskEvents(1);

 		} catch(ExceptionCollection $e) {

 			$this->throwFromExceptionCollection($e, TaskNotFoundException::class);
 		}
 	}


 	public function test_If_getSelfOwnedSubtaskEventEnabler_Returns_The_Number_Of_Subtask_Event_Enablers(){

 		$event_arr = array(new Event(
 			new EventId(1) , new PersonnelId(1), EventType::StatusChanged() , null, new DateTime())
 		);

 		$subtask_arr = array(
			new Subtask(new SubtaskId(1), null, 'title1', null,null,null,null,null,null, null,null,null,$event_arr, null, new DateTime('now')));

 		$task = new Task( 

		new TaskId(1),
		'CHANGED TASK-TEST TITLE', 
		new PersonnelId(1),
		null, 
		'BTC',
		null, 
		null, 
		null, 
		$subtask_arr, 
		null,
		null, 
		null, 
		null, 
		null, 
		null, 
		new DateTime());

		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('findBySubtask')->willReturn($task);

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$number_of_event_enablers = $this->task_management_service->getSelfOwnedSubtaskEventEnabler(1,1,1);
		$this->assertEquals(1, $number_of_event_enablers);
 	}

 	public function test_If_getSelfOwnedSubtaskEventEnabler_Throws_Exception_When_Personnel_Has_No_Access(){

 		$this->expectException(TaskNotFoundException::class);

		try{

		$event_arr = array(new Event(
 			new EventId(1) , new PersonnelId(1), EventType::StatusChanged() , null, new DateTime())
 		);

 		$subtask_arr = array(
			new Subtask(new SubtaskId(1), null, 'title1', null,null,null,null,null,null, null,null,null,$event_arr, null, new DateTime('now')));

 		$task = new Task( 

		new TaskId(1),
		'CHANGED TASK-TEST TITLE', 
		null,
		null, 
		'BTC',
		null, 
		null, 
		null, 
		$subtask_arr, 
		null,
		null, 
		null, 
		null, 
		null, 
		null, 
		new DateTime());

		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('findBySubtask')->willReturn($task);

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$this->task_management_service->getSelfOwnedSubtaskEventEnabler(1,1,1);

		} catch(ExceptionCollection $e){

			$this->throwFromExceptionCollection($e, TaskNotFoundException::class);
		}

 		
 	}

 	public function test_If_getSelfOwnedTaskAttachments_Returns_Attachment_View_Model_Array(){

 		
 		$attachment_arr = array(new Attachment(
 			new AttachmentId(1), new PersonnelId(1), 'name', 'base64', null)
 		);

 		$task = new Task( 
		new TaskId(1),
		'CHANGED TASK-TEST TITLE', 
		new PersonnelId(1),
		null, 
		'BTC',
		null, 
		null, 
		null, 
		null, 
		null,
		null, 
		null, 
		null, 
		null, 
		$attachment_arr, 
		new DateTime());

		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('find')->willReturn($task);

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$stored_attachment_vm = $this->task_management_service->getSelfOwnedTaskAttachments(1);
		$this->assertIsArray($stored_attachment_vm);
		$this->assertNotEmpty($stored_attachment_vm);
 	}

 	public function test_If_getSelfOwnedTaskAttachments_Throws_Exception_When_Personnel_Has_No_Access(){

 		$this->expectException(TaskNotFoundException::class);

 		try{

 		$attachment_arr = array(new Attachment(
 			new AttachmentId(1), new PersonnelId(1), 'name', 'base64', null)
 		);

 		$task = new Task( 
		new TaskId(1),
		'CHANGED TASK-TEST TITLE', 
		null,
		null, 
		'BTC',
		null, 
		null, 
		null, 
		null, 
		null,
		null, 
		null, 
		null, 
		null, 
		$attachment_arr, 
		new DateTime());

		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('find')->willReturn($task);

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$this->task_management_service->getSelfOwnedTaskAttachments(1);

 		} catch(ExceptionCollection $e) {

 			$this->throwFromExceptionCollection($e, TaskNotFoundException::class);
 		}

 	}

 	public function test_If_getSelfOwnedTaskAttachmentUploader_Returns_The_Number_Of_Attachments(){

 		$attachment_arr = array(new Attachment(
 			new AttachmentId(1), new PersonnelId(1), 'name', 'base64', null)
 		);

 		$task = new Task( 
		new TaskId(1),
		'CHANGED TASK-TEST TITLE', 
		new PersonnelId(1),
		null, 
		'BTC',
		null, 
		null, 
		null, 
		null, 
		null,
		null, 
		null, 
		null, 
		null, 
		$attachment_arr, 
		new DateTime());

		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('find')->willReturn($task);

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$number_of_uploaders = $this->task_management_service->getSelfOwnedTaskAttachmentUploader(1,1);

		$this->assertEquals(1, $number_of_uploaders);

 	}


 	public function test_If_getSelfOwnedTaskAttachmentUploader_Throws_Exception_When_Personnel_Has_No_Access(){

 		$this->expectException(TaskNotFoundException::class);

 		try{

 		$attachment_arr = array(new Attachment(
 			new AttachmentId(1), new PersonnelId(1), 'name', 'base64', null)
 		);

 		$task = new Task( 
		new TaskId(1),
		'CHANGED TASK-TEST TITLE', 
		null,
		null, 
		'BTC',
		null, 
		null, 
		null, 
		null, 
		null,
		null, 
		null, 
		null, 
		null, 
		$attachment_arr, 
		new DateTime());

		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('find')->willReturn($task);

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$this->task_management_service->getSelfOwnedTaskAttachmentUploader(1,1);

 		} catch(ExceptionCollection $e){

 			$this->throwFromExceptionCollection($e, TaskNotFoundException::class);
 		}
 	}


 	public function test_If_getTaskMostRecentSelfOwnedAttachment_Returns_Attachment_View_Model(){


 		$attachment_arr = array(new Attachment(new AttachmentId(1), new PersonnelId(1), 'name', 'base64', null));

 		$task = new Task( 
		new TaskId(1),
		'CHANGED TASK-TEST TITLE', 
		new PersonnelId(1),
		null, 
		'BTC',
		null, 
		null, 
		null, 
		null, 
		null,
		null, 
		null, 
		null, 
		null, 
		$attachment_arr, 
		new DateTime());

		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('find')->willReturn($task);

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$stored_attachment_vm = $this->task_management_service->getTaskMostRecentSelfOwnedAttachment(1);
		$this->assertNotEmpty($stored_attachment_vm);
 	}


 	public function test_If_getTaskMostRecentSelfOwnedAttachment_Throws_Exception_When_Personnel_Has_No_Access(){

 		$this->expectException(TaskNotFoundException::class);

 		try{

 		$attachment_arr = array(new Attachment(new AttachmentId(1), new PersonnelId(1), 'name', 'base64', null));

 		$task = new Task( 
		new TaskId(1),
		'CHANGED TASK-TEST TITLE', 
		null,
		null, 
		'BTC',
		null, 
		null, 
		null, 
		null, 
		null,
		null, 
		null, 
		null, 
		null, 
		$attachment_arr, 
		new DateTime());

		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('find')->willReturn($task);

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$this->task_management_service->getTaskMostRecentSelfOwnedAttachment(1);

 		} catch(ExceptionCollection $e){

 			$this->throwFromExceptionCollection($e, TaskNotFoundException::class);
 		}
 	}

 	public function test_If_getSelfOwnedSubtaskAttachments_Returns_Attachment_View_Model_Array(){


 		$attachment_arr = array(new Attachment(new AttachmentId(1), new PersonnelId(1), 'name', 'base64', null));

		$subtask_arr = array(
			new Subtask(new SubtaskId(1), null, 'title1', null,null,null,null,null,null, null,null,null,null,$attachment_arr, new DateTime('now')));

 		$task = new Task( 
		new TaskId(1),
		'CHANGED TASK-TEST TITLE', 
		new PersonnelId(1),
		null, 
		'BTC',
		null, 
		null, 
		null, 
		$subtask_arr, 
		null,
		null, 
		null, 
		null, 
		null, 
		null, 
		new DateTime());

		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('findBySubtask')->willReturn($task);

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$attachment_vm_arr = $this->task_management_service->getSelfOwnedSubtaskAttachments(1);
		$this->assertIsArray($attachment_vm_arr);
		$this->assertNotEmpty($attachment_vm_arr);
 	}
 		

 	public function test_If_getSelfOwnedSubtaskAttachments_Throws_Exception_When_Personnel_Has_No_Access(){

 		$this->expectException(TaskNotFoundException::class);

 		try{

 		$attachment_arr = array(new Attachment(new AttachmentId(1), new PersonnelId(1), 'name', 'base64', null));

		$subtask_arr = array(
			new Subtask(new SubtaskId(1), null, 'title1', null,null,null,null,null,null, null,null,null,null,$attachment_arr, new DateTime('now')));

 		$task = new Task( 
		new TaskId(1),
		'CHANGED TASK-TEST TITLE', 
		null,
		null, 
		'BTC',
		null, 
		null, 
		null, 
		$subtask_arr, 
		null,
		null, 
		null, 
		null, 
		null, 
		null, 
		new DateTime());

		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('findBySubtask')->willReturn($task);

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$this->task_management_service->getSelfOwnedSubtaskAttachments(1);

 		} catch(ExceptionCollection $e) {

 			$this->throwFromExceptionCollection($e, TaskNotFoundException::class);
 		}
 	}


 	public function test_If_getSelfOwnedSubtaskAttachmentUploader_Returns_The_Number_Of_Attachments(){

 		$attachment_arr = array(new Attachment(new AttachmentId(1), new PersonnelId(1), 'name', 'base64', null));

		$subtask_arr = array(
			new Subtask(new SubtaskId(1), null, 'title1', null,null,null,null,null,null, null,null,null,null,$attachment_arr, new DateTime('now')));

 		$task = new Task( 
		new TaskId(1),
		'CHANGED TASK-TEST TITLE', 
		new PersonnelId(1),
		null, 
		'BTC',
		null, 
		null, 
		null, 
		$subtask_arr, 
		null,
		null, 
		null, 
		null, 
		null, 
		null, 
		new DateTime());

		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('findBySubtask')->willReturn($task);

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$number_of_attachments = $this->task_management_service->getSelfOwnedSubtaskAttachmentUploader(1,1,1);
		$this->assertEquals(1, $number_of_attachments);

 	}

 	public function test_If_getSelfOwnedSubtaskAttachmentUploader_Throws_Exception_When_Personnel_Has_No_Access(){

 		$this->expectException(TaskNotFoundException::class);

 		try{

 		$attachment_arr = array(new Attachment(new AttachmentId(1), new PersonnelId(1), 'name', 'base64', null));

		$subtask_arr = array(
			new Subtask(new SubtaskId(1), null, 'title1', null,null,null,null,null,null, null,null,null,null,$attachment_arr, new DateTime('now')));

 		$task = new Task( 
		new TaskId(1),
		'CHANGED TASK-TEST TITLE', 
		null,
		null, 
		'BTC',
		null, 
		null, 
		null, 
		$subtask_arr, 
		null,
		null, 
		null, 
		null, 
		null, 
		null, 
		new DateTime());

		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('findBySubtask')->willReturn($task);

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$this->task_management_service->getSelfOwnedSubtaskAttachmentUploader(1,1,1);

 		}catch(ExceptionCollection $w) {

 			$this->throwFromExceptionCollection($w, TaskNotFoundException::class);
 		}
 	}

 	public function test_If_getSubtaskMostRecentSelfOwnedAttachment_Attachment_View_Model(){

 		$attachment_arr = array(new Attachment(new AttachmentId(1), new PersonnelId(1), 'name', 'base64', null));

		$subtask_arr = array(
			new Subtask(new SubtaskId(1), null, 'title1', null,null,null,null,null,null, null,null,null,null,$attachment_arr, new DateTime('now')));

 		$task = new Task( 
		new TaskId(1),
		'CHANGED TASK-TEST TITLE', 
		new PersonnelId(1),
		null, 
		'BTC',
		null, 
		null, 
		null, 
		$subtask_arr, 
		null,
		null, 
		null, 
		null, 
		null, 
		null, 
		new DateTime());

		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('findBySubtask')->willReturn($task);

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$stored_attachment_vm = $this->task_management_service->getSubtaskMostRecentSelfOwnedAttachment(1,1);
		$this->assertNotEmpty($stored_attachment_vm);
 	}


 	public function test_If_getSubtaskMostRecentSelfOwnedAttachment_Throws_Exception_When_Personnel_Has_No_Access(){

 		$this->expectException(TaskNotFoundException::class);

 		try{

 		$attachment_arr = array(new Attachment(new AttachmentId(1), new PersonnelId(1), 'name', 'base64', null));

		$subtask_arr = array(
			new Subtask(new SubtaskId(1), null, 'title1', null,null,null,null,null,null, null,null,null,null,$attachment_arr, new DateTime('now')));

 		$task = new Task( 
		new TaskId(1),
		'CHANGED TASK-TEST TITLE', 
		null,
		null, 
		'BTC',
		null, 
		null, 
		null, 
		$subtask_arr, 
		null,
		null, 
		null, 
		null, 
		null, 
		null, 
		new DateTime());

		$task_repository = $this->createMock(ITaskRepository::class);
		$task_repository->method('findBySubtask')->willReturn($task);

		$identity_provider_mock = $this->createMock(IIdentityProvider::class);
		$identity_provider_mock->method('identity')->willReturn(1);  

		$personnel_validator = $this->createMock(IPersonnelValidator::class);
		$personnel_validator->method('personnelWithIdExists')->willReturn(true);

		$personnel_access_resolver = $this->createMock(IPersonnelAccessResolver::class);
		$personnel_access_resolver->method('canAccess')->willReturn(true);

		$file_access_provider = $this->createMock(IFileDirectAccessLinkProvider::class); 
		$file_access_provider->method('getLink')->willReturn('this is a link string');

		$this->task_management_service = new TaskManagementService(
		$task_repository, $identity_provider_mock, $personnel_validator, $personnel_access_resolver, $file_access_provider);

		$this->task_management_service->getSubtaskMostRecentSelfOwnedAttachment(1,1);

 		} catch(ExceptionCollection $w) {

 			$this->throwFromExceptionCollection($w, TaskNotFoundException::class);
 		}

 	}

	private function throwFromExceptionCollection($exception_collection, $exception) {
			foreach($exception_collection->getExceptions() as $e) {
				
				if(get_class($e) == PersonnelNotReachableException::class) {
					throw new PersonnelNotReachableException(1,1);
				}
			
				else if(get_class($e) == $exception) {
					throw new $exception;
			}
		}
	}

}

?>

