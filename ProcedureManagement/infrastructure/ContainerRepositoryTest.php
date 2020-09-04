<?php

use model\ProcedureManagement\infrastructure\ContainerRepository;
use \model\ProcedureManagement\domain\model\ContainerId;

use PHPUnit\Framework\TestCase;

class ContainerRepositoryTest extends TestCase{

	private static \DB $db;

	public static function setUpBeforeClass() : void {
    	
    	global $framework;
        $config = $framework->get('config');

        self::$db = new \DB(
            $config->get('db_procedure_management_type'),
            $config->get('db_procedure_management_hostname'),
            $config->get('db_procedure_management_username'),
            $config->get('db_procedure_management_password'),
            $config->get('db_procedure_management_database'),
            $config->get('db_procedure_management_port')
        );

       self::$db->command("DELETE FROM container");
	}

	public function test_If_find_Method_Returns_The_Container_On_Db_With_Given_Id(){

		$container_repository = new ContainerRepository(self::$db);

		self::$db->command("INSERT INTO container(id, type) VALUES ('1', '1')");

		$container_from_db = $container_repository->find(new ContainerId(1));
		$this->assertNotEmpty($container_from_db);
	}
}

?>