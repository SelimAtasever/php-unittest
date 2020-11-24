<?php

use \model\ProcedureManagement\domain\model\ProcedureSupportResolver;
use \model\ProcedureManagement\domain\model\Container;
use \model\ProcedureManagement\domain\model\Step;
use \model\ProcedureManagement\domain\model\StepId;
use \model\ProcedureManagement\domain\model\DepartmentId;
use \model\ProcedureManagement\domain\model\ContainerId;
use \model\ProcedureManagement\domain\model\ContainerType;
use \model\ProcedureManagement\domain\model\ProcedureType;
use \model\ProcedureManagement\domain\model\Procedure;
use \model\ProcedureManagement\domain\model\ProcedureId;
use \model\ProcedureManagement\domain\model\InitiatorId;
use \model\ProcedureManagement\domain\model\exception\InvalidContainerTypeException;
use \model\common\ExceptionCollection;

use PHPUnit\Framework\TestCase;


class ProcedureSupportResolverTest extends TestCase{

	public function test_If_containerSupportsProcedure_Returns_True_When_ProcedureType_Is_Numbering(){

		$procedure_support_resolver = new ProcedureSupportResolver();
		$steps_arr = array();
		$subprocedures_arr = array();
		$choices_arr = array();

		$confirm_returns_true = $procedure_support_resolver->containerSupportsProcedure( 
			new Container(
				new ContainerId(1), 
				ContainerType::Structure()
			), 

			$procedure = new Procedure(
				new ProcedureId(1), 
				new ContainerId(1), 
				null, 
				'this is the procedure title', 
				$steps_arr, 
				$subprocedures_arr,
				new Step(								
					new StepId(1),							
					'step_title',
					true, 
					true,
					$choices_arr, 
					null, 
					1,
					null
				),
				ProcedureType::Numbering(),
				new DepartmentId(1)
			)
		);

		$this->assertTrue($confirm_returns_true);
	}

	public function test_If_containerSupportsProcedure_Returns_True_When_ProcedureType_Is_DeconstructionPermit(){

		$procedure_support_resolver = new ProcedureSupportResolver();
		$steps_arr = array();
		$subprocedures_arr = array();
		$choices_arr = array();

		$confirm_returns_true = $procedure_support_resolver->containerSupportsProcedure( 
			new Container(
			new ContainerId(1), 
			ContainerType::Structure()), 

			$procedure = new Procedure(
				new ProcedureId(1), 
				new ContainerId(1), 
				null, 
				'this is the procedure title', 
				$steps_arr, 
				$subprocedures_arr,
				new Step(								
					new StepId(1),							
					'step_title',
					true, 
					true,
					$choices_arr, 
					null, 
					1,
					null
				),
				ProcedureType::DeconstructionPermit(),
				new DepartmentId(1)
			)
		);

		$this->assertTrue($confirm_returns_true);
	}

	public function test_If_containerSupportsProcedure_Returns_True_When_ProcedureType_Is_ConstructionDirectionSurveying(){

		$procedure_support_resolver = new ProcedureSupportResolver();
		$steps_arr = array();
		$subprocedures_arr = array();
		$choices_arr = array();

		$confirm_returns_true = $procedure_support_resolver->containerSupportsProcedure( 
			new Container(
			new ContainerId(1), 
			ContainerType::Structure()), 

			$procedure = new Procedure(
				new ProcedureId(1), 
				new ContainerId(1), 
				null, 
				'this is the procedure title', 
				$steps_arr, 
				$subprocedures_arr,
				new Step(								
					new StepId(1),							
					'step_title',
					true, 
					true,
					$choices_arr, 
					null, 
					1,
					null
				),
				ProcedureType::ConstructionDirectionSurveying(),
				new DepartmentId(1)
			)
		);

		$this->assertTrue($confirm_returns_true);
	}

	public function test_If_containerSupportsProcedure_Returns_True_When_ProcedureType_Is_BuildingPermit(){

		$procedure_support_resolver = new ProcedureSupportResolver();
		$steps_arr = array();
		$subprocedures_arr = array();
		$choices_arr = array();

		$confirm_returns_true = $procedure_support_resolver->containerSupportsProcedure( 
			new Container(
			new ContainerId(1), 
			ContainerType::Structure()), 

			$procedure = new Procedure(
				new ProcedureId(1), 
				new ContainerId(1), 
				null, 
				'this is the procedure title', 
				$steps_arr, 
				$subprocedures_arr,
				new Step(								
					new StepId(1),							
					'step_title',
					true, 
					true,
					$choices_arr, 
					null, 
					1,
					null
				),
				ProcedureType::ConstructionDirectionSurveying(),
				new DepartmentId(1)
			)
		);

		$this->assertTrue($confirm_returns_true);
	}


	public function test_If_containerSupportsProcedure_Returns_True_When_ProcedureType_Is_Expropriation(){

		$procedure_support_resolver = new ProcedureSupportResolver();
		$steps_arr = array();
		$subprocedures_arr = array();
		$choices_arr = array();

		$confirm_returns_true = $procedure_support_resolver->containerSupportsProcedure( 
			new Container(
			new ContainerId(1), 
			ContainerType::Structure()), 

			$procedure = new Procedure(
				new ProcedureId(1), 
				new ContainerId(1), 
				null, 
				'this is the procedure title', 
				$steps_arr, 
				$subprocedures_arr,
				new Step(								
					new StepId(1),							
					'step_title',
					true, 
					true,
					$choices_arr, 
					null, 
					1,
					null
				),
				ProcedureType::ConstructionDirectionSurveying(),
				new DepartmentId(1)
			)
		);

		$this->assertTrue($confirm_returns_true);
	}


	public function test_If_containerSupportsProcedure_Returns_True_When_ProcedureType_Is_Parcelling(){

		$procedure_support_resolver = new ProcedureSupportResolver();
		$steps_arr = array();
		$subprocedures_arr = array();
		$choices_arr = array();

		$confirm_returns_true = $procedure_support_resolver->containerSupportsProcedure( 
			new Container(
			new ContainerId(1), 
			ContainerType::Structure()), 

			$procedure = new Procedure(
				new ProcedureId(1), 
				new ContainerId(1), 
				null, 
				'this is the procedure title', 
				$steps_arr, 
				$subprocedures_arr,
				new Step(								
					new StepId(1),							
					'step_title',
					true, 
					true,
					$choices_arr, 
					null, 
					1,
					null
				),
				ProcedureType::Parcelling(),
				new DepartmentId(1)
			)
		);

		$this->assertTrue($confirm_returns_true);
	}

	public function test_If_containerSupportsProcedure_Returns_True_When_ProcedureType_Is_PreliminaryDesign(){

		$procedure_support_resolver = new ProcedureSupportResolver();
		$steps_arr = array();
		$subprocedures_arr = array();
		$choices_arr = array();

		$confirm_returns_true = $procedure_support_resolver->containerSupportsProcedure( 
			new Container(
			new ContainerId(1), 
			ContainerType::Structure()), 

			$procedure = new Procedure(
				new ProcedureId(1), 
				new ContainerId(1), 
				null, 
				'this is the procedure title', 
				$steps_arr, 
				$subprocedures_arr,
				new Step(								
					new StepId(1),							
					'step_title',
					true, 
					true,
					$choices_arr, 
					null, 
					1,
					null
				),
				ProcedureType::PreliminaryDesign(),
				new DepartmentId(1)
			)
		);

		$this->assertTrue($confirm_returns_true);
	}

	public function test_If_containerSupportsProcedure_Returns_True_When_ProcedureType_Is_ElevationProfileSurveying(){

		$procedure_support_resolver = new ProcedureSupportResolver();
		$steps_arr = array();
		$subprocedures_arr = array();
		$choices_arr = array();

		$confirm_returns_true = $procedure_support_resolver->containerSupportsProcedure( 
			new Container(
			new ContainerId(1), 
			ContainerType::Structure()), 

			$procedure = new Procedure(
				new ProcedureId(1), 
				new ContainerId(1), 
				null, 
				'this is the procedure title', 
				$steps_arr, 
				$subprocedures_arr,
				new Step(								
					new StepId(1),							
					'step_title',
					true, 
					true,
					$choices_arr, 
					null, 
					1,
					null
				),
				ProcedureType::ElevationProfileSurveying(),
				new DepartmentId(1)
			)
		);

		$this->assertTrue($confirm_returns_true);
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