<?php

use \model\ProcedureManagement\domain\model\ProcedureFactory;
use \model\ProcedureManagement\domain\model\Procedure;
use \model\ProcedureManagement\domain\model\ProcedureId;
use \model\ProcedureManagement\domain\model\InitiatorId;
use \model\ProcedureManagement\domain\model\ContainerId;
use \model\ProcedureManagement\domain\model\ProcedureType;
use \model\ProcedureManagement\domain\model\IProcedureRepository;
use \model\ProcedureManagement\domain\model\exception\InvalidProcedureTypeException;

use PHPUnit\Framework\TestCase;


class ProcedureFactoryTest extends TestCase{

	private function validProcedureFactory(){
		$procedure_repository = $this->createMock(IProcedureRepository::class);
		$procedure_repository->method('nextProcedureId')->willReturn(new ProcedureId(1) );

		return new ProcedureFactory(
			$procedure_repository
		);
	}

	public function test_If_CreateProcedureFromType_Returns_A_New_Procedure(){
		$procedure_factory = $this->validProcedureFactory();

		$new_procedure = $procedure_factory->CreateProcedureFromType(ProcedureType::Numbering(), new ContainerId(1), new InitiatorId(1234567890));

		$this->assertTrue($new_procedure->id()->equals(new ProcedureId(1)));

	}

	public function test_If_CreateProcedureFromType_Throws_Exception_When_ProcedureType_Isnt_DeconstructionPermit(){
		$this->expectException(InvalidProcedureTypeException::class);
		
		$procedure_factory = $this->validProcedureFactory();

		$new_procedure = $procedure_factory->CreateProcedureFromType(ProcedureType::EnumForFail(), new ContainerId(1), new InitiatorId(1234567890));
		// procedure type must be deconstructionPermit, this will throw new exception.
	}
}

?>