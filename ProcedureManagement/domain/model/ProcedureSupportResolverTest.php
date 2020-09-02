<?php

use \model\ProcedureManagement\domain\model\ProcedureSupportResolver;
use \model\ProcedureManagement\domain\model\Container;
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


	public function test_If_containerSupportsProcedure_Returns_True_When_ProcedureType_Is_ConstructionPermit(){

		$procedure_support_resolver = new ProcedureSupportResolver();
		$steps_arr = array();

		$confirm_returns_true = $procedure_support_resolver->containerSupportsProcedure( 
			new Container(
			new ContainerId(1), 
			ContainerType::Structure()), 

			$procedure = new Procedure(
			new ProcedureId(1), 
			new InitiatorId(1234567890), 
			'this is the procedure title', 
			$steps_arr, 
			ProcedureType::ConstructionPermit(),
			true)
		);

		$this->assertTrue($confirm_returns_true);
	}

	public function test_If_containerSupportsProcedure_Returns_True_When_ProcedureType_Is_DeconstructionPermit(){

		$procedure_support_resolver = new ProcedureSupportResolver();
		$steps_arr = array();

		$confirm_returns_true = $procedure_support_resolver->containerSupportsProcedure( 
			new Container(
			new ContainerId(1), 
			ContainerType::Structure()), 

			$procedure = new Procedure(
			new ProcedureId(1), 
			new InitiatorId(1234567890), 
			'this is the procedure title', 
			$steps_arr, 
			ProcedureType::DeconstructionPermit(),
			true)
		);

		$this->assertTrue($confirm_returns_true);
	}

	public function test_If_containerSupportsProcedure_Returns_True_When_ProcedureType_Is_RenovationPermit(){

		$procedure_support_resolver = new ProcedureSupportResolver();
		$steps_arr = array();

		$confirm_returns_true = $procedure_support_resolver->containerSupportsProcedure( 
			new Container(
			new ContainerId(1), 
			ContainerType::Structure()), 

			$procedure = new Procedure(
			new ProcedureId(1), 
			new InitiatorId(1234567890), 
			'this is the procedure title', 
			$steps_arr, 
			ProcedureType::RenovationPermit(),
			true)
		);

		$this->assertTrue($confirm_returns_true);
	}

	public function test_If_containerSupportsProcedure_Returns_True_When_ProcedureType_Is_BuildingPermit(){

		$procedure_support_resolver = new ProcedureSupportResolver();
		$steps_arr = array();

		$confirm_returns_true = $procedure_support_resolver->containerSupportsProcedure( 
			new Container(
			new ContainerId(1), 
			ContainerType::Structure()), 

			$procedure = new Procedure(
			new ProcedureId(1), 
			new InitiatorId(1234567890), 
			'this is the procedure title', 
			$steps_arr, 
			ProcedureType::BuildingPermit(),
			true)
		);

		$this->assertTrue($confirm_returns_true);
	}


	public function test_If_containerSupportsProcedure_Returns_True_When_ProcedureType_Is_UtilizationPermit(){

		$procedure_support_resolver = new ProcedureSupportResolver();
		$steps_arr = array();

		$confirm_returns_true = $procedure_support_resolver->containerSupportsProcedure( 
			new Container(
			new ContainerId(1), 
			ContainerType::Structure()), 

			$procedure = new Procedure(
			new ProcedureId(1), 
			new InitiatorId(1234567890), 
			'this is the procedure title', 
			$steps_arr, 
			ProcedureType::UtilizationPermit(),
			true)
		);

		$this->assertTrue($confirm_returns_true);
	}


	public function test_If_containerSupportsProcedure_Returns_True_When_ProcedureType_Is_PublicWorkplacePermit(){

		$procedure_support_resolver = new ProcedureSupportResolver();
		$steps_arr = array();

		$confirm_returns_true = $procedure_support_resolver->containerSupportsProcedure( 
			new Container(
			new ContainerId(1), 
			ContainerType::Workplace()), 

			$procedure = new Procedure(
			new ProcedureId(1), 
			new InitiatorId(1234567890), 
			'this is the procedure title', 
			$steps_arr, 
			ProcedureType::PublicWorkplacePermit(),
			true)
		);

		$this->assertTrue($confirm_returns_true);
	}

	public function test_If_containerSupportsProcedure_Returns_True_When_ProcedureType_Is_InsanitaryWorkplacePermit(){

		$procedure_support_resolver = new ProcedureSupportResolver();
		$steps_arr = array();

		$confirm_returns_true = $procedure_support_resolver->containerSupportsProcedure( 
			new Container(
			new ContainerId(1), 
			ContainerType::Workplace()), 

			$procedure = new Procedure(
			new ProcedureId(1), 
			new InitiatorId(1234567890), 
			'this is the procedure title', 
			$steps_arr, 
			ProcedureType::InsanitaryWorkplacePermit(),
			true)
		);

		$this->assertTrue($confirm_returns_true);
	}

	public function test_If_containerSupportsProcedure_Returns_True_When_ProcedureType_Is_SanitaryWorkplacePermit(){

		$procedure_support_resolver = new ProcedureSupportResolver();
		$steps_arr = array();

		$confirm_returns_true = $procedure_support_resolver->containerSupportsProcedure( 
			new Container(
			new ContainerId(1), 
			ContainerType::Workplace()), 

			$procedure = new Procedure(
			new ProcedureId(1), 
			new InitiatorId(1234567890), 
			'this is the procedure title', 
			$steps_arr, 
			ProcedureType::SanitaryWorkplacePermit(),
			true)
		);

		$this->assertTrue($confirm_returns_true);
	}


	// public function test_If_containerSupportResolver_Throws_Exception_When_ContainerType_Is_Invalid(){

	// 	$this->expectException(InvalidContainerTypeException::class);

	// 	try{
	// 	$procedure_support_resolver = new ProcedureSupportResolver();

	// 	$steps_arr = array();

	// 	$confirm_returns_true = $procedure_support_resolver->containerSupportsProcedure( 
	// 		new Container(
	// 		new ContainerId(1), 
	// 		ContainerType::Structure()), 

	// 		$procedure = new Procedure(
	// 		new ProcedureId(1), 
	// 		new InitiatorId(1234567890), 
	// 		'this is the procedure title', 
	// 		$steps_arr, 
	// 		ProcedureType::SanitaryWorkplacePermit(),
	// 		true)
	// 	);
		
	// 	}catch(ExceptionCollection $e){
	// 		$this->throwFromExceptionCollection($e, InvalidContainerTypeException::class);
	// 	}	 
	// }


	// private function throwFromExceptionCollection($exception_collection, $exception) {
	// 		foreach($exception_collection->getExceptions() as $e) {
	// 			if(get_class($e) == $exception) {
	// 			   throw new $exception;
	// 		}
	// 	}
	// }
}


?>