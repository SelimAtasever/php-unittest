<?php

use \model\ProcedureManagement\domain\model\Procedure;
use \model\ProcedureManagement\domain\model\ProcedureId;
use \model\ProcedureManagement\domain\model\Step;
use \model\ProcedureManagement\domain\model\StepId;
use \model\ProcedureManagement\domain\model\Container;
use \model\ProcedureManagement\domain\model\ContainerId;
use \model\ProcedureManagement\domain\model\ContainerType;
use \model\ProcedureManagement\domain\model\ProcedureType;
use \model\ProcedureManagement\domain\model\Initiator;
use \model\ProcedureManagement\domain\model\InitiatorId;
use \model\ProcedureManagement\domain\model\InitiatorType;
use \model\ProcedureManagement\domain\model\ProcedureFactory;
use \model\ProcedureManagement\domain\model\ProcedureSupportResolver;
use \model\ProcedureManagement\domain\model\ProcedureDepartmentProvider;
use \model\ProcedureManagement\domain\model\DepartmentId;
use \model\ProcedureManagement\domain\model\IProcedureRepository;
use \model\ProcedureManagement\domain\model\exception\UnsupportedProcedureException;

use \model\common\ExceptionCollection;

use PHPUnit\Framework\TestCase;


class ContainerTest extends TestCase{


	public function test_If_startProcedure_Returns_The_Procedure(){

		$procedure_support_resolver = $this->createMock(ProcedureSupportResolver::class);
		$procedure_support_resolver->method('containerSupportsProcedure')->willReturn(true);
		$choices_arr = array();

		$steps_arr = array();
		$subprocedures_arr = array();
		
		$procedures = array(
			$procedure = new Procedure(
				new ProcedureId(1), 
				new ContainerId(1),
				null, 
				'this is the procedure title', 
				$steps_arr, 
				$subprocedures_arr,
				new Step(new StepId(1), 'step_title', true, true, $choices_arr, null, 1,null),
				ProcedureType::DeconstructionPermit(),
				new DepartmentId(1)
				)
		);

		$procedure_factory = $this->createMock(ProcedureFactory::class);
		$procedure_factory->method('CreateProcedureFromType')->willReturn($procedure);

		$container = new Container(new ContainerId(1), ContainerType::Structure());

		$new_procedure = $container->startProcedure(
			ProcedureType::DeconstructionPermit(), 
			null, 
			null, 
			$procedure_factory,
			$procedure_support_resolver,
			new ProcedureDepartmentProvider(),
			$procedures
		);

		$this->assertEquals(new ProcedureId(1), $new_procedure->id());

	}


	public function test_If_startProcedure_Throws_Exception_When_Procedure_Types_Arent_Supported(){

		$this->expectException(UnsupportedProcedureException::class);

		$procedure_support_resolver = $this->createMock(ProcedureSupportResolver::class);
		$procedure_support_resolver->method('containerSupportsProcedure')->willReturn(false);
		$choices_arr = array();

		$steps_arr = array();
		$subprocedures_arr = array();
		
		$procedures = array(
			$procedure = new Procedure(
				new ProcedureId(1), 
				new ContainerId(1),
				null, 
				'this is the procedure title', 
				$steps_arr, 
				$subprocedures_arr,
				new Step(new StepId(1), 'step_title', true, true, $choices_arr, null, 1,null),
				ProcedureType::DeconstructionPermit(),
				new DepartmentId(1)
				)
		);

		$procedure_factory = $this->createMock(ProcedureFactory::class);
		$procedure_factory->method('CreateProcedureFromType')->willReturn($procedure);

		$container = new Container(new ContainerId(1), ContainerType::Structure());

		$container->startProcedure(
			ProcedureType::DeconstructionPermit(), 
			null, 
			null, 
			$procedure_factory,
			$procedure_support_resolver,
			new ProcedureDepartmentProvider(),
			$procedures
		);

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