<?php 

use \model\ProcedureManagement\domain\model\Subprocedure;
use \model\ProcedureManagement\domain\model\SubprocedureId;
use \model\ProcedureManagement\domain\model\Step;
use \model\ProcedureManagement\domain\model\StepId;
use \model\ProcedureManagement\domain\model\ProcedureId;
use \model\ProcedureManagement\domain\model\Choice;
use \model\ProcedureManagement\domain\model\ChoiceType; 

use \model\ProcedureManagement\domain\model\exception\SubprocedureNotActiveException;
use \model\ProcedureManagement\domain\model\exception\ProcedureConcludedException;
use \model\ProcedureManagement\domain\model\exception\StepNotFoundException;

use PHPUnit\Framework\TestCase;


class SubprocedureTest extends TestCase{

	public function test_If_advance_Method_Throws_An_Exception_When_Current_Step_Is_Completed(){
		$this->expectException(ProcedureConcludedException::class);

		$choices_arr = array(new Choice(
				'comment_message',
				new StepId(1),
				null,
				ChoiceType::Transition(),
				1
			)
		);

		$steps_arr = [

			new Step(new StepId(1),'this is first title',true, true, $choices_arr, null, 1), 
			new Step(new StepId(2), 'this is second title',true, true, $choices_arr, null, 1),
		];

		$subprocedure = new Subprocedure(
			new ProcedureId(2), 			/* child procedure */
			new ProcedureId(1), 			/* parent procedure */
			'subprocedure title',
			$steps_arr,
			null,			 /* current step is null, this will throw an exception. */
			true 	
		);

		$subprocedure->advance(1);

	}

	public function test_If_advance_Method_Throws_An_Exception_When_Subprocedure_Isnt_Active(){
		$this->expectException(SubprocedureNotActiveException::class);

		$choices_arr = array(new Choice(
				'comment_message',
				new StepId(1),
				null,
				ChoiceType::Transition(),
				1
			)
		);

		$steps_arr = [

			new Step(new StepId(1),'this is first title',true, true, $choices_arr, null, 1), 
			new Step(new StepId(2), 'this is second title',true, true, $choices_arr, null, 1),
			// new Step(new StepId(3), 'this is third title',true,false, $choices_arr, null, 1) 
		];

		$subprocedure = new Subprocedure(
			new ProcedureId(2), 			/* child procedure */
			new ProcedureId(1), 			/* parent procedure */
			'subprocedure title',
			$steps_arr,
			new Step(
				new StepId(2),
				'example title',
				true,
				true,			/* is_completed = true */
				$choices_arr,
				null, 			/* triggers */
				1,
				null 			/* activated_on (datetime) */
			),
			false 	/* is_active is false, this will throw an exception. */
		);

		$subprocedure->advance(1);
	
	}


	public function test_If_advance_Method_Throws_An_Exception_When_Nextstep_Isnt_Found(){
		$this->expectException(StepNotFoundException::class);

		$choices_arr = array(new Choice(
				'comment_message',
				new StepId(1),
				null,
				ChoiceType::Transition(),
				2
			)
		);

		$steps_arr = [

			// new Step(new StepId(1),'this is first title',true, true, $choices_arr, null, 1),  
			// this commented step will throw exception. Its set as next step on choices_arr

			new Step(new StepId(2), 'this is second title',true, false, $choices_arr, null, 1)
		];

		$subprocedure = new Subprocedure(
			new ProcedureId(2), 			
			new ProcedureId(1), 			
			'unique subprocedure title',
			$steps_arr,
			new Step(
				new StepId(2),
				'unique step title',
				true,
				false,
				$choices_arr,
				null, 			
				1 			
			),
			true 	
		);


		$subprocedure->advance(2);

	}


	public function test_If_advance_Method_Completes_The_Current_Step(){

		$choices_arr = array(new Choice(
				'comment_message',
				null, 					/* this null indicates there wont be a next step after current step is complete. */
				null,
				ChoiceType::Transition(),
				2
			)
		);

		$steps_arr = [

			new Step(new StepId(1),'this is first title',true, true, $choices_arr, null, 1), 
			new Step(new StepId(2), 'this is second title',true, false, $choices_arr, null, 1)
		];

		$subprocedure = new Subprocedure(
			new ProcedureId(2), 			
			new ProcedureId(1), 			
			'unique subprocedure title',
			$steps_arr,
			new Step(
				new StepId(2),
				'unique step title',
				true,
				false,
				$choices_arr,
				null, 			
				1 			
			),
			true 	
		);

		$subprocedure->advance(2);
		$this->assertTrue( $subprocedure->isComplete() );
	}
}

?>