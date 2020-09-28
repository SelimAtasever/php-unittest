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

 }

	public function test_If_startSession_Creates_New_Session() {

		$session_controller = new Session();

		$session_controller->startSession(1, '172.100.0.55');

		$session = self::$db->query("SELECT * FROM session WHERE personnel_id = '1'")->row;

		$this->assertEquals($session['personnel_id'], 1);
	}


	public function test_If_startSession_Updates_Session_When_Its_Called_On_Same_PersonnelId(){

		$session = new Session();

		$session->startSession(2, '192.000.880.56');

		$session->startSession(2, '8.8.0.0');

		$updated_ip = self::$db->query("SELECT * FROM session WHERE personnel_id = '2'")->row['ip'];

		$this->assertEquals($updated_ip, '8.8.0.0');
	}


	public function test_If_There_Is_No_Session_With_Given_Token_Test_Return_False() {

		$session_check = new Session();

		$success = $session_check->authenticate('invalid token', '174.100.0.22');

		$this->assertFalse($success);

	}


	public function test_If_There_Is_A_Session_With_Null_Token_Return_False() {


		$session = new Session();

		$session->startSession(3, '8.8.4.4');

		self::$db->command("UPDATE session SET token = '' WHERE personnel_id = '2'");

		$fails = $session->authenticate('', '8.8.4.4');

		$this->assertFalse($fails);

	}


	public function test_If_Given_Ip_Does_Not_Match_With_Db_Ip_Return_False() {

		$session_check = new Session();

		$db_token = self::$db->query("SELECT token FROM session WHERE personnel_id ='3'")->row;

		$authenticated = $session_check->authenticate($db_token['token'], '10.10.10.10');

		$this->assertFalse($authenticated);
	
	}


	public function test_If_LastOperations_And_ExpiresIn_Bigger_Than_Current_TimestampReturnFalse() {

		$session_check = new Session();

		$session_check->startSession(4, '0.0.0.0');

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


	public function test_If_Authentication_Succeeds_LastOperation_Must_Be_Equal_To_TimeStamp() {

		$session = new Session();

		$last_mod = self::$db->query("SELECT last_operation FROM session WHERE personnel_id = '2'")->row;

		$last_mod = new DateTime($last_mod['last_operation']);

		$this->assertEquals((new DateTime())->format('Y-m-d'), $last_mod->format('Y-m-d'));

		$this->assertTrue((new DateTime())->getTimestamp() - $last_mod->getTimestamp() < 5); 

	}

	public function test_If_EndSession_Deletes_Existing_Session() {

		$session_check = new Session();

		$session_check->endSession(5);

		$check_db = self::$db->query("SELECT * FROM session WHERE personnel_id = '5'")->row;

		$this->assertEmpty($check_db);
	}
}

?>