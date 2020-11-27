<?php

use \model\ProcedureManagement\application\ProcedureApplicationService;
use \model\ProcedureManagement\infrastructure\ContainerRepository;
use \model\ProcedureManagement\infrastructure\ProcedureRepository;
use \model\ProcedureManagement\infrastructure\ApplicationRepository;

use \model\ProcedureManagement\infrastructure\ApplicationFileLocator;

use \model\common\domain\model\FormData;
use PHPUnit\Framework\TestCase;

class ProcedureApplicationServiceDbTest extends TestCase{

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
       self::$db->command("DELETE FROM application");
       self::$db->command("DELETE FROM `procedure`");


	}	

	public function test_If_apply_Function_Returns_Procedure_Id(){

		$application_file_locator = new ApplicationFileLocator('./role_root_dir/');	
		$application_file_bin_locator = new ApplicationFileLocator('./role_root_bin_dir/');

		$application_repository = new ApplicationRepository(
			self::$db, $application_file_locator, $application_file_bin_locator, null
		);
		$container_repository = new ContainerRepository(self::$db);
		$procedure_repository = new ProcedureRepository(self::$db, null);

		$procedure_application_service = new ProcedureApplicationService(
			$container_repository, $procedure_repository, $application_repository
		);	

		self::$db->insert('container', array(
			'id' => 1,
			'type' => 1
		));

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

		$returned_id = $procedure_application_service->apply(
			1,  							/* container_id */
			2,								/* type */
			$initiator_data,				/* initiator_data (array) */
			new FormData('data', null)
		);

		$db_id = self::$db->query("SELECT * FROM `procedure` WHERE id = :id", array(
			':id' => $returned_id
		))->row['id'];

		$this->assertEquals($returned_id, $db_id);

		$db_title = self::$db->query("SELECT * FROM `procedure` WHERE id = :id", array(
			':id' => $returned_id
		))->row['title'];

		$this->assertEquals($db_title, 'Yapı Ruhsatı');
	}
}

?>