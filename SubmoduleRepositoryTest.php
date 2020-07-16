<?php

use \model\common\infrastructure\SubmoduleRepository;
use \model\common\domain\model\ModuleId;
use \model\common\domain\model\SubmoduleId;
use \model\common\domain\model\Submodule;
use \model\common\domain\model\ISubmoduleRepository;

use PHPUnit\Framework\TestCase;


class SubmoduleRepositoryTest extends TestCase {

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

   		self::$db->command("DELETE FROM submodule");
	}


	public function testIfSubmoduleOfDbMatchenWithFindById() {

		$submodule_repository = new SubmoduleRepository();

		self::$db->command("INSERT INTO submodule (id, module_id, name) VALUES ('1', '1', 'submodule_name')");

		$sub_module = $submodule_repository->findById(new SubmoduleId(1));

		$this->assertTrue($sub_module->equals(new SubmoduleId(1)));
	}

	public function testIfSubmoduleReturnsNullIfIdIsNull() {

		$submodule_repository = new SubmoduleRepository();

		$submodule = $submodule_repository->findById(new SubmoduleId(2));

		$this->assertEmpty($submodule);
	}
}

?>