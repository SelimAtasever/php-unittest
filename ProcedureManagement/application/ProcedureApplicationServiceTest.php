<?php

use \model\ProcedureManagement\application\ProcedureApplicationService;
use \model\ProcedureManagement\domain\model\IContainerRepository;
use \model\ProcedureManagement\domain\model\IProcedureRepository;
use \model\ProcedureManagement\domain\model\IApplicationRepository;
use \model\ProcedureManagement\domain\model\ApplicationId;
use \model\ProcedureManagement\domain\model\Container;
use \model\ProcedureManagement\domain\model\ContainerId;
use \model\ProcedureManagement\domain\model\ContainerType;
use \model\ProcedureManagement\domain\model\InitiatorType;
use \model\ProcedureManagement\domain\model\Procedure;
use \model\ProcedureManagement\domain\model\ProcedureId;
use \model\ProcedureManagement\domain\model\InitiatorId;
use \model\ProcedureManagement\domain\model\ProcedureType;
use \model\ProcedureManagement\domain\model\Step;
use \model\ProcedureManagement\domain\model\StepId;
use \model\ProcedureManagement\domain\model\DepartmentId;
use \model\common\domain\model\FormData;

use \model\ProcedureManagement\application\exception\ContainerNotFoundException;

use PHPUnit\Framework\TestCase;


class ProcedureApplicationServiceTest extends TestCase{

	public function test_If_apply_Method_Returns_The_Procedure_Id_Correctly(){ 

		$container = new Container(
			new ContainerId(1), 
			ContainerType::Structure()
		);

		$choices_arr = array();

		$steps_arr = [
			new Step(new StepId(1),'this is first title',true, true, $choices_arr, null, 1), 
			new Step(new StepId(2), 'this is second title',true, true, $choices_arr, null, 1),
			new Step(new StepId(3), 'this is third title',true,false, $choices_arr, null, 1) 			
		];

		$procedure = new Procedure(
				new ProcedureId(1), 
				new ContainerId(1), 
				null, 
				'this is the procedure title', 
				$steps_arr, 
				null,
				$steps_arr[0],
				ProcedureType::Numbering(),
				new DepartmentId(1)
			);

		$container_repository = $this->createMock(IContainerRepository::class);
		$container_repository->method('find')->willReturn($container);	

		$procedure_repository = $this->createMock(IProcedureRepository::class);
		$procedure_repository->method('find')->willReturn($procedure);
		$procedure_repository->method('nextProcedureId')->willReturn(new ProcedureId(1));

		$application_repository = $this->createMock(IApplicationRepository::class);
		$application_repository->method('save')->willReturn(new ApplicationId(1));

		$procedure_application_service = new ProcedureApplicationService($container_repository, $procedure_repository, $application_repository);

		$exceptions = array();

		$initiator_data = array(
			'type' => 1,
			'tcno' => 11223344550,
			'taxnumber' => 002,
			'address' => 'address',
			'phone' => 12491041,
			'firstname' => 'sonny',
			'lastname' => 'liston',
			'tax_office' => 'warsaw',
			'corporate_name' => 'kant',
		);

		$returned_procedure_id = $procedure_application_service->apply(
			1, 	 						/* container_id */
			2,							/* type */
			$initiator_data,			/* initiator data (array) */	
			new FormData('data', null)
		);

		$this->assertEquals($returned_procedure_id, 1);

	}

	public function test_If_Throws_Exception_When_Container_Isnt_Found(){

		$this->expectException(ContainerNotFoundException::class);

		$container = null;

		$choices_arr = array();

		$steps_arr = [
			new Step(new StepId(1),'this is first title',true, true, $choices_arr, null, 1), 
			new Step(new StepId(2), 'this is second title',true, true, $choices_arr, null, 1),
			new Step(new StepId(3), 'this is third title',true,false, $choices_arr, null, 1) 			
		];

		$procedure = new Procedure(
				new ProcedureId(1), 
				new ContainerId(1), 
				null, 
				'this is the procedure title', 
				$steps_arr, 
				null,
				$steps_arr[0],
				ProcedureType::Numbering(),
				new DepartmentId(1)
			);

		$container_repository = $this->createMock(IContainerRepository::class);
		$container_repository->method('find')->willReturn($container);	

		$procedure_repository = $this->createMock(IProcedureRepository::class);
		$procedure_repository->method('find')->willReturn($procedure);
		$procedure_repository->method('nextProcedureId')->willReturn(new ProcedureId(1));

		$application_repository = $this->createMock(IApplicationRepository::class);
		$application_repository->method('save')->willReturn(new ApplicationId(1));

		$procedure_application_service = new ProcedureApplicationService($container_repository, $procedure_repository, $application_repository);

		$exceptions = array();

		$initiator_data = array(
			'type' => 1,
			'tcno' => 11223344550,
			'taxnumber' => 002,
			'address' => 'address',
			'phone' => 12491041,
			'firstname' => 'michael',
			'lastname' => 'corleone',
			'tax_office' => 'istanbul',
			'corporate_name' => 'kant',
		);

		$returned_procedure_id = $procedure_application_service->apply(
			1,
			2,
			$initiator_data,
			new FormData('data', null)
		);
	}
}


?>