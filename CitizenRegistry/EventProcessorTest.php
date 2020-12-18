<?php

use model\CitizenRegistry\EventProcessor;

use \model\ProcedureManagement\domain\model\IndividualInitiatedProcedure;
use \model\CitizenRegistry\CitizenRepository;
use \model\CitizenRegistry\Citizen;
use \model\CitizenRegistry\CitizenId;
use \model\CitizenRegistry\Gender;

use PHPUnit\Framework\TestCase;

class EventProcessorTest extends TestCase{

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

       self::$db->command("DELETE FROM citizen_variation");

	}

	public function test_If_There_Isnt_A_Citizen_With_Datas_Tcno_New_Citizen_Created(){

		$data = new stdClass();
		$data->id = 1;
		$data->procedure_id = 1; 				// procedure_id
		$data->procedure_type = 1;				// procedure_type
		$data->tcno = '11223344550';			// tcno
		$data->firstname = 'Abraham';			// firstname
		$data->lastname = 'SweetVoice';			// lastname
		$data->address = 'Oxford, UK';			// address
		$data->phone = '+442198419249';			// phone

		$event_processor = new EventProcessor(
			new CitizenRepository(self::$db, null)
		);

		$event_processor->onIndividualProcedureInitiation($data);

		$new_citizen = self::$db->query("SELECT * FROM citizen WHERE tcno = :tcno", array(
			':tcno' => 11223344550
		))->row;

		$this->assertEquals($new_citizen['firstname'], 'Abraham');
		$this->assertEquals($new_citizen['lastname'], 'SweetVoice');
	}


	public function test_If_New_Registry_Of_A_Citizen_With_Existing_Tcno_Occurs_Old_Citizen_Credentials_Saved_On_Citizen_Variation(){

		$data = new stdClass();
		$data->id = 1;
		$data->procedure_id = 1; 				
		$data->procedure_type = 1;				
		$data->tcno = '11223344550';	 // same tcno, which updates citizen credentials too.		
		$data->firstname = 'İbrahim';			
		$data->lastname = 'Tatlıses';			
		$data->address = 'Urfa, Türkiye';		
		$data->phone = '+902198419249';			

		$event_processor = new EventProcessor(
			new CitizenRepository(self::$db, null)
		);

		$event_processor->onIndividualProcedureInitiation($data);

		$query = self::$db->query("SELECT * FROM citizen_variation WHERE varied_value = :varied_value", array(
			':varied_value' => 'Abraham'
		))->row;

		$this->assertEquals($query['varied_value'] , 'Abraham');
	}
}

?>