<?php

use \model\TaskManagement\domain\model\Subtask;
use \model\TaskManagement\domain\model\Location;
use \model\TaskManagement\domain\model\TaskPriority;
use \model\TaskManagement\domain\model\TaskStatus;
use \model\TaskManagement\domain\model\SubtaskId;
use \model\TaskManagement\domain\model\TaskId;
use \model\TaskManagement\domain\model\PersonnelId;
use \model\TaskManagement\domain\model\Comment;
use \model\TaskManagement\domain\model\Attachment;
use \model\TaskManagement\domain\model\AttachmentId;
use \model\TaskManagement\domain\model\CommentId;
use \model\TaskManagement\domain\model\exception\CommentEditPrivilegeException;
use \model\common\ExceptionCollection;


use PHPUnit\Framework\TestCase;


class SubtaskTest extends TestCase {

	private function validSubtaskWithId($id, $comments = null, $attachments = null){
		return new Subtask(
			new SubtaskId($id), 			/* id */
			new TaskId(1), 					/* task id */
			'Subtask Title',				/* title */
			new PersonnelId(1),				/* assigner */
			null,							/* assignee[] */
			'Subtask Description',			/* description */ 
			new DateTime(), 				/* start date */
			new DateTime(), 				/* due date */
			null, 							/* location */
			null, 							/* priority */
			null, 							/* status */
			$comments, 						/* comments[] */
			null, 							/* events[] */
			$attachments, 					/* attachments[] */
			new DateTime()					/* created on[] */
		);
	}


	public function test_IsAssigner_Returns_True_If_PersonnelId_Matches() {

		$subtask = $this->validSubtaskWithId(1);

		$check_can_assign = $subtask->isAssigner(new PersonnelId(1));
		$this->assertTrue($check_can_assign);

	}

	public function test_If_changeTitle_Can_Change_The_Title(){

		$subtask = $this->validSubtaskWithId(1);

		$subtask->changeTitle('this is changed changed title!!', new PersonnelId(1));
		$get_changed_title = $subtask->title();

		$this->assertEquals('this is changed changed title!!', $get_changed_title);		
	}

	public function test_If_changeTitle_Stores_Events() {

		$subtask = $this->validSubtaskWithId(1);

		$subtask->changeTitle('this is changed changed title!!', new PersonnelId(1));

		$check_event_stored = $subtask->events();
		$this->assertNotEmpty($check_event_stored);
	}

	public function test_If_assignTo_Can_Assign_A_Task_To_An_Assignee() {
	
		$subtask = $this->validSubtaskWithId(1);

		$subtask->assignTo(new PersonnelId(1), new PersonnelId(2));

		$confirm_subtask_assigned = $subtask->isAssignee(new PersonnelId(1));
		$this->assertTrue($confirm_subtask_assigned);
	}

	public function test_If_deAssign_Can_deAssign_A_Task_From_An_Assignee() {

		$subtask = $this->validSubtaskWithId(1);	

		$subtask->deassignFrom(new PersonnelId(1), new PersonnelId(2));
		$confirm_subtask_deassigned = $subtask->isAssignee(new PersonnelId(1));

		$this->assertFalse($confirm_subtask_deassigned);
	}

	public function test_If_changeDescription_Can_Change_The_Description(){

		$subtask = $this->validSubtaskWithId(1);

		$subtask->changeDescription('changed desc', new PersonnelId(1));
		$confirm_description_change = $subtask->description();

		$this->assertEquals('changed desc', $confirm_description_change);

	} 

	public function test_If_changeStartDate_Changes_The_Start_Date(){


		$subtask = $this->validSubtaskWithId(1);	

		$subtask->changeStartDate(new DateTime('now'), new PersonnelId(1));
		$confirm_startdate_change = $subtask->startDate();

		$this->assertTrue((new DateTime())->getTimestamp() - $confirm_startdate_change->getTimestamp() < 5);

	}

	public function test_If_changeDueDate_Can_Change_OperationTime() {

		$subtask = $this->validSubtaskWithId(1);

		$subtask->changeDueDate(new DateTime('now'), new PersonnelId(1));
		$confirm_duedate_change = $subtask->dueDate();

		$this->assertTrue((new DateTime('now'))->getTimestamp() - $confirm_duedate_change->getTimestamp() < 5);
	}

	public function test_If_changeLocation_Can_Change_Given_Location() {

		$subtask = $this->validSubtaskWithId(1);

		$subtask->changeLocation(new Location('first', 'second'), new PersonnelId(1));
		$confirm_location_change = $subtask->location();

		$this->assertNotEmpty($confirm_location_change);
	}

	public function test_If_changePriority_Can_Change_The_Given_Priority() {

		$subtask = $this->validSubtaskWithId(1);

		$subtask->changePriority(TaskPriority::Low(), new PersonnelId(1));
		$confirm_priority_change = $subtask->priority();

		$this->assertTrue(TaskPriority::Low() == $confirm_priority_change);
	}

	public function test_If_open_Function_Changes_Status_To_Open() {

		$subtask = $this->validSubtaskWithId(1);

		$subtask->open(new PersonnelId(1));
		$confirm_status_change = $subtask->status();

		$this->assertTrue(TaskStatus::Open() == $confirm_status_change);
	}

	public function test_If_markAsInProgress_Changes_Status_To_InProgress(){

		$subtask = $this->validSubtaskWithId(1);

		$subtask->markAsInProgress(new PersonnelId(1));
		$confirm_status_change = $subtask->status();

		$this->assertTrue(TaskStatus::InProgress() == $confirm_status_change);		
	}

	public function test_If_delay_Function_Changes_Status_To_Delayed(){

		$subtask = $this->validSubtaskWithId(1);

		$subtask->delay(new PersonnelId(1));
		$confirm_status_change = $subtask->status();

		$this->assertTrue(TaskStatus::Delayed() == $confirm_status_change);
	}

	public function test_If_complete_Function_Changes_Status_To_Completed() {

		$subtask = $this->validSubtaskWithId(1);

		$subtask->complete(new PersonnelId(1));
		$confirm_status_change = $subtask->status();

		$this->assertTrue(TaskStatus::Completed() == $confirm_status_change);
	}

	public function test_If_cancel_Function_Changes_Status_To_Cancelled() {

		$subtask = $this->validSubtaskWithId(1);

		$subtask->cancel(new PersonnelId(1));
		$confirm_status_change = $subtask->status();

		$this->assertTrue(TaskStatus::Cancelled() == $confirm_status_change);

	}

	public function test_If_isComplete_Confirmes_The_Status_Is_Completed() {

		$subtask = $this->validSubtaskWithId(1);

		$subtask->complete(new PersonnelId(1));
		$confirm_status_isComplete = $subtask->isComplete();

		$this->assertTrue($confirm_status_isComplete);

	}

	public function test_If_comment_Function_Adds_A_New_Comment() {

		$subtask = $this->validSubtaskWithId(1);

		$subtask->comment(new PersonnelId(1), 'this is the comment!');
		$comments = $subtask->comments();

		$commentator_id_obj = $comments[0]->commentator();
		$commentator_id_int = $commentator_id_obj->getId();

		$this->assertEquals(1, $commentator_id_int);
	}

	public function test_If_editComment_Alters_The_Existing_Comment() {
		$comments = array(
			new Comment(new CommentId(1), new PersonnelId(1), 'this is the message', new DateTime(), new DateTime())
		); 
		$subtask = $this->validSubtaskWithId(1, $comments);

		$subtask->editComment(new CommentId(1), 'updated message', new PersonnelId(1));

		$comment_obj_arr = $subtask->comments();
		$updated_comment_msg = $comment_obj_arr[0]->message();

		$this->assertEquals($updated_comment_msg, 'updated message');
	}

	public function test_If_editComment_Throws_Exception_When_Updater_Ids_Doesnt_Match(){
		
		$this->expectException(CommentEditPrivilegeException::class);

		$comment_arr = array(
			new Comment(new CommentId(1), new PersonnelId(1), 'this is the message', new DateTime(), new DateTime())
		); 

		$subtask = $this->validSubtaskWithId(1, $comment_arr);
		
		$subtask->editComment(new CommentId(1), 'edited fail', new PersonnelId(2));
		$exception_collection = new ExceptionCollection($subtask->exceptions());

		$this->throwFromExceptionCollection($exception_collection, CommentEditPrivilegeException::class);
	}

	public function test_If_removeComment_Removes_Existing_Comment_Successfully(){

		$comments = array(
			new Comment(new CommentId(1), new PersonnelId(1), 'this is the message', new DateTime(), new DateTime())
		); 

		$subtask = $this->validSubtaskWithId(1, $comments);	

		$removed = $subtask->removeComment(new CommentId(1), new PersonnelId(1));
		$this->assertTrue($removed);
	}


	public function test_If_attachment_Function_Adds_A_New_Attachment() {

		$subtask = $this->validSubtaskWithId(1);

		$subtask->attachment('base-64', 'attachment-name', new PersonnelId(1));
		$attachment = $subtask->attachments();

		$attachment_name = $attachment[0]->name();
		$this->assertEquals($attachment_name, 'attachment-name');

	}

	public function test_If_removeAttachment_Removes_Attachment(){
		$attachments = array(
			new Attachment(new AttachmentId(1), new PersonnelId(1), 'attachment name', 'base64', new DateTime())
		); 
		$subtask = $this->validSubtaskWithId(1, null, $attachments);

		$subtask->removeAttachment(new AttachmentId(1), new PersonnelId(1));
	 	$attachment_container = $subtask->attachments();

	 	$this->assertEmpty($attachment_container);
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