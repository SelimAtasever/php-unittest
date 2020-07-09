<?php

use \model\auth\session;

use PHPUnit\Framework\TestCase;


class DbSessionTest extends TestCase {

	private static \DB $db;

    public static function setUpBeforeClass() : void {
    	global $framework;
        $config = $framework->get('config');

    	self::$db = new \DB(
            $config->get('db_auth_type'),
            $config->get('db_auth_hostname'),
            $config->get('db_auth_username'),
            $config->get('db_auth_password'),
            $config->get('db_auth_database'),
            $config->get('db_auth_port')
        );

        //self::$db->command('DELETE FROM session');
    }

	public function testStartSessionCreatesNewSession() {

		$session_controller = new Session();

		$session_controller->startSession(1, '172.100.0.55');

		$session = self::$db->query("SELECT * FROM session WHERE personnel_id = '1'")->row;

		$this->assertNotEmpty($session);	
	}


	public function testIfCreateSessionWithSameIdChangesToken(){

		$session_controller = new Session();

		$session_controller->startSession(1, '192.000.880.56');

		$session = self::$db->query("SELECT * FROM session WHERE personnel_id = '1'")->row;

		$session_controller->startSession(1, '8.8.0.0');

		$session2 = self::$db->query("SELECT * FROM session WHERE personnel_id = '1'")->row;

		$this->assertNotEquals($session['token'], $session2['token']);
	}


	public function testIfThereIsNoSessionWithGivenTokenReturnFalse() {

		$session_check = new Session();

		$success = $session_check->authenticate('aafwfıwaıfjawfıafj', '174.100.0.22');

		$this->assertFalse($success);

	}


	public function testIfThereIsASessionWithNullTokenReturnFalse() {


		$session_check = new Session();

		$session_check->startSession(2, '8.8.4.4');

		self::$db->command("UPDATE session SET token = '' WHERE personnel_id = '2'");

		$success = $session_check->authenticate('', '8.8.4.4');

		$this->assertFalse($success);

	}


	public function testIfGivenIpDoesNotMatchWithDbIpReturnFalse() {

		$session_check = new Session();

		$session_check->startSession(3 , '13.13.13.13');

		$db_token = self::$db->query("SELECT token FROM session WHERE personnel_id ='3'")->row;

		$authenticated = $session_check->authenticate($db_token['token'], '10.10.10.10');

		$this->assertFalse($authenticated);
	
	}


	public function testIfLastOperationsAndExpiresinBiggerThanCurrentTimestampReturnFalse() {

		$session_check = new Session();

		$session_check->startSession(4, '0.0.0.0');

		//UPDATE session SET last_operation = '1950.05.08' WHERE personnel_id = '4'"

		$date   = new DateTime(); 
		$new_date = date_sub($date, date_interval_create_from_date_string("3 hours"));
		$result = $new_date->format('Y-m-d H:i:s');

		self::$db->command("UPDATE session SET last_operation = :last_operation WHERE personnel_id = '4'", array(
			':last_operation' => $result
		));

		$session = self::$db->query("SELECT last_operation, expires_in, token FROM session WHERE personnel_id = '4'")->row;

		$db_expires_in = $session['expires_in'];

		$authenticated = $session_check->authenticate($session['token'], '0.0.0.0');

		$this->assertFalse($authenticated);


	}


	public function testIfAuthenticationSucceedsLastOperationMustBeEqualToTimeStamp() {

		$session_check = new Session();

		$session_check->startSession(5, '1.1.1.1');

		$last_mod = self::$db->query("SELECT last_operation FROM session WHERE personnel_id = '5'")->row;

		$last_mod = new DateTime($last_mod['last_operation']);

		$this->assertEquals((new \DateTime('now'))->format('Y-m-d H:i:s'), $last_mod->format('Y-m-d H:i:s'));

		$this->assertTrue((new \DateTime())->getTimestamp() - $last_mod->getTimestamp() < 5); 


	}

}

?>