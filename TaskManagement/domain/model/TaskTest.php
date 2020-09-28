<?php

use \model\TaskManagement\domain\model\TaskId;
use \model\TaskManagement\domain\model\Task;
use \model\TaskManagement\domain\model\Subtask;
use \model\TaskManagement\domain\model\Comment;
use \model\TaskManagement\domain\model\Attachment;
use \model\TaskManagement\domain\model\PersonnelId;
use \model\TaskManagement\domain\model\SubtaskId;
use \model\TaskManagement\domain\model\AttachmentId;
use \model\TaskManagement\domain\model\CommentId;
use \model\TaskManagement\domain\model\exception\TaskInsufficentPrivilegeForActionException;
use \model\TaskManagement\domain\model\exception\TaskCreateSubtaskPrivilegeException;
use \model\TaskManagement\domain\model\exception\TaskRemoveSubtaskPrivilegeException;
use \model\TaskManagement\domain\model\exception\SubtaskInsufficentPrivilegeForActionException;
use \model\TaskManagement\domain\model\exception\SubtaskCommentPrivilegeException;
use \model\TaskManagement\domain\model\exception\SubtaskAttachmentPrivilegeException;
use \model\TaskManagement\domain\model\exception\AttachmentEditPrivilegeException;
use \model\TaskManagement\domain\model\EventType;
use \model\TaskManagement\domain\model\Location;
use \model\TaskManagement\domain\model\TaskPriority;
use \model\TaskManagement\domain\model\TaskStatus;
use \model\common\ExceptionCollection;

use PHPUnit\Framework\TestCase;


class TaskTest extends TestCase {

	private function validTaskWithId($id, $assignee = null, $location = null, $subtasks = null){
		return new Task(

			new TaskId($id), 				/* id */
			'title', 						/* title */
			new PersonnelId(1), 			/* assigner id */
			$assignee, 						/* assignee[] */
			'description',					/* description */
			null, 							/* start date */
			null, 							/* due date */
			null, 							/* location */
			$subtasks, 						/* subtasks[] */
			null,							/* priority */
			null, 							/* status */
			null,							/* triggers[] */ 
			null, 							/* comments[] */
			null, 							/* events[] */
			null, 							/* attachments[] */
			null, 							/* created on */
			null 							/* edited on */
		);
	}

		private function validSubtaskWithId($id, $assignee = null, $location = null, $comments = null, $attachments = null){
		return new Subtask(
			new SubtaskId($id), 			/* id */
			new TaskId(1), 					/* task id */
			'Subtask Title',				/* title */
			new PersonnelId(1),				/* assigner */
			$assignee,						/* assignee[] */
			'Subtask Description',			/* description */ 
			new DateTime(), 				/* start date */
			new DateTime(), 				/* due date */
			$location, 						/* location */
			null, 							/* priority */
			null, 							/* status */
			$comments, 						/* comments[] */
			null, 							/* events[] */
			$attachments, 					/* attachments[] */
			new DateTime()					/* created on[] */
		);
	}


	public function test_IsAssigner_Returns_True_If_PersonnelId_Matches() {

		$task = $this->validTaskWithId(1);

		$check_can_assign = $task->isAssigner(new PersonnelId(1));
		$this->assertTrue($check_can_assign);

	}

	public function test_IsRemovableBy_Returns_True_When_PersonnelId_Matches() {

		$task = $this->validTaskWithId(1);

		$confirm_returns_true = $task->isRemovableBy(new PersonnelId(1));
		$this->assertTrue($confirm_returns_true);

	}

	public function test_If_IsRemovableBy_Returns_False_When_Assigner_Is_Null(){

		$task = new Task(

			new TaskId(1),
			'title', 
			null, 		 // assigner id null given, will return null.	
			null, 					
			'description',					
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
			null, 						
			null 					
		);

		$confirm_returns_false = $task->isRemovableBy(new PersonnelId(1));

		$this->assertFalse($confirm_returns_false);
	}


	public function test_If_ChangeTitle_Can_Change_The_Given_Title() {
		
		$task = $this->validTaskWithId(1);

		$task->changeTitle('This title has changed!!!', new PersonnelId(1));

		$confirm_changes = $task->title();
		$this->assertEquals('This title has changed!!!', $confirm_changes);

	}

	public function testIf_Throws_Exception_When_Title_UpdaterId_Doesnt_Match() {

		$this->expectException(TaskInsufficentPrivilegeForActionException::class);

		$task = $this->validTaskWithId(1);

		$task->changeTitle('new title will fail', new PersonnelId(2)); // unmatching updater id

		$exception_collection = new ExceptionCollection($task->exceptions());

		$this->throwFromExceptionCollection($exception_collection, TaskInsufficentPrivilegeForActionException::class);
	
	}

	public function test_If_Assigner_Can_Assign_A_Task_To_An_Assignee(){

		$task = $this->validTaskWithId(1);

		$task->assignTo(new PersonnelId(1), new PersonnelId(2));

		$confirm = $task->isAssignee(new PersonnelId(2));
		$this->assertTrue($confirm);

	}

	public function test_If_AssignTo_Fails_When_Wrong_AssignerId_Given() {

		$this->expectException(TaskInsufficentPrivilegeForActionException::class);

		$task = $this->validTaskWithId(1);

		$task->assignTo(new PersonnelId(2), new PersonnelId(2)); // assigner id doesnt match with the one on constructor

		$exception_collection = new ExceptionCollection($task->exceptions());
		$this->throwFromExceptionCollection($exception_collection, TaskInsufficentPrivilegeForActionException::class); 

	}

	public function test_If_assignTo_Stores_Events() {

		$task = $this->validTaskWithId(1);

		$task->assignTo(new PersonnelId(1), new PersonnelId(2));

		$confirm_event_stored = $task->events();
		$this->assertNotEmpty($confirm_event_stored);
	
	}

	public function test_If_deAssignFrom_Removes_Assignment() {

		$task = $this->validTaskWithId(1);

		$task->assignTo(new PersonnelId(1), new PersonnelId(2));

		$task->deassignFrom(new PersonnelId(1), new PersonnelId(2));

		$confirm_removed = $task->isAssignee(new PersonnelId(2));
		$this->assertFalse($confirm_removed);
	}


	public function test_If_deAssignFrom_Stores_Events() {

		$task = $this->validTaskWithId(1);

		$task->assignTo(new PersonnelId(1), new PersonnelId(2));

		$task->deassignFrom(new PersonnelId(1), new PersonnelId(2));

		$confirm_event_stored = $task->events();
		$this->assertNotEmpty($confirm_event_stored);

	}

	public function test_Throw_Exception_If_deAssignerId_Doesnt_Match(){

		$this->expectException(TaskInsufficentPrivilegeForActionException::class);

		$task = $this->validTaskWithId(1);

		$task->assignTo(new PersonnelId(1), new PersonnelId(2));	

		$task->deassignFrom(new PersonnelId(2), new PersonnelId(2));

	}

	public function test_If_changeDescription_Changes_Given_Description(){


		$task = $this->validTaskWithId(1);

		$task->changeDescription('Desc has changed!' , new PersonnelId(1));

		$confirm_changed_desc = $task->description();

		$this->assertEquals('Desc has changed!', $confirm_changed_desc);
	
	}

	public function test_If_changeDescription_Stores_Events() {

		
		$task = $this->validTaskWithId(1);

		$task->changeDescription('Desc has changed!' , new PersonnelId(1));

		$confirm_event_stored = $task->events();
		$this->assertNotEmpty($confirm_event_stored);

	}


	public function testIf_Throws_Exception_When_Description_UpdaterId_Doesnt_Match() {

		$this->expectException(TaskInsufficentPrivilegeForActionException::class);

		$task = $this->validTaskWithId(1);

		$task->changeDescription('desc fail', new PersonnelId(2));

		$exception_collection = new ExceptionCollection($task->exceptions());

		$this->throwFromExceptionCollection($exception_collection, TaskInsufficentPrivilegeForActionException::class);

	}

	public function test_If_Assigner_Can_Change_The_StartDate(){

		$task = $this->validTaskWithId(1);

		$task->changeStartDate(new DateTime('now'), new PersonnelId(1));
		$confirm_startdate_change = $task->startDate();

		$this->assertTrue((new \DateTime())->getTimestamp() - $confirm_startdate_change->getTimestamp() < 5); 

	}

	public function test_If_changeStartDate_Stores_Events(){

		$task = $this->validTaskWithId(1);

		$task->changeStartDate(new DateTime(), new PersonnelId(1));
		$confirm_event_stored = $task->events();

		$this->assertNotEmpty($confirm_event_stored);

	}

	public function test_If_changeStartDate_Throws_Exception_When_UpdaterIds_Dont_Match(){
		
		$this->expectException(TaskInsufficentPrivilegeForActionException::class);

		$task = $this->validTaskWithId(1);

		$task->changeStartDate(new DateTime(), new PersonnelId(2));
		$exception_collection = new ExceptionCollection($task->exceptions());

		$this->throwFromExceptionCollection($exception_collection, TaskInsufficentPrivilegeForActionException::class);

	}

	public function testIf_Assigner_Can_Change_DueDate() {

		$task = $this->validTaskWithId(1);

		$task->changeDueDate(new \DateTime('now'), new PersonnelId(1));

		$confirm_duedate_change = $task->dueDate();

		$this->assertTrue((new \DateTime())->getTimestamp() - $confirm_duedate_change->getTimestamp() < 5); 

	}


	public function test_If_changeDueDate_Stores_Events() {


		$task = $this->validTaskWithId(1);

		$task->changeDueDate(new \DateTime('now'), new PersonnelId(1));

		$confirm_event_stored = $task->events();
		$this->assertNotEmpty($confirm_event_stored);

	}


	public function testIf_Throws_Exception_When_DueDate_UpdaterId_Doesnt_Match() {


		$this->expectException(TaskInsufficentPrivilegeForActionException::class);

		$task = $this->validTaskWithId(1);

		$task->changeDueDate(new DateTime('now'), new PersonnelId(2));

		$exception_collection = new ExceptionCollection($task->exceptions());
		$this->throwFromExceptionCollection($exception_collection, TaskInsufficentPrivilegeForActionException::class);

	}

	public function test_If_Assigner_Can_Change_Location() {

		$location = new Location('ex-latitude', 'ex-longitude');
		$task = $this->validTaskWithId(1,null,$location);

		$new_location = new Location('new-latitude', 'new-longtitude');

		$task->changeLocation($new_location, new PersonnelId(1));

		$changed_location = $task->location();

		$latitude = $changed_location->latitude();
		$longitude = $changed_location->longitude();

		$this->assertEquals($latitude, 'new-latitude');
		$this->assertEquals($longitude, 'new-longtitude');
	}


	public function test_If_changeLocation_Stores_Events() {

		$location = new Location('ex-latitude', 'ex-longitude');
		$task = $this->validTaskWithId(1,null,$location);

		$task->changeLocation(new Location('new-latitude', 'new-longitude'), new PersonnelId(1));

		$confirm_event_stored = $task->events();
		$this->assertNotEmpty($confirm_event_stored);
	}


	public function testIf_Throws_Exception_When_Location_UpdaterId_Doesnt_Match () {

		$this->expectException(TaskInsufficentPrivilegeForActionException::class);

		$location = new Location('ex-latitude', 'ex-longitude');
		$task = $this->validTaskWithId(1,null,$location);

		$task->changeLocation(new Location('a','b'), new PersonnelId(2));

		$exception_collection = new ExceptionCollection($task->exceptions());

		$this->throwFromExceptionCollection($exception_collection, TaskInsufficentPrivilegeForActionException::class);

	}

	public function test_If_Create_SubTask_Creates_SubTask() {

		$task = $this->validTaskWithId(1);

		$task->createSubtask('subtask-title', new PersonnelId(1), null, null,null, null, null, TaskPriority::Clear(), TaskStatus::Open());

		$confirm_subtask_created = $task->subtasks();
		$this->assertNotEmpty($confirm_subtask_created);


	}

	public function test_If_createSubtask_Stores_Events() {

		$task = $this->validTaskWithId(1);

		$task->createSubtask('subtask-title', new PersonnelId(1), null, null, null, null, null, TaskPriority::Clear(), TaskStatus::Open());

		$confirm_event_stored = $task->events();
		$this->assertNotEmpty($confirm_event_stored);

	}

	public function testIf_Remove_Subtask_Removes_Existing_Subtask() {
		
		$subtask_arr = array($this->validSubtaskWithId(1));

		$task = $this->validTaskWithId(1, null, null, $subtask_arr);

		$task->removeSubtask(new SubtaskId(1), new PersonnelId(1));
		$check_empty = $task->subtasks();

		$this->assertEmpty($check_empty);

	}

	public function test_If_removeSubtask_Stores_Events() {

		$subtask_arr = array($this->validSubtaskWithId(1));

		$task = $this->validTaskWithId(1,null,null, $subtask_arr);

		$task->removeSubtask(new SubtaskId(1), new PersonnelId(1));

		$confirm_event_stored = $task->events();
		$this->assertNotEmpty($confirm_event_stored);
	}


	public function testIf_Throws_Exception_When_RemoverId_Doesnt_Match() {

		
		$this->expectException(TaskRemoveSubtaskPrivilegeException::class);
		
		$subtask_arr = array($this->validSubtaskWithId(1));

		$task = $this->validTaskWithId(1, null, null, $subtask_arr);

		$task->removeSubtask(new SubtaskId(1), new PersonnelId(2));

		$exception_collection = new ExceptionCollection($task->exceptions());
		$this->throwFromExceptionCollection($exception_collection, TaskRemoveSubtaskPrivilegeException::class);
		
	}


	public function testIf_Change_Subtask_Title_Changes_The_Title() {

		 $subtask_arr = array($this->validSubtaskWithId(1));

		$task = $this->validTaskWithId(1,null,null,$subtask_arr);

		$task->changeSubtaskTitle(new SubtaskId(1), 'changed_title', new PersonnelId(1));

		$get_subtask = $task->subtasks(); // get_subtask holds the arrays of subtasks

		$firstarray_of_subtask = $get_subtask[0]->title();  //firstarray_of_subtask holds the first array's title 

		$this->assertEquals('changed_title', $firstarray_of_subtask); 

	}

	public function testIf_Throws_Exception_When_Subtask_Title_UpdaterId_Doesnt_Match() {

		 $this->expectException(SubtaskInsufficentPrivilegeForActionException::class);

		 $subtask_arr = array($this->validSubtaskWithId(1));

		$task = $this->validTaskWithId(1, null,null, $subtask_arr);

		$task->changeSubtaskTitle(new SubtaskId(1), 'changed_title', new PersonnelId(2));

		$exception_collection = new ExceptionCollection($task->exceptions());
		$this->throwFromExceptionCollection($exception_collection, SubtaskInsufficentPrivilegeForActionException::class);
	}

	public function testIf_isAssignerOfASubtask_Returns_True_When_AssignerIds_Match() {

		$subtask_arr = array($this->validSubtaskWithId(1));

		$task = $this->validTaskWithId(1,null,null,$subtask_arr);

		$confirm_assigner_subtask = $task->isAssignerOfASubtask(new PersonnelId(1));
		$this->assertTrue($confirm_assigner_subtask);

	}

	public function testIf_isAssignerOfASubtask_Returns_False_When_AssignerIds_Doesnt_Match() {

		$subtask_arr = array($this->validSubtaskWithId(1));

		$task = $this->validTaskWithId(1, null,null, $subtask_arr);

		$confirm_assigner_subtask = $task->isAssignerOfASubtask(new PersonnelId(2)); //wrong assigner id given
		$this->assertFalse($confirm_assigner_subtask);

	}

	public function testassignSubtaskTo_Assigns_Subtask_To_Assignee() {

		$subtask_arr = array($this->validSubtaskWithId(1));

		$task = $this->validTaskWithId(1, null, null,$subtask_arr);

		$task->assignSubtaskTo(new SubtaskId(1), new PersonnelId(1), new PersonnelId(1));

		$confirm_subtask_assigned = $task->subtasks();
		$this->assertNotEmpty($confirm_subtask_assigned);

	}

	public function testIf_Throws_Exception_When_assignSubtaskTo_UpdaterId_Doesnt_Match() {

		$this->expectException(SubtaskInsufficentPrivilegeForActionException::class);

		$subtask_arr = array($this->validSubtaskWithId(1));

		$task = $this->validTaskWithId(1, null, null,$subtask_arr);

		$task->assignSubtaskTo(new SubtaskId(1), new PersonnelId(1), new PersonnelId(2));

		$exception_collection = new ExceptionCollection($task->exceptions());

		$this->throwFromExceptionCollection($exception_collection, SubtaskInsufficentPrivilegeForActionException::class);


	}

	public function testdeassignSubtaskFrom_Removes_Assigned_Subtask(){


		$subtask_arr = array($this->validSubtaskWithId(1));

		$task = $this->validTaskWithId(1, null, null,$subtask_arr);

		$task->assignSubtaskTo(new SubtaskId(1), new PersonnelId(1), new PersonnelId(1));

		$task->deassignSubtaskFrom(new SubtaskId(1), new PersonnelId(1), new PersonnelId(1));

		$confirm_subtask_removed = $task->isAssignee(new PersonnelId(1));
		$this->assertFalse($confirm_subtask_removed);
	} 


	public function testdeassignSubtask_Throws_Exception_When_deAssigner_Has_No_Access() {

		$this->expectException(SubtaskInsufficentPrivilegeForActionException::class);

		$subtask_arr = array($this->validSubtaskWithId(1));

		$task = $this->validTaskWithId(1, null,null, $subtask_arr);

		$task->assignSubtaskTo(new SubtaskId(1), new PersonnelId(1), new PersonnelId(1));

		$task->deassignSubtaskFrom(new SubtaskId(1), new PersonnelId(1), new PersonnelId(2));

		$exception_collection = new ExceptionCollection($task->exceptions());
		$this->throwFromExceptionCollection($exception_collection, SubtaskInsufficentPrivilegeForActionException::class);

	}

	public function test_If_isAssigneeOfASubtask_Returns_True_When_AssigneeIds_Match(){

		$assignee_arr = array(
			new PersonnelId(2)
		);
		$subtask_arr = array($this->validSubtaskWithId(1,$assignee_arr));

		$task = $this->validTaskWithId(1, null,null, $subtask_arr);

		$confirm_assignee = $task->isAssigneeOfASubtask(new PersonnelId(2));
		$this->assertTrue($confirm_assignee);
	
	}


	public function test_If_changeSubtaskDescription_Changes_The_Description() {

		$subtask_arr = array($this->validSubtaskWithId(1));

		$subtask_arr[0]->isAssigner(new PersonnelId(1));

		$task = $this->validTaskWithId(1,null, null,$subtask_arr); 

		$task->changeSubtaskDescription(new SubtaskId(1) , 'this is the new description!' , new PersonnelId(1));
		$get_subtask = $task->subtasks();
		$desc_of_subtask = $get_subtask[0]->description();

		$this->assertEquals('this is the new description!', $desc_of_subtask);

	}

	public function testIf_Throws_Exception_When_changeSubtaskDescription_UpdaterId_Doesnt_Match() {


		$this->expectException(SubtaskInsufficentPrivilegeForActionException::class);

		$subtask_arr = array($this->validSubtaskWithId(1));

		$task = $this->validTaskWithId(1,null, null,$subtask_arr); 

		$task->changeSubtaskDescription(new SubtaskId(1) , 'this is the new description!' , new PersonnelId(3));

		$exception_collection = new ExceptionCollection($task->exceptions());
		$this->throwFromExceptionCollection($exception_collection, SubtaskInsufficentPrivilegeForActionException::class);
	}


	public function test_If_changeSubtaskDueDate_Can_Alter_Subtask_DueDate() {

		$subtask_arr = array($this->validSubtaskWithId(1));

		$task = $this->validTaskWithId(1,null,null,$subtask_arr); 

		$task->changeSubtaskDueDate(New SubtaskId(1), new DateTime('now'), new PersonnelId(1));

		$get_subtask = $task->subtasks();
		$subtask_time = $get_subtask[0]->dueDate();


		//$confirm_time_change = $task->dueDate();
		$this->assertTrue((new \DateTime())->getTimestamp() - $subtask_time->getTimestamp() < 5); 

	}

	public function test_changeSubtaskDueDate_Throws_An_Exception_If_UpdaterId_Doesnt_Match() {

		$this->expectException(SubtaskInsufficentPrivilegeForActionException::class);

		$subtask_arr = array($this->validSubtaskWithId(1));

		$task = $this->validTaskWithId(1,null,null,$subtask_arr); 

		$task->changeSubtaskDueDate(New SubtaskId(1), new DateTime('now'), new PersonnelId(3));

		$exception_collection = new ExceptionCollection($task->exceptions());
		$this->throwFromExceptionCollection($exception_collection, SubtaskInsufficentPrivilegeForActionException::class);


	}

	public function testIf_changeSubtaskLocation_Changes_The_Location(){

		$location = new Location('latitude', 'longitude');

		$subtask_arr = array($this->validSubtaskWithId(1,null,$location));

		$task = $this->validTaskWithId(1,null,null,$subtask_arr); 

		$task->changeSubtaskLocation(new SubtaskId(1), $location , new PersonnelId(1));

		$get_subtask = $task->subtasks();

		$returned_location = $get_subtask[0]->location();

		$latitude = $returned_location->latitude();
		$longitude = $returned_location->longitude();

		$this->assertEquals($latitude, 'latitude');
		$this->assertEquals($longitude, 'longitude');

	}

	public function testIf_changeSubtaskLocation_Throws_Exception_When_UpdaterIds_Doesnt_Match() {

		$this->expectException(SubtaskInsufficentPrivilegeForActionException::class);

		$location = new Location('latitude', 'longitude');

		$subtask_arr = array($this->validSubtaskWithId(1,null,$location));

		$task = $this->validTaskWithId(1,null,null,$subtask_arr); 

		$task->changeSubtaskLocation(new SubtaskId(1), $location, new PersonnelId(2));

		$exception_collection = new ExceptionCollection($task->exceptions());

		$this->throwFromExceptionCollection($exception_collection, SubtaskInsufficentPrivilegeForActionException::class);

	}

	public function testIf_changeSubtaskPriority_Changes_Given_Priority() {


		$subtask_arr = array($this->validSubtaskWithId(1));

		$task = $this->validTaskWithId(1,null,null,$subtask_arr); 	

		$task->changeSubtaskPriority(new SubtaskId(1), TaskPriority::Medium() , new PersonnelId(1));

		$get_subtask = $task->subtasks();
		$confirm_change = $get_subtask[0]->priority();

		$this->assertTrue($confirm_change == TaskPriority::Medium());

	}

	public function testIf_changeSubtaskPriority_Throws_Exception_When_UpdaterIds_Doesnt_Match() {

		$this->expectException(SubtaskInsufficentPrivilegeForActionException::class);

		$subtask_arr = array($this->validSubtaskWithId(1));

		$task = $this->validTaskWithId(1,null,null,$subtask_arr); 	

		$task->changeSubtaskPriority(new SubtaskId(1), TaskPriority::Medium() , new PersonnelId(2));

		$exception_collection = new ExceptionCollection($task->exceptions());

		$this->throwFromExceptionCollection($exception_collection, SubtaskInsufficentPrivilegeForActionException::class);
	}

	public function testIf_openSubtask_Allows_Updater_To_Change_Status_As_Open(){ 


		$subtask_arr = array($this->validSubtaskWithId(1));

		$task = $this->validTaskWithId(1, null,null, $subtask_arr); 	

		$task->openSubtask(new SubtaskId(1), new PersonnelId(1));

		$get_subtask = $task->subtasks();
		$firstarray_of_subtask = $get_subtask[0]->status();

		$this->assertTrue($firstarray_of_subtask == TaskStatus::Open());

	}

	public function testIf_openSubtask_Throws_Exception_When_Updater_Has_No_Access() {

		$this->expectException(SubtaskInsufficentPrivilegeForActionException::class);

		$subtask_arr = array($this->validSubtaskWithId(1));

		$task = $this->validTaskWithId(1,null,null,$subtask_arr); 	

		$task->openSubtask(new SubtaskId(1), new PersonnelId(4));

		$exception_collection = new ExceptionCollection($task->exceptions());
		$this->throwFromExceptionCollection($exception_collection, SubtaskInsufficentPrivilegeForActionException::class);

	}

	public function testIf_markSubtaskAsInProgress_Allows_Updater_To_Change_Status_As_InProgress(){ 


		$subtask_arr = array($this->validSubtaskWithId(1));

		$task = $this->validTaskWithId(1,null,null,$subtask_arr); 	

		$task->markSubtaskAsInProgress(new SubtaskId(1), new PersonnelId(1));

		$get_subtask = $task->subtasks();
		$firstarray_of_subtask = $get_subtask[0]->status();

		$this->assertTrue($firstarray_of_subtask == TaskStatus::InProgress());

	}

	public function testIf_markSubtaskAsInProgress_Throws_Exception_When_Updater_Has_No_Access() {

		$this->expectException(SubtaskInsufficentPrivilegeForActionException::class);

		$subtask_arr = array($this->validSubtaskWithId(1));

		$task = $this->validTaskWithId(1,null,null,$subtask_arr); 	

		$task->markSubtaskAsInProgress(new SubtaskId(1), new PersonnelId(4));

		$exception_collection = new ExceptionCollection($task->exceptions());
		$this->throwFromExceptionCollection($exception_collection, SubtaskInsufficentPrivilegeForActionException::class);

	}

	public function testIf_delaySubtask_Allows_Updater_To_Change_Status_As_InProgress(){ 


		$subtask_arr = array($this->validSubtaskWithId(1));

		$task = $this->validTaskWithId(1,null,null,$subtask_arr); 	

		$task->delaySubtask(new SubtaskId(1), new PersonnelId(1));

		$get_subtask = $task->subtasks();
		$firstarray_of_subtask = $get_subtask[0]->status();

		$this->assertTrue($firstarray_of_subtask == TaskStatus::Delayed());

	}

	public function testIf_delaySubtask_Throws_Exception_When_Updater_Has_No_Access() {  

		$this->expectException(SubtaskInsufficentPrivilegeForActionException::class);

		$subtask_arr = array($this->validSubtaskWithId(1));

		$task = $this->validTaskWithId(1,null,null,$subtask_arr); 	

		$task->delaySubtask(new SubtaskId(1), new PersonnelId(4));

		$exception_collection = new ExceptionCollection($task->exceptions());
		$this->throwFromExceptionCollection($exception_collection, SubtaskInsufficentPrivilegeForActionException::class);

	}


	public function testIf_completeSubtask_Allows_Updater_To_Change_Status_As_InProgress(){ 


		$subtask_arr = array($this->validSubtaskWithId(1));

		$task = $this->validTaskWithId(1,null,null,$subtask_arr); 	

		$task->completeSubtask(new SubtaskId(1), new PersonnelId(1));

		$get_subtask = $task->subtasks();
		$firstarray_of_subtask = $get_subtask[0]->status();

		$this->assertTrue($firstarray_of_subtask == TaskStatus::Completed());

	}

	public function testIf_completeSubtask_Throws_Exception_When_Updater_Has_No_Access() {  

		$this->expectException(SubtaskInsufficentPrivilegeForActionException::class);

		$subtask_arr = array($this->validSubtaskWithId(1));

		$task = $this->validTaskWithId(1,null,null,$subtask_arr); 	

		$task->completeSubtask(new SubtaskId(1), new PersonnelId(4));

		$exception_collection = new ExceptionCollection($task->exceptions());
		$this->throwFromExceptionCollection($exception_collection, SubtaskInsufficentPrivilegeForActionException::class);

	}

	public function testIf_cancelSubtask_Allows_Updater_To_Change_Status_As_InProgress(){ 


		$subtask_arr = array($this->validSubtaskWithId(1));

		$task = $this->validTaskWithId(1,null,null,$subtask_arr); 	

		$task->cancelSubtask(new SubtaskId(1), new PersonnelId(1));

		$get_subtask = $task->subtasks();
		$firstarray_of_subtask = $get_subtask[0]->status();

		$this->assertTrue($firstarray_of_subtask == TaskStatus::Cancelled());


	}

	public function testIf_cancelSubtask_Throws_Exception_When_Updater_Has_No_Access() {  

		$this->expectException(SubtaskInsufficentPrivilegeForActionException::class);

		$subtask_arr = array($this->validSubtaskWithId(1));

		$task = $this->validTaskWithId(1,null,null,$subtask_arr); 	

		$task->cancelSubtask(new SubtaskId(1), new PersonnelId(4));

		$exception_collection = new ExceptionCollection($task->exceptions());
		$this->throwFromExceptionCollection($exception_collection, SubtaskInsufficentPrivilegeForActionException::class);

	}

	public function testIf_commentOnSubtask_Adds_Comment_To_Subtask() {


		$subtask_arr = array($this->validSubtaskWithId(1));

		$task = $this->validTaskWithId(1,null,null,$subtask_arr); 

		$task->commentOnSubtask(new SubtaskId(1), new PersonnelId(1), 'this is a subtask comment');

		$get_subtask = $task->subtasks();
		$confirm_comment_added = $get_subtask[0]->comments();

		$this->assertNotEmpty($confirm_comment_added);
	}

	public function testIf_Throws_Exception_When_Commentator_Has_No_Privilage(){

		$this->expectException(SubtaskCommentPrivilegeException::class);

		$subtask_arr = array($this->validSubtaskWithId(1));

		$task = $this->validTaskWithId(1,null,null,$subtask_arr); 

		$task->commentOnSubtask(new SubtaskId(1), new PersonnelId(4), 'this is a subtask comment');

		$exception_collection =  new ExceptionCollection($task->exceptions());
		$this->throwFromExceptionCollection($exception_collection, SubtaskCommentPrivilegeException::class);

	
	}

	public function testIf_editSubtaskComment_Changes_Subtask_Comment() {

		$comment_arr = array(
			new Comment(New CommentId(1), new PersonnelId(3), 'this is a new comment', new DateTime('now'), new DateTime('now'))
		);

		$subtask_arr = array($this->validSubtaskWithId(1,null,null,$comment_arr));

		$task = $this->validTaskWithId(1,null,null,$subtask_arr); 

		$task->editSubtaskComment(new SubtaskId(1), new CommentId(1), 'changed subtask comment', new PersonnelId(1));

		$get_subtask = $task->subtasks();
		$confirm_comment_added = $get_subtask[0]->comments();

		$this->assertNotEmpty($confirm_comment_added);
	}

	public function testIf_removeSubtaskComment_Removes_The_Subtask_Comment(){

		$comment_arr = array(
			new Comment(New CommentId(1), new PersonnelId(3), 'this is a new comment', new DateTime('now'), new DateTime('now'))
		);

		$subtask_arr = array($this->validSubtaskWithId(1,null,null,$comment_arr));

		$task = $this->validTaskWithId(1,null,null,$subtask_arr); 

		$task->removeSubtaskComment(new SubtaskId(1), new CommentId(1), new PersonnelId(5));

		$get_subtask = $task->subtasks();
		$confirm_comment_removed = $get_subtask[0]->comments();

		$this->assertEmpty($confirm_comment_removed);

	}

	public function testIf_taskAttachment_Adds_Attachment_To_Subtask(){

		$subtask_arr = array($this->validSubtaskWithId(1));

		$task = $this->validTaskWithId(1,null,null,$subtask_arr);

		$task->addSubtaskAttachment(new SubtaskId(1), 'base64' , 'new attachment', new PersonnelId(2));

		$get_subtask = $task->subtasks();
		$attachment_added = $get_subtask[0]->attachments();

		$this->assertEquals($attachment_added[0]->name(), 'new attachment');
	}


	public function test_addSubtaskAttachment_Throws_Exception_When_Updater_Has_No_Access(){

		$this->expectException(SubtaskAttachmentPrivilegeException::class);

		$subtask_arr = array($this->validSubtaskWithId(1));

		$task = $this->validTaskWithId(1,null,null,$subtask_arr);

		$task->addSubtaskAttachment(new SubtaskId(1), 'base64' , 'new attachment', new PersonnelId(6));

		$exception_collection = new ExceptionCollection($task->exceptions());
		$this->throwFromExceptionCollection($exception_collection, SubtaskAttachmentPrivilegeException::class);
	}

	public function test_removeSubtaskAttachment_Removes_Subtask_Attachment() { 

		$attachment_arr = array(
			new Attachment(new AttachmentId(1), new PersonnelId(1), 'entanglements', 'base64', new DateTime('now'))
		);

		$subtask_arr = array($this->validSubtaskWithId(1,null,null,null,$attachment_arr));
		$task = ($this->validTaskWithId(1,null,null,$subtask_arr));

		$task->removeSubtaskAttachment(new SubtaskId(1), new AttachmentId(1), new PersonnelId(1));

		$get_subtask = $task->subtasks();
		$confirm_attachment_removed = $get_subtask[0]->attachments();

		$this->assertEmpty($confirm_attachment_removed);

	}

	public function test_removeSubtaskAttachment_Throws_Exception_When_Remover_Has_No_Access() {

		$this->expectException(AttachmentEditPrivilegeException::class);

		$attachment_arr = array(
			new Attachment(new AttachmentId(1), new PersonnelId(1), 'entanglements', 'base64', new DateTime('now'))
		);

		$subtask_arr = array($this->validSubtaskWithId(1,null,null,null,$attachment_arr));

		$task = $this->validTaskWithId(1,null,null,$subtask_arr);

		$task->removeSubtaskAttachment(new SubtaskId(1), new AttachmentId(1), new PersonnelId(4));

		$exception_collection = new ExceptionCollection($task->exceptions());
		$this->throwFromExceptionCollection($exception_collection, AttachmentEditPrivilegeException::class);

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