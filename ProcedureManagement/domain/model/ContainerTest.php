<?php

use \model\ProcedureManagement\domain\model\Procedure;
use \model\ProcedureManagement\domain\model\ProcedureId;
use \model\ProcedureManagement\domain\model\Container;
use \model\ProcedureManagement\domain\model\ContainerId;
use \model\ProcedureManagement\domain\model\ContainerType;
use \model\ProcedureManagement\domain\model\ProcedureType;
use \model\ProcedureManagement\domain\model\Initiator;
use \model\ProcedureManagement\domain\model\InitiatorId;
use \model\ProcedureManagement\domain\model\InitiatorType;
use \model\ProcedureManagement\domain\model\ProcedureFactory;
use \model\ProcedureManagement\domain\model\ProcedureSupportResolver;
use \model\ProcedureManagement\domain\model\IProcedureRepository;
use \model\ProcedureManagement\domain\model\exception\DuplicateProcedureException;
use \model\ProcedureManagement\domain\model\exception\UnsupportedProcedureException;

use \model\common\ExceptionCollection;

use PHPUnit\Framework\TestCase;


class ContainerTest extends TestCase{

	public function test_If_startProcedure_Returns_The_Procedure(){

		$procedure_support_resolver = $this->createMock(ProcedureSupportResolver::class);
		$procedure_support_resolver->method('containerSupportsProcedure')->willReturn(true);

		$steps_arr = array();
		
		$procedures = [

		$procedure = new Procedure(
			new ProcedureId(13), 
			new InitiatorId(1234567890), 
			'this is the procedure title', 
			$steps_arr, 
			ProcedureType::DeconstructionPermit(),
			true
			)
		];

		$procedure_factory = $this->createMock(ProcedureFactory::class);
		$procedure_factory->method('CreateProcedureFromType')->willReturn($procedure);

		$container = new Container(new ContainerId(1), ContainerType::Structure());

		$new_procedure = $container->startProcedure(ProcedureType::DeconstructionPermit(), 
			new Initiator(new InitiatorId(1234567890), 
			InitiatorType::Individual(), 
			'initiator name', 
			'initiator address', 
			'+40 10341040104'), 
			$procedure_factory, $procedure_support_resolver, $procedures);

		$this->assertEquals(new ProcedureId(13), $new_procedure->id());

	}


	public function test_If_startProcedure_Throws_Exception_When_Procedure_Types_Duplicate(){

		$this->expectException(DuplicateProcedureException::class);

		$procedure_support_resolver = $this->createMock(ProcedureSupportResolver::class);
		$procedure_support_resolver->method('containerSupportsProcedure')->willReturn(true);

		$steps_arr = array();
		
		$procedures = [

		$procedure = new Procedure(
			new ProcedureId(1), 
			new InitiatorId(1234567890), 
			'this is the procedure title', 
			$steps_arr, 
			ProcedureType::DeconstructionPermit(),
			false 	// this will trigger the exception.
			)
		];

		$procedure_factory = $this->createMock(ProcedureFactory::class);
		$procedure_factory->method('CreateProcedureFromType')->willReturn($procedure);

		$container = new Container(new ContainerId(1), ContainerType::Structure());

		$container->startProcedure(ProcedureType::DeconstructionPermit(), 
			new Initiator(new InitiatorId(1234567890), 
			InitiatorType::Individual(), 
			'initiator name', 
			'initiator address', 
			'+40 10341040104'), 
			$procedure_factory, $procedure_support_resolver, $procedures);

		$exception_collection = new ExceptionCollection($container->exceptions());
		$this->throwFromExceptionCollection($exception_collection, DuplicateProcedureException::class);
	}


	public function test_If_startProcedure_Throws_Exception_When_Procedure_Types_Arent_Supported(){


		$this->expectException(UnsupportedProcedureException::class);

		$procedure_support_resolver = $this->createMock(ProcedureSupportResolver::class);
		$procedure_support_resolver->method('containerSupportsProcedure')->willReturn(false);

		$steps_arr = array();
		
		$procedures = [

		$procedure = new Procedure(
			new ProcedureId(1), 
			new InitiatorId(1234567890), 
			'this is the procedure title', 
			$steps_arr, 
			ProcedureType::DeconstructionPermit(),
			true
			)
		];

		$procedure_factory = $this->createMock(ProcedureFactory::class);
		$procedure_factory->method('CreateProcedureFromType')->willReturn($procedure);

		$container = new Container(new ContainerId(1), ContainerType::Structure());

		$container->startProcedure(ProcedureType::DeconstructionPermit(), 
			new Initiator(new InitiatorId(1234567890), 
			InitiatorType::Individual(), 
			'initiator name', 
			'initiator address', 
			'+40 10341040104'), 
			$procedure_factory, $procedure_support_resolver, $procedures);

		$exception_collection = new ExceptionCollection($container->exceptions());
		$this->throwFromExceptionCollection($exception_collection, UnsupportedProcedureException::class);
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