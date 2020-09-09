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

use model\common\application\exception\SubmoduleNotFoundException;
use model\common\application\exception\ModuleNotFoundException;

use PHPUnit\Framework\TestCase;


class SubmoduleServiceTest extends TestCase {

	private SubmoduleService $submodule_service;
	private ISubmoduleRepository $submodule_repository;
	private IModuleRepository $module_repository;
	
	protected function setUp() : void {

		$submodule = new Submodule(new SubmoduleId(1), new ModuleId(1), 'submodule_1');
		$module = new Module(new ModuleId(1), 'module_1');

		$this->submodule_repository = $this->createMock(ISubmoduleRepository::class);
		$this->submodule_repository->method('findById')->willReturn($submodule);
		$this->submodule_repository->method('exists')->willReturn(true);

		$this->module_repository = $this->createMock(IModuleRepository::class);
		$this->module_repository->method('findById')->willReturn($module);

		$this->submodule_repository_null = $this->createMock(ISubmoduleRepository::class);
		$this->submodule_repository_null->method('findById')->willReturn(null);

		$this->module_repository_null = $this->createMock(IModuleRepository::class);
		$this->module_repository_null->method('findById')->willReturn(null);
		
	}


	public function testIfGetByIdReturnsCorrectSubmodule() {

		$this->submodule_service = new SubmoduleService($this->submodule_repository, $this->module_repository); 

		$check_get_id = $this->submodule_service->getById(1);

		$return_submodule_id = $check_get_id->id();

		$this->assertEquals(1, $return_submodule_id);

	}

	public function testIfGetParentModuleReturnsModule() {

		$this->submodule_service = new SubmoduleService($this->submodule_repository, $this->module_repository); 

		$parent_module = $this->submodule_service->getParentModule(1);

		$return_parent = $parent_module->id();

		$this->assertEquals(1,$return_parent);

	}

	public function testIfExistsReturnsExistingSubmodule() {

		$this->submodule_service = new SubmoduleService($this->submodule_repository, $this->module_repository); 

		$check_submodule = $this->submodule_service->exists(1);

		$this->assertTrue($check_submodule);

	}

	public function test_getParentModule_Throws_Exception_If_Submodule_Isnt_Found(){

		$this->expectException(SubmoduleNotFoundException::class);

		$this->submodule_service = new SubmoduleService($this->submodule_repository_null, $this->module_repository); 

		$this->submodule_service->getParentModule(1);

	}

	public function test_getParentModule_Throws_Exception_If_Module_Isnt_Found(){

		$this->expectException(ModuleNotFoundException::class);

		$this->submodule_service = new SubmoduleService($this->submodule_repository, $this->module_repository_null);

		$this->submodule_service->getParentModule(1);
	}

}


?>