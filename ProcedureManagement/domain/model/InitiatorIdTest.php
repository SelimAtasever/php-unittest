<?php

use \model\ProcedureManagement\domain\model\Procedure;
use \model\ProcedureManagement\domain\model\ProcedureId;
use \model\ProcedureManagement\domain\model\Step;
use model\ProcedureManagement\domain\model\StepId;
use \model\ProcedureManagement\domain\model\exception\InvalidInitiatorIdentifierException;
use \model\ProcedureManagement\domain\model\InitiatorId;
use \model\common\ExceptionCollection;

use PHPUnit\Framework\TestCase;


class InitiatorIdTest extends TestCase{

	public function test_If_Exception_Thrown_When_InitiatorId_Fewer_Than_10_Numbers(){

		$this->expectException(InvalidInitiatorIdentifierException::class);

		$choices = array();
		$triggers = array();

		$steps_arr = [
			new Step(new StepId(1),'this is first title', true, true, $choices, $triggers, 1), 
			new Step(new StepId(2), 'this is second title',true, true, $choices, $triggers, 1)
		];

		$procedure = new Procedure(
			new ProcedureId(1), 
			new InitiatorId(1234567), 	// fewer than 10, should throw exception. 
			'this is the procedure title', 
			$steps_arr, 
			ProcedureType::ConstructionPermit(),
			true
			);
	}

	public function test_If_Exception_Thrown_When_InitiatorId_Longer_Than_10_Numbers(){

		$this->expectException(InvalidInitiatorIdentifierException::class);

		$choices = array();
		$triggers = array();

		$steps_arr = [
			new Step(new StepId(1),'this is first title',true, true, $choices, $triggers, 1), 
			new Step(new StepId(2), 'this is second title', true, true, $choices, $triggers, 1)
		];

		$procedure = new Procedure(
			new ProcedureId(1), 
			new InitiatorId(12345676543210), 	// longer than 11, should throw exception. 
			'this is the procedure title', 
			$steps_arr, 
			ProcedureType::ConstructionPermit(),
			true
			);
	}	
}

?>