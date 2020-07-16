<?php


use \model\common\infrastructure\ModuleRepository;
use \model\common\domain\model\IModuleRepository;
use \model\common\domain\model\ModuleId;
use \model\common\domain\model\Module;

use PHPUnit\Framework\TestCase;


class ModuleRepositoryTest extends TestCase {

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
	}


	public function testIfModuleTakenFromDbMatchesFindById() {

		$module_repository = new ModuleRepository();

		self::$db->command("INSERT INTO module(id, name) VALUES('1', 'first_module')");

		$module = $module_repository->findById(new ModuleId(1));

		$this->assertTrue($module->equals(new ModuleId(1)));
	
	}
		

	public function testIfModuleIdIsNullReturnEmpty() {

		$module_repository = new ModuleRepository();

		$module = $module_repository->findById(new ModuleId(2));

		$this->assertEmpty($module);


	}


}
?>
	