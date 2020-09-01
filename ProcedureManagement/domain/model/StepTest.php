<?php

use \model\ProcedureManagement\domain\model\Step;
use \model\ProcedureManagement\domain\model\StepId;
use \model\ProcedureManagement\domain\model\PersonnelId;
use \model\ProcedureManagement\domain\model\CommentId;
use model\ProcedureManagement\domain\model\AttachmentId;


use PHPUnit\Framework\TestCase;


class StepTest extends TestCase{

	public function test_If_comment_Function_Returns_The_Correct_Comment(){

		$step = new Step(
			new StepId(1),
			'step title',
			true,
			1
		);

		$confirm_msg_returned = $step->comment(new CommentId(1), new PersonnelId(1), 'this is the msg!');
		$this->assertEquals('this is the msg!', $confirm_msg_returned->message());
	}

	public function test_If_addAttachment_Returns_The_Correct_Attachment(){

		$step = new Step(
			new StepId(1),
			'step title',
			true,
			1
		);

		$confirm_attachment_returned = $step->addAttachment(new AttachmentId(1), new PersonnelId(1), 'base64', 'general zod');
		$this->assertEquals('general zod', $confirm_attachment_returned->name());
	}

	public function test_If_isComplete_Returns_True_When_Step_Is_Completed(){

		$step = new Step(
			new StepId(1),
			'step title',
			true, // this indicates that the step is completed.
			1
		);

		$confirm_step_completed = $step->isComplete();
		$this->assertTrue($confirm_step_completed);

	}

	public function test_If_isComplete_Returns_False_When_Step_Isnt_Completed(){

		$step = new Step(
			new StepId(1),
			'step title',
			false,
			1
		);

		$confirm_isnt_completed = $step->isComplete();
		$this->assertFalse($confirm_isnt_completed);
	}

	public function test_If_comlete_method_Completes_The_Step(){

		$step = new Step(
			new StepId(1),
			'step title',
			false, //this step isnt complete.
			1
		);

		$step->complete();
		$confirm_changed_to_complete = $step->isComplete();
		$this->assertTrue($confirm_changed_to_complete);
	}

	public function test_If_comesBefore_Returns_True_When_New_Step_Is_A_Following_Step(){

		$step = new Step(
			new StepId(1),
			'step title',
			true,
			1
		);

		$confirm = $step->comesBefore(new Step(
			new StepId(1),
			'new step title', 
			true,
			2
		));
		
		$this->assertTrue($confirm);
	}	
}

?>
