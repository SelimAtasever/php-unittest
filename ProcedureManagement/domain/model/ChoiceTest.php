<?php 

use model\ProcedureManagement\domain\model\Choice;
use model\ProcedureManagement\domain\model\ChoiceType;
use model\ProcedureManagement\domain\model\exception\InvalidChoiceNumberException;

use PHPUnit\Framework\TestCase;


class ChoiceTest extends TestCase{

	public function test_setNumber_Throws_Exception_If_Invalid_Number_Is_Chosen(){

		$this->expectException(InvalidChoiceNumberException::class);

		$choice = new Choice(
			'message',
			null, /* step id */
			null, /* procedure id */
			new ChoiceType(1),
			0  // cannot be lower than 1.
		);
	}

}

?>