<?php

use \model\ProcedureManagement\domain\model\Procedure;
use \model\ProcedureManagement\domain\model\ProcedureId;
use \model\ProcedureManagement\domain\model\InitiatorId;
use \model\ProcedureManagement\domain\model\ProcedureType;
use \model\ProcedureManagement\domain\model\Step;
use \model\ProcedureManagement\domain\model\StepId;
use \model\ProcedureManagement\domain\model\Comment;
use \model\ProcedureManagement\domain\model\CommentId;
use \model\ProcedureManagement\domain\model\PersonnelId;
use \model\ProcedureManagement\domain\model\AttachmentId;

use PHPUnit\Framework\TestCase;


class ProcedureTest extends TestCase {

	public function test_IsInProgress_Returns_True_If_One_Of_The_Steps_Is_Not_Completed(){

		$steps_arr = [
			new Step(new StepId(1),'this is first title', true,1), 
			new Step(new StepId(2), 'this is second title', true,1),
			new Step(new StepId(3), 'this is third title', false,1) // 1 false is enough for IsInProgress to Return true.

		];
		$procedure = new Procedure(
			new ProcedureId(1), 
			new InitiatorId(1234567890), 
			'this is the procedure title', 
			$steps_arr, 
			ProcedureType::ConstructionPermit(),
			true
			);

	$confirm_isInProgress = $procedure->isInProgress();
	$this->assertTrue($confirm_isInProgress);
	
	}

	public function test_If_isComplete_Returns_True_If_Step_Is_Completed(){

		$steps_arr = [
			new Step(new StepId(1),'this is first title', true,1), 
			new Step(new StepId(2), 'this is second title', true,1), 
			new Step(new StepId(3), 'this is third title', false,1) // 1 false is enough for IsComplete to Return false.
		];

		$procedure = new Procedure(
			new ProcedureId(1), 
			new InitiatorId(1234567890), 
			'this is the procedure title', 
			$steps_arr, 
			ProcedureType::ConstructionPermit(),
			true
			);

	$confirm_isComplete = $procedure->isComplete();
	$this->assertFalse($confirm_isComplete);
	}

	public function test_If_isComplete_Returns_False_If_Step_Is_Not_Completed(){

		$steps_arr = [
			array(new Step(new StepId(1),'this is first title', false,1)), 
		];
		$procedure = new Procedure(
			new ProcedureId(1), 
			new InitiatorId(1234567890), 
			'this is the procedure title', 
			$steps_arr[0], 
			ProcedureType::ConstructionPermit(),
			true
			);

	$confirm_isComplete_false = $procedure->isComplete();
	$this->assertFalse($confirm_isComplete_false);
	}

	public function test_If_isRepeatable_Returns_True(){

		$steps_arr = [
			array(new Step(new StepId(1),'this is first title', false,1)), 
		];
		$procedure = new Procedure(
			new ProcedureId(1), 
			new InitiatorId(1234567890), 
			'this is the procedure title', 
			$steps_arr[0], 
			ProcedureType::ConstructionPermit(),
			true // this will make it return true.
			);

		$confirm_returns_true = $procedure->isRepeatable();
		$this->assertTrue($confirm_returns_true);
	}

	public function test_If_isRepeatable_Returns_False(){

		$steps_arr = [
			array(new Step(new StepId(1),'this is first title', false,1)), 
		];
		$procedure = new Procedure(
			new ProcedureId(1), 
			new InitiatorId(1234567890), 
			'this is the procedure title', 
			$steps_arr[0], 
			ProcedureType::ConstructionPermit(),
			false // this will make it return false.
			);

		$confirm_returns_false = $procedure->isRepeatable();
		$this->assertFalse($confirm_returns_false);
	}


	public function test_If_comment_Method_Returns_Comment_With_Defined_StepId(){

		$steps_arr = [
			array(new Step(new StepId(1),'this is first title', false,1)), 
			array(new Step(new StepId(2), 'this is second title', true,1))
		];

		$procedure = new Procedure(
			new ProcedureId(1), 
			new InitiatorId(1234567890), 
			'this is the procedure title', 
			$steps_arr[1], 
			ProcedureType::ConstructionPermit(),
			true
			);

		$return_comment = $procedure->comment(new StepId(2), new CommentId(1), new PersonnelId(1), 'this is the comment message');
		$this->assertEquals(new StepId(2), $return_comment->stepId());
	}

	public function test_If_addAttachment_Returns_Attachment_With_Defined_StepId(){

		$steps_arr = [
			array(new Step(new StepId(1),'this is first title', false,1)), 
			array(new Step(new StepId(2), 'this is second title', true,1))
		];

		$procedure = new Procedure(
			new ProcedureId(1), 
			new InitiatorId(1234567890), 
			'this is the procedure title', 
			$steps_arr[1], 
			ProcedureType::ConstructionPermit(),
			true
			);

		$return_attachment = $procedure->addAttachment(new StepId(2), new AttachmentId(1), new PersonnelId(1), 'base64','attachment name');
		$this->assertEquals(new StepId(2), $return_attachment->stepId());
	}

	public function test_If_advance_Method_Sorts_Existing_Steps(){

		$steps_arr = [
			array(new Step(new StepId(1),'this is first title', true,1)), 
			array(new Step(new StepId(2), 'this is second title', false,1))
		];

		$procedure = new Procedure(
			new ProcedureId(1), 
			new InitiatorId(1234567890), 
			'this is the procedure title', 
			$steps_arr[1], 
			ProcedureType::ConstructionPermit(),
			true
			);

		$procedure->advance();
		$confirm_steptwo_completed = $procedure->isComplete();
		$this->assertTrue($confirm_steptwo_completed);


	}
}

?>