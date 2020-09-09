<?php

use \model\CorporationRegistry\CorporationRepository;
use \model\CorporationRegistry\Corporation;
use \model\CorporationRegistry\CorporationId;
use \model\common\IOperationReporter;

use PHPUnit\Framework\TestCase;

class CorporationRepositoryTest extends TestCase{

	private static \DB $db;

 	public static function setUpBeforeClass() : void {
    	
    	global $framework;
        $config = $framework->get('config');

        self::$db = new \DB(
            $config->get('db_corporation_registry_type'),
            $config->get('db_corporation_registry_hostname'),
            $config->get('db_corporation_registry_username'),
            $config->get('db_corporation_registry_password'),
            $config->get('db_corporation_registry_database'),
            $config->get('db_corporation_registry_port')
        );

       self::$db->command("DELETE FROM corporation");

	}

	public function test_If_save_Method_Creates_A_New_Corporation_To_Db(){

		$corporation_repository = new CorporationRepository(self::$db, null);

		$corporation_repository->save(new Corporation(
			new CorporationId(1),
			'tax number',
			'tax office',
			'title',
			'address',
			'+90 0520510510'
		));

		$returned_id = self::$db->query("SELECT * FROM corporation WHERE id = 1")->row['id'];
		$this->assertEquals($returned_id, 1);
	}

	public function test_If_save_Method_Updates_Existing_Corporation_On_Db(){

		$corporation_repository = new CorporationRepository(self::$db, null);

		$corporation_repository->save(new Corporation(
			new CorporationId(1),
			12013029,
			'updated tax office',
			'updated title',
			'updated address',
			'+90 0520510510'
		));

		$changed_tax_number = self::$db->query("SELECT * FROM corporation WHERE id=1")->row['tax_number'];
		$changed_address = self::$db->query("SELECT * FROM corporation WHERE id=1")->row['address'];

		$this->assertEquals($changed_tax_number, 12013029);
		$this->assertEquals($changed_address, 'updated address');
	}

	public function test_If_existsWithTaxNumber_Returns_Corporation_Task_Number(){

		$corporation_repository = new CorporationRepository(self::$db, null);

		$returned_tax_number = $corporation_repository->existsWithTaxNumber(12013029);

		$this->assertEquals($returned_tax_number, 12013029);
	}

	public function test_If_nextId_Returns_A_New_Unique_Id(){

		$corporation_repository = new CorporationRepository(self::$db, null);

		$unique_id = $corporation_repository->nextId();

		$this->assertNotEmpty($unique_id);
	}
}

?>