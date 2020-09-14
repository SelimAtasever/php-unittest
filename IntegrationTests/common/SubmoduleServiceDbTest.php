<?php

use \model\common\application\SubmoduleService;
use \model\common\domain\model\IModuleRepository;
use \model\common\domain\model\ISubmoduleRepository;
use \model\common\domain\model\SubmoduleId;
use \model\common\domain\model\Submodule;
use \model\common\domain\model\ModuleId;
use \model\common\domain\model\Module;
use \model\common\infrastructure\SubmoduleRepository;
use \model\common\infrastructure\ModuleRepository;

use \model\common\application\DTO\SubmoduleDTO;
use \model\common\application\DTO\ModuleDTO;

use model\common\application\exception\SubmoduleNotFoundException;
use model\common\application\exception\ModuleNotFoundException;

use PHPUnit\Framework\TestCase;
class SubmoduleServiceDbTest extends TestCase{

private static \DB $db;

 	public static function setUpBeforeClass() : void {
    	
    	global $framework;
        $config = $framework->get('config');

        self::$db = new \DB(
            $config->get('db_common_type'),
            $config->get('db_common_hostname'),
            $config->get('db_common_username'),
            $config->get('db_common_password'),
            $config->get('db_common_database'),
            $config->get('db_common_port')
        );

       self::$db->command("DELETE FROM module");
       self::$db->command("DELETE FROM submodule");

	}

	public function test_If_getById_Returns_Submodule_From_Db_Correctly(){

		$submodule_repository = new SubmoduleRepository(); // implements ISubmoduleRepository.
		$module_repository = new ModuleRepository();	  //  implements IModuleRepository.

		$submodule_service = new SubmoduleService($submodule_repository, $module_repository);

		self::$db->insert('submodule', array(
			'id' => 1,
			'module_id' => 2,
			'name' => 'submodule_name'
		));

		$returned_submodule_from_db = $submodule_service->getById(1);
		$this->assertEquals($returned_submodule_from_db->id(), 1);

	}

	public function test_If_getParentModule_Throws_Exception_When_There_Is_No_Module_On_Db(){

		$this->expectException(ModuleNotFoundException::class);
		
		$submodule_repository = new SubmoduleRepository();
		$module_repository = new ModuleRepository();

		$submodule_service = new SubmoduleService($submodule_repository, $module_repository);

		$submodule_service->getParentModule(1); 
		// Submodule with Id:1 has no parentmodule. This will throw an exception.
	}

	public function test_If_getParentModule_Throws_Exception_When_There_Is_No_Submodule_On_Db(){

		$this->expectException(SubmoduleNotFoundException::class);

		$submodule_repository = new SubmoduleRepository();
		$module_repository = new ModuleRepository();

		$submodule_service = new SubmoduleService($submodule_repository, $module_repository);

		$submodule_service->getParentModule(99);
	}

	public function test_If_getParentModule_Returns_Parent_ModuleId_From_Db_Correctly(){

		$submodule_repository = new SubmoduleRepository();
		$module_repository = new ModuleRepository();

		$submodule_service = new SubmoduleService($submodule_repository, $module_repository);

		self::$db->insert('submodule', array(
			'id' => 2,
			'module_id' => 1,
			'name' => 'new_submodule_name'
		));

		self::$db->insert('module', array(
			'id' => 1,
			'name' => 'first_module_name'
		));

		$returned_module_from_db = $submodule_service->getParentModule(2);
		$this->assertEquals($returned_module_from_db->id(), 1);
	}

	public function test_existsMethod_Returns_True_If_Submodule_With_GivenId_Is_Found(){

		$submodule_repository = new SubmoduleRepository();
		$module_repository = new ModuleRepository();

		$submodule_service = new SubmoduleService($submodule_repository, $module_repository);

		$submodule_id_one_exists = $submodule_service->exists(1);
		$this->assertTrue($submodule_id_one_exists);

		$submodule_id_two_exists = $submodule_service->exists(2);
		$this->assertTrue($submodule_id_two_exists);

		$submodule_id_three_doesnt_exists = $submodule_service->exists(3);
		$this->assertFalse($submodule_id_three_doesnt_exists);
	}
}

?>