<?php

use \model\ProcedureManagement\domain\model\Procedure;
use \model\ProcedureManagement\domain\model\ProcedureId;
use \model\ProcedureManagement\domain\model\InitiatorId;
use \model\ProcedureManagement\domain\model\ProcedureType;
use \model\ProcedureManagement\domain\model\Step;
use \model\ProcedureManagement\domain\model\StepId;
use \model\ProcedureManagement\domain\model\Comment;
use \model\ProcedureManagement\domain\model\CommentId;
use \model\ProcedureManagement\domain\model\ContainerId;
use \model\ProcedureManagement\domain\model\DepartmentId;
use \model\ProcedureManagement\domain\model\PersonnelId;
use \model\ProcedureManagement\domain\model\AttachmentId;
use \model\ProcedureManagement\domain\model\Choice;
use \model\ProcedureManagement\domain\model\ChoiceType;
use \model\ProcedureManagement\domain\model\Subprocedure;
use \model\ProcedureManagement\domain\model\ContainerType;

use \model\ProcedureManagement\domain\model\exception\StepNotFoundException;
use \model\ProcedureManagement\domain\model\exception\PersonnelNotAuthorizedException;
use \model\ProcedureManagement\domain\model\exception\ProcedureConcludedException;
use \model\ProcedureManagement\domain\model\exception\ProcedureDescriptionLenghtException;

use PHPUnit\Framework\TestCase;


class ProcedureTest extends TestCase {


	public function test_IsInProgress_Returns_True_If_One_Of_The_Steps_Is_Not_Completed(){
		$choices_arr = array();
		$steps_arr = [

			new Step(new StepId(1),'this is first title',true, true, $choices_arr, null, 1), 
			new Step(new StepId(2), 'this is second title',true, true, $choices_arr, null, 1),
			new Step(new StepId(3), 'this is third title',true,false, $choices_arr, null, 1) 
			
			// 1 false is enough for IsInProgress to Return true.
		];
		$procedure = new Procedure(
				new ProcedureId(1), 
				new ContainerId(1),
				ContainerType::Structure(), 
				null, 
				'this is the procedure title', 
				'description',
				$steps_arr, 
				null,
				$steps_arr[0],
				ProcedureType::Numbering(),
				new DepartmentId(1)
			);

	$confirm_isInProgress = $procedure->isInProgress();
	$this->assertTrue($confirm_isInProgress);
	
	}


	public function test_If_isComplete_Returns_True_If_Step_Is_Completed(){
		$choices_arr = array();
		$steps_arr = [
			new Step(new StepId(1),'this is first title',true, true, $choices_arr, null, 1), 
			new Step(new StepId(2), 'this is second title',true, true, $choices_arr, null, 1),
			new Step(new StepId(3), 'this is third title',true,false, $choices_arr, null, 1)  

			// 1 false is enough for IsComplete to Return false.
		];

		$procedure = new Procedure(
				new ProcedureId(1), 
				new ContainerId(1),
				ContainerType::Structure(), 
				null, 
				'this is the procedure title', 
				'description',
				$steps_arr, 
				null,
				$steps_arr[0],
				ProcedureType::Numbering(),
				new DepartmentId(1)
			);

	$confirm_isComplete = $procedure->isComplete();
	$this->assertFalse($confirm_isComplete);

	}

	public function test_If_isComplete_Returns_False_If_Step_Is_Not_Completed(){
		$choices_arr = array();
		$steps_arr = [
			new Step(new StepId(2), 'this is second title',true, false, $choices_arr, null, 1)
			// fourth param : is_complete
		];

		$procedure = new Procedure(
				new ProcedureId(1), 
				new ContainerId(1),
				ContainerType::Structure(), 
				null, 
				'this is the procedure title', 
				'description',
				$steps_arr, 
				null,
				$steps_arr[0],
				ProcedureType::Numbering(),
				new DepartmentId(1)
			);

	$confirm_isComplete_false = $procedure->isComplete();
	$this->assertFalse($confirm_isComplete_false);
	}


	public function test_If_comment_Method_Returns_Comment_With_Defined_StepId(){
		$choices_arr = array();
		$steps_arr = [
			new Step(new StepId(2), 'this is second title',true, false, $choices_arr, null, 1),
			new Step(new StepId(2), 'this is second title',true, true, $choices_arr, null, 1)
		];

		$procedure = new Procedure(
				new ProcedureId(1), 
				new ContainerId(1),
				ContainerType::Structure(), 
				null, 
				'this is the procedure title', 
				'description',
				$steps_arr, 
				null,
				$steps_arr[0],
				ProcedureType::Numbering(),
				new DepartmentId(1)
			);

		$return_comment = $procedure->comment(new StepId(2), new CommentId(1), new PersonnelId(1), 'this is the comment message', new DepartmentId(1));
		$this->assertEquals(new StepId(2), $return_comment->stepId());
	}

	public function test_If_addAttachment_Returns_Attachment_With_Defined_StepId(){
		$choices_arr = array();
		$steps_arr = [
			new Step(new StepId(2), 'this is second title',true, false, $choices_arr, null, 1),
			new Step(new StepId(2), 'this is second title',true, true, $choices_arr, null, 1)
		];

		$procedure = new Procedure(
				new ProcedureId(1), 
				new ContainerId(1),
				ContainerType::Structure(), 
				null, 
				'this is the procedure title', 
				'description',
				$steps_arr, 
				null,
				$steps_arr[0],
				ProcedureType::Numbering(),
				new DepartmentId(1)
			);

		$return_attachment = $procedure->addAttachment(new StepId(2), new AttachmentId(1), new PersonnelId(1), 'base64','attachment name', new DepartmentId(1));
		$this->assertEquals(new StepId(2), $return_attachment->stepId());
	}

	public function test_If_advance_Method_Completes_Chosen_Step(){
		$choices_arr = array(new Choice(
				'comment_message',
				new StepId(3),
				null,
				ChoiceType::Transition(),
				3
			)
		);

		$steps_arr = [
			new Step(new StepId(1), 'this is second title',true, true, $choices_arr, null, 1),
			new Step(new StepId(2), 'this is second title',true, true, $choices_arr, null, 2),
			new Step(new StepId(3), 'this is second title',true, false, $choices_arr, null, 3),
			new Step(new StepId(4), 'this is second title',true, false, $choices_arr, null, 4),
			new Step(new StepId(5), 'this is second title',true, false, $choices_arr, null, 5)
		];

		$procedure = new Procedure(
				new ProcedureId(1), 
				new ContainerId(1),
				ContainerType::Structure(), 
				null, 
				'this is the procedure title', 
				'description',
				$steps_arr, 
				null,
				$steps_arr[2],
				ProcedureType::Numbering(),
				new DepartmentId(1)
			);

		$procedure->advance(3, new DepartmentId(1));

		$steps_of_procedure = $procedure->steps();	//only step id(3) will return true because advance will return true by order. 

		$confirm_stepthree_completed = $steps_of_procedure[2]->isComplete(); 	
		$this->assertTrue($confirm_stepthree_completed);

		$confirm_stepfour_incomplete = $steps_of_procedure[3]->isComplete();
		$this->assertFalse($confirm_stepfour_incomplete);

		$confirm_stepfive_incomple = $steps_of_procedure[4]->isComplete();
		$this->assertFalse($confirm_stepfive_incomple);

	}


	public function test_If_hasActiveIncompleteSubprocedures_Returns_True_When_There_Is_An_Incomplete_Subprocedure(){
		$choices_arr = array(new Choice(
				'comment_message',
				new StepId(3),
				null,
				ChoiceType::Transition(),
				3
			)
		);

		$steps_arr = [
			new Step(new StepId(1), 'this is a title',true, true,  $choices_arr, null, 1),
			new Step(new StepId(2), 'this is a title',true, false, $choices_arr, null, 3) 
			// second step will make the method return true, doesnt matter first step is completed.
		];

		$subprocedure_arr = array(
			$child = new Subprocedure(
				new ProcedureId(2),
				new ProcedureId(1), 	 /* parent id */
				'subprocedure_title',
				$steps_arr,
				$steps_arr[1], 			/* current step */
				true
		));

		$procedure = new Procedure(
				new ProcedureId(1), 
				new ContainerId(1),
				ContainerType::Structure(), 
				null, 
				'this is the parent procedure title', 
				'description',
				$steps_arr, 
				$subprocedure_arr,
				null,
				ProcedureType::Numbering(),
				new DepartmentId(1)
			);

		$confirm_incomplete_steps_exist = $procedure->hasActiveIncompleteSubprocedures();
		$this->assertTrue($confirm_incomplete_steps_exist);
	}


	public function test_If_advanceSubprocedure_Completes_Chosen_Step(){
		$choices_arr = array(new Choice(
				'comment_message',
				new StepId(3),
				null,
				ChoiceType::Transition(),
				3
			)
		);

		$steps_arr = [
			new Step(new StepId(1), 'this is a title',true, true,  $choices_arr, null, 1),
			new Step(new StepId(2), 'this is a title',true, true,  $choices_arr, null, 2),
			new Step(new StepId(3), 'this is a title',true, false, $choices_arr, null, 3),
			new Step(new StepId(4), 'this is a title',true, false, $choices_arr, null, 4),
			new Step(new StepId(5), 'this is a title',true, false, $choices_arr, null, 5)
		];

		$subprocedure_arr = array(
			$child = new Subprocedure(
				new ProcedureId(2),
				new ProcedureId(1), 	 /* parent id */
				'subprocedure_title',
				$steps_arr,
				$steps_arr[2], 			/* current step */
				true
		));

		$procedure = new Procedure(
				new ProcedureId(1), 
				new ContainerId(1),
				ContainerType::Structure(), 
				null, 
				'this is the parent procedure title', 
				'description',
				$steps_arr, 
				$subprocedure_arr,
				null,
				ProcedureType::Numbering(),
				new DepartmentId(1)
			);

		$procedure->advanceSubprocedure(new ProcedureId(2),  3, new DepartmentId(1));

		$steps_of_procedure = $child->steps();	

		$confirm_stepthree_completed = $steps_of_procedure[2]->isComplete(); 	
		$this->assertTrue($confirm_stepthree_completed);

		$confirm_stepfour_incomplete = $steps_of_procedure[3]->isComplete();
		$this->assertFalse($confirm_stepfour_incomplete);


	}


	public function test_If_advanceSubprocedure_Throws_Exception_When_Personnel_Isnt_Authorized(){
		$this->expectException(PersonnelNotAuthorizedException::class);

		$choices_arr = array(new Choice(
				'comment_message',
				new StepId(3),
				null,
				ChoiceType::Transition(),
				3
			)
		);

		$steps_arr = [
			new Step(new StepId(1), 'this is a title',true, true,  $choices_arr, null, 1),
			new Step(new StepId(2), 'this is a title',true, true,  $choices_arr, null, 2),
			new Step(new StepId(3), 'this is a title',true, false, $choices_arr, null, 3),
			new Step(new StepId(4), 'this is a title',true, false, $choices_arr, null, 4),
			new Step(new StepId(5), 'this is a title',true, false, $choices_arr, null, 5)
		];

		$subprocedure_arr = array(
			$child = new Subprocedure(
				new ProcedureId(2),
				new ProcedureId(1), 	 /* parent id */
				'subprocedure_title',
				$steps_arr,
				$steps_arr[2], 			 /* current step */
				true
		));

		$procedure = new Procedure(
				new ProcedureId(1), 
				new ContainerId(1),
				ContainerType::Structure(), 
				null, 
				'this is the parent procedure title', 
				'description',
				$steps_arr, 
				$subprocedure_arr,
				null,
				ProcedureType::Numbering(),
				new DepartmentId(1)
			);

		$procedure->advanceSubprocedure(new ProcedureId(2),  3, new DepartmentId(2));

		$steps_of_procedure = $child->steps();	

		$confirm_stepthree_completed = $steps_of_procedure[2]->isComplete(); 	
		$this->assertTrue($confirm_stepthree_completed);

	}

	public function test_If_advance_Method_Throws_Exception_If_Step_Isnt_Found(){ 
		$this->expectException(PersonnelNotAuthorizedException::class);

			$choices_arr = array(new Choice(
				'comment_message',
				new StepId(3),
				null,
				ChoiceType::Transition(),
				3
			)
		);

		$steps_arr = [
			new Step(new StepId(1), 'this is a title',true, true, $choices_arr, null, 1),
			new Step(new StepId(2), 'this is a title',true, true, $choices_arr, null, 2),
			new Step(new StepId(3), 'this is a title',true, false, $choices_arr, null, 3),
			new Step(new StepId(4), 'this is a title',true, false, $choices_arr, null, 4),
			new Step(new StepId(5), 'this is a title',true, false, $choices_arr, null, 5)
		];

		$procedure = new Procedure(
				new ProcedureId(1), 
				new ContainerId(1),
				ContainerType::Structure(), 
				null, 
				'this is the procedure title', 
				'description',
				$steps_arr, 
				null,
				null,
				ProcedureType::Numbering(),
				new DepartmentId(1)
			);

		$procedure->advance(3, new DepartmentId(2));  // department ids doesnt match, this should throw an exception.

		$steps_of_procedure = $procedure->steps();	

		$confirm_stepthree_completed = $steps_of_procedure[2]->isComplete(); 	
		$this->assertTrue($confirm_stepthree_completed);
	}

	public function test_If_advance_Method_Throws_Exception_If_Step_Concluded_Already(){ 
		$this->expectException(ProcedureConcludedException::class);

			$choices_arr = array(new Choice(
				'comment_message',
				new StepId(3),
				null,
				ChoiceType::Transition(),
				1
			)
		);

		$steps_arr = [
			new Step(new StepId(1), 'this is a title',true, true, $choices_arr, null, 1),
			new Step(new StepId(2), 'this is a title',true, true, $choices_arr, null, 2),
			new Step(new StepId(3), 'this is a title',true, false, $choices_arr, null, 3),
			new Step(new StepId(4), 'this is a title',true, false, $choices_arr, null, 4),
			new Step(new StepId(5), 'this is a title',true, false, $choices_arr, null, 5)
		];

		$procedure = new Procedure(
				new ProcedureId(1), 
				new ContainerId(1),
				ContainerType::Structure(), 
				null, 
				'this is the procedure title', 
				'description',
				$steps_arr, 
				null,
				null,
				ProcedureType::Numbering(),
				new DepartmentId(1)
			);

		$procedure->advance(1, new DepartmentId(1));  

		$steps_of_procedure = $procedure->steps();	

		$confirm_stepthree_completed = $steps_of_procedure[0]->isComplete(); 	
		$this->assertTrue($confirm_stepthree_completed);
	}

	public function test_If_changeDescription_Changes_Existing_Description_Of_Procedure(){

		$choices_arr = array();
		$steps_arr = [
			new Step(new StepId(2), 'this is second title',true, false, $choices_arr, null, 1),
			new Step(new StepId(2), 'this is second title',true, true, $choices_arr, null, 1)
		];

		$procedure = new Procedure(
				new ProcedureId(1), 
				new ContainerId(1),
				ContainerType::Structure(), 
				null, 
				'this is the procedure title', 
				'description',
				$steps_arr, 
				null,
				$steps_arr[0],
				ProcedureType::Numbering(),
				new DepartmentId(1)
		);

		$procedure->changeDescription('prometheus');

		$this->assertNotEquals($procedure->description(), 'description');
		$this->assertEquals($procedure->description(), 'prometheus');
	}

	public function test_changeDescription_Throws_Exception_If_Description_Lenght_Is_Lower_Than_2(){

		$this->expectException(ProcedureDescriptionLenghtException::class);
		
		$choices_arr = array();
		$steps_arr = [
			new Step(new StepId(2), 'this is second title',true, false, $choices_arr, null, 1),
			new Step(new StepId(2), 'this is second title',true, true, $choices_arr, null, 1)
		];

		$procedure = new Procedure(
				new ProcedureId(1), 
				new ContainerId(1),
				ContainerType::Structure(), 
				null, 
				'this is the procedure title', 
				'description',
				$steps_arr, 
				null,
				$steps_arr[0],
				ProcedureType::Numbering(),
				new DepartmentId(1)
		);

		$procedure->changeDescription('p');

		$exception_array = $procedure->exceptions();

		foreach ($exception_array as $exception) {
			if(get_class($exception) == ProcedureDescriptionLenghtException::class)
				throw new ProcedureDescriptionLenghtException();
		}

	}


	public function test_changeDescription_Throws_Exception_If_Description_Lenght_Is_Longer_Than_256(){

		$this->expectException(ProcedureDescriptionLenghtException::class);
		
		$choices_arr = array();
		$steps_arr = [
			new Step(new StepId(2), 'this is second title',true, false, $choices_arr, null, 1),
			new Step(new StepId(2), 'this is second title',true, true, $choices_arr, null, 1)
		];

		$procedure = new Procedure(
				new ProcedureId(1), 
				new ContainerId(1),
				ContainerType::Structure(), 
				null, 
				'this is the procedure title', 
				'description',
				$steps_arr, 
				null,
				$steps_arr[0],
				ProcedureType::Numbering(),
				new DepartmentId(1)
		);

		$procedure->changeDescription(str_repeat('a', 257));

		$exception_array = $procedure->exceptions();

		foreach ($exception_array as $exception) {
			if(get_class($exception) == ProcedureDescriptionLenghtException::class)
				throw new ProcedureDescriptionLenghtException();
		}

	}
}

?>