<?php

use \model\ProcedureManagement\domain\model\Step;
use \model\ProcedureManagement\domain\model\StepId;
use \model\ProcedureManagement\domain\model\PersonnelId;
use \model\ProcedureManagement\domain\model\CommentId;
use model\ProcedureManagement\domain\model\AttachmentId;
use \model\ProcedureManagement\domain\model\Choice;
use \model\ProcedureManagement\domain\model\ChoiceType;
use \model\ProcedureManagement\domain\model\StepTrigger;
use model\common\domain\model\DomainEvent;
use \model\ProcedureManagement\domain\model\exception\ChoiceNotFoundException;

use PHPUnit\Framework\TestCase;


class DomainEventConcrete extends DomainEvent {
	public int $event_version;
	public \DateTime $occurred_on;

	function __construct(){
		$this->event_version = 666;
		$this->occurred_on = new DateTime();
	}

	public function eventVersion() : int {
    	return $this->event_version;
    }

    public function occurredOn() : \DateTime {
        return $this->occurred_on;
    }

  	public function jsonSerialize() {
    	return get_object_vars($this);
    }

    public function data() : array {
        return $this->jsonSerialize();
    }
	
}


class StepTest extends TestCase{

	private function stepWithId($id){
		$choices_arr = array();

		return new Step(
			new StepId($id),	/* step id */
			'step_title',		/* title */
			true,				/* out of scope  */
			true,				/* is complete */
			$choices_arr, 		/* choices(array) */ 	
			null,				/* triggers(array) */
			1,					/* order */
			null 				/* activated on(datetime) */			
		);
	}



	public function test_If_comment_Function_Returns_The_Correct_Comment(){

		$step = $this->stepWithId(1);

		$confirm_msg_returned = $step->comment(new CommentId(1), new PersonnelId(1), 'this is the msg!');
		$this->assertEquals('this is the msg!', $confirm_msg_returned->message());
	}

	public function test_If_addAttachment_Returns_The_Correct_Attachment(){

		$step = $this->stepWithId(1);

		$confirm_attachment_returned = $step->addAttachment(new AttachmentId(1), new PersonnelId(1), 'base64', 'general zod');
		$this->assertEquals('general zod', $confirm_attachment_returned->name());
	}

	public function test_If_isComplete_Returns_True_When_Step_Is_Completed(){

		$step = $this->stepWithId(1); // default step 'is_complete' equals to true, this will return true.

		$confirm_step_completed = $step->isComplete();
		$this->assertTrue($confirm_step_completed);

	}

	public function test_If_isComplete_Returns_False_When_Step_Isnt_Completed(){
		$choices_arr = array();

		$step = new Step(
			new StepId(1),
			'step title',
			true,
			false,
			$choices_arr,
			null,
			1
		);

		$confirm_isnt_completed = $step->isComplete();
		$this->assertFalse($confirm_isnt_completed);
	}

	public function test_If_choose_Method_Throws_Exception_When_Choice_Isnt_Found(){
		$this->expectException(ChoiceNotFoundException::class);

		$choices_arr = array(new Choice(
			'choice message',
			new StepId(1),
			null, /* subprocedure id */
			ChoiceType::Success(),
			2
		) );

		$step = new Step(
			new StepId(1),
			'step title',
			true,
			true,
			$choices_arr,
			null,
			1
		);

		$stored_choice = $step->choose(1);
	}

	public function test_If_choose_Method_Returns_Choice_Correctly(){

		$choices_arr = array(new Choice(
			'choice message',
			new StepId(1),
			null, /* subprocedure id */
			ChoiceType::Success(),
			2
		) );

		$step = new Step(
			new StepId(1),
			'step title',
			true,
			true,
			$choices_arr,
			null,
			1
		);

		$stored_choice = $step->choose(2);

		$this->assertEquals($stored_choice->number(), 2);
		$this->assertEquals($stored_choice->message(), 'choice message');
		$this->assertEquals($stored_choice->type(), ChoiceType::Success());

	}

	public function test_If_choose_Method_Returns_Creates_Events_Correctly(){

		$choices_arr = array(new Choice(
			'choice message',
			new StepId(1),
			null, /* subprocedure id */
			ChoiceType::Success(),
			1
		) ); 

		$triggers = array(new StepTrigger(
			ChoiceType::Success(),
			new DomainEventConcrete()
		) );

		$step = new Step(
			new StepId(1),
			'step title',
			true,
			true,
			$choices_arr,
			$triggers,
			1
		);

		$stored_choice = $step->choose(1);

		$event_version = $triggers[0]->event()->eventVersion();
		$event_occurred_on = $triggers[0]->event()->occurredOn();

		$this->assertEquals($event_version, 666);
		$this->assertTrue((new DateTime())->getTimestamp() - $event_occurred_on->getTimestamp() < 5);
	}
}

?>
