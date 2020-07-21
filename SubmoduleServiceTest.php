<?php


use \model\common\application\SubmoduleService;
use \model\common\domain\model\IModuleRepository;
use \model\common\domain\model\ISubmoduleRepository;
use \model\common\domain\model\SubmoduleId;
use \model\common\domain\model\Submodule;
use \model\common\domain\model\ModuleId;
use \model\common\domain\model\Module;
use \model\common\application\DTO\SubmoduleDTO;
use \model\common\application\DTO\ModuleDTO;


use PHPUnit\Framework\TestCase;


class SubmoduleServiceTest extends TestCase {

	private SubmoduleService $submodule_service;
	private Submodule $submodule;
	private Module $module;
	
	protected function setUp() : void {

		$this->submodule = new Submodule(new SubmoduleId(1), new ModuleId(1), 'submodule_1');
		$this->module = new Module(new ModuleId(1), 'module_1');

		$first_mock = $this->createMock(ISubmoduleRepository::class);
		$first_mock->method('findById')->willReturn($this->submodule);
		$first_mock->method('exists')->willReturn(true);

		$second_mock = $this->createMock(IModuleRepository::class);
		$second_mock->method('findById')->willReturn($this->module);
	
		$this->submodule_service = new SubmoduleService($first_mock, $second_mock); 
	
	}


	public function testIfGetByIdReturnsCorrectSubmodule() {

		$check_get_id = $this->submodule_service->getById(1);

		$return_submodule_id = $check_get_id->id();

		$this->assertEquals(1, $return_submodule_id);

	}

	public function testIfGetParentModuleReturnsModule() {

		$parent_module = $this->submodule_service->getParentModule(1);

		$return_parent = $parent_module->id();

		$this->assertEquals(1,$return_parent);

	}

	public function testIfExistsReturnsExistingSubmodule() {


		$check_submodule = $this->submodule_service->exists(1);

		$this->assertTrue($check_submodule);

	}

}


?>
