<?php

use \model\ProcedureManagement\application\ProcedureApplicationService;
use \model\ProcedureManagement\domain\model\IContainerRepository;
use \model\ProcedureManagement\domain\model\IProcedureRepository;
use \model\ProcedureManagement\domain\model\IApplicationRepository;
use \model\ProcedureManagement\domain\model\ApplicationId;
use \model\ProcedureManagement\domain\model\Container;
use \model\ProcedureManagement\domain\model\ContainerId;
use \model\ProcedureManagement\domain\model\ContainerType;
use \model\common\domain\model\FormData;
use \model\ProcedureManagement\domain\model\Procedure;
use \model\ProcedureManagement\domain\model\ProcedureId;
use \model\ProcedureManagement\domain\model\InitiatorId;
use \model\ProcedureManagement\domain\model\ProcedureType;
use \model\ProcedureManagement\application\exception\ContainerNotFoundException;

use PHPUnit\Framework\TestCase;


class ProcedureApplicationServiceTest extends TestCase{

	public function test_If_apply_Method_Returns_The_Procedure_Id_Correctly(){ 

		$container = new Container(
			new ContainerId(1), 
			ContainerType::Structure()
		);

		$steps_arr = array();
		$procedures = array(
 			new Procedure(
			new ProcedureId(1), 
			new InitiatorId(1234567890), 
			'this is the procedure title', 
			$steps_arr, 
			ProcedureType::ConstructionPermit(),
			true)
		);

		$container_repository = $this->createMock(IContainerRepository::class);
		$container_repository->method('find')->willReturn($container);	

		$procedure_repository = $this->createMock(IProcedureRepository::class);
		$procedure_repository->method('proceduresOfContainer')->willReturn($procedures);
		$procedure_repository->method('nextProcedureId')->willReturn(new ProcedureId(1));

		$application_repository = $this->createMock(IApplicationRepository::class);
		$application_repository->method('save')->willReturn(new ApplicationId(1));

		$procedure_application_service = new ProcedureApplicationService($container_repository, $procedure_repository, $application_repository);

		$exceptions = array();

		$returned_procedure_id = $procedure_application_service->apply(
			1,
			2,
			1,
			1234567890,
			null,
			null,
			null,
			new FormData('data', null)
		);

		$this->assertEquals($returned_procedure_id, 1);

	}

	public function test_If_Throws_Exception_When_Container_Isnt_Found(){

		$this->expectException(ContainerNotFoundException::class);

		$container = null;

		$steps_arr = array();
		$procedures = array(
 			new Procedure(
			new ProcedureId(1), 
			new InitiatorId(1234567890), 
			'this is the procedure title', 
			$steps_arr, 
			ProcedureType::ConstructionPermit(),
			true)
		);

		$container_repository = $this->createMock(IContainerRepository::class);
		$container_repository->method('find')->willReturn($container);	

		$procedure_repository = $this->createMock(IProcedureRepository::class);
		$procedure_repository->method('proceduresOfContainer')->willReturn($procedures);
		$procedure_repository->method('nextProcedureId')->willReturn(new ProcedureId(1));

		$application_repository = $this->createMock(IApplicationRepository::class);
		$application_repository->method('save')->willReturn(new ApplicationId(1));

		$procedure_application_service = new ProcedureApplicationService($container_repository, $procedure_repository, $application_repository);

		$exceptions = array();

		$returned_procedure_id = $procedure_application_service->apply(
			1,
			2,
			1,
			1234567890,
			null,
			null,
			null,
			new FormData('data', null)
		);
	}
}


?>