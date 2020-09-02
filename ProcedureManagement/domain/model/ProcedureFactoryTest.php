<?php

use \model\ProcedureManagement\domain\model\ProcedureFactory;
use \model\ProcedureManagement\domain\model\Procedure;
use \model\ProcedureManagement\domain\model\ProcedureId;
use \model\ProcedureManagement\domain\model\InitiatorId;
use \model\ProcedureManagement\domain\model\ProcedureType;
use \model\ProcedureManagement\domain\model\IProcedureRepository;
use \model\ProcedureManagement\domain\model\exception\InvalidProcedureTypeException;

use PHPUnit\Framework\TestCase;


class ProcedureFactoryTest extends TestCase{


	public function test_If_CreateProcedureFromType_Returns_A_New_Procedure(){

		$procedure_repository = $this->createMock(IProcedureRepository::class);
		$procedure_repository->method('nextProcedureId')->willReturn(new ProcedureId(3));

		$procedure_factory = new ProcedureFactory($procedure_repository);

		$new_procedure = $procedure_factory->CreateProcedureFromType(ProcedureType::DeconstructionPermit(), new InitiatorId(1234567890));

		$this->assertTrue($new_procedure->id()->equals(new ProcedureId(3)));

	}

	public function test_If_CreateProcedureFromType_Throws_Exception_When_ProcedureType_Isnt_DeconstructionPermit(){

		$this->expectException(InvalidProcedureTypeException::class);
		
		$procedure_repository = $this->createMock(IProcedureRepository::class);
		$procedure_repository->method('nextProcedureId')->willReturn(new ProcedureId(1));

		$procedure_factory = new ProcedureFactory($procedure_repository);
		$new_procedure = $procedure_factory->CreateProcedureFromType(ProcedureType::ConstructionPermit(), new InitiatorId(1234567890));
		// procedure type must be deconstructionPermit, this will throw new exception.
	}


	public function test_If_DeconstructionPermitProcedure_Returns_A_New_Procedure(){

		$procedure_repository = $this->createMock(IProcedureRepository::class);
		$procedure_repository->method('nextProcedureId')->willReturn(new ProcedureId(24));

		$procedure_factory = new ProcedureFactory($procedure_repository);

		$new_procedure = $procedure_factory->DeconstructionPermitProcedure(new InitiatorId(1122334455));

		$this->assertTrue($new_procedure->id()->equals(new ProcedureId(24)));
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