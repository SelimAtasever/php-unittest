<?php

use \model\CitizenRegistry\CitizenRepository;
use \model\CitizenRegistry\Citizen;
use \model\CitizenRegistry\CitizenId;
use \model\CitizenRegistry\Gender;

use PHPUnit\Framework\TestCase;


class CitizenRepositoryTest extends TestCase{

	private static \DB $db;

 	public static function setUpBeforeClass() : void {
    	
    	global $framework;
        $config = $framework->get('config');

        self::$db = new \DB(
            $config->get('db_citizen_registry_type'),
            $config->get('db_citizen_registry_hostname'),
            $config->get('db_citizen_registry_username'),
            $config->get('db_citizen_registry_password'),
            $config->get('db_citizen_registry_database'),
            $config->get('db_citizen_registry_port')
        );

       self::$db->command("DELETE FROM citizen");

	}

	public function test_If_save_Method_Creates_A_New_Citizen_To_The_Db(){

		$citizen_repository = new CitizenRepository(self::$db, null);

		$citizen_repository->save(new Citizen(
			new CitizenId(1),
			44134182214,
			'citizen name',
			'citizen lastname',
			Gender::Male(),
			'citizen address',
			'+41 0124021401'
		));

		$confirm_created = self::$db->query("SELECT * FROM citizen WHERE id = 1")->row['id'];
		$this->assertEquals($confirm_created, 1);
	}

	public function test_If_save_Method_Updates_Existing_Citizen_On_Db(){

		$citizen_repository = new CitizenRepository(self::$db, null);

		$citizen_repository->save(new Citizen(
			new CitizenId(1),
			44134182214,
			'updated citizen name',
			'updated citizen lastname',
			Gender::Male(),
			'updated citizen address',
			'+49 0124021401'
		));

		$confirm_updated = self::$db->query("SELECT * FROM citizen WHERE firstname = 'updated citizen name'")->row['firstname'];
		$this->assertEquals($confirm_updated, 'updated citizen name');
	}

	public function test_existsWithTcNo_Returns_True_If_Citizen_TcNo_Exists_On_Db(){

		$citizen_repository = new CitizenRepository(self::$db, null);

		$confirm_tc_exists = $citizen_repository->existsWithTcno(44134182214);
		$this->assertTrue($confirm_tc_exists);

		$confirm_fails = $citizen_repository->existsWithTcno(2104124421002);
		$this->assertFalse($confirm_fails);
	}

	public function test_If_nextId_Method_Returns_A_Unique_Id(){

		$citizen_repository = new CitizenRepository(self::$db, null);

		$unique_id = $citizen_repository->nextId();

		$this->assertNotEmpty($unique_id);
		$this->assertIsObject($unique_id);
	}
}

?>