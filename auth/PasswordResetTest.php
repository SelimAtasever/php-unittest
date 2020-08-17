<?php

use \model\auth\PasswordReset;


use PHPUnit\Framework\TestCase;


class PasswordResetTest extends TestCase {

	private static \DB $db;
   	private static $config;

	public static function setUpBeforeClass() : void {

    	global $framework; //kant

        self::$config = $framework->get('config');

    	self::$db = new \DB(
            self::$config->get('db_auth_type'),
            self::$config->get('db_auth_hostname'),
            self::$config->get('db_auth_username'),
            self::$config->get('db_auth_password'),
            self::$config->get('db_auth_database'),
            self::$config->get('db_auth_port')
        );

        self::$db->command("DELETE FROM password_reset");
    }


    public function testIfReturnedTokenMatchesWithTheOneInDb() {

    	$password_reset = new PasswordReset();

    	$token = $password_reset->requestReset(1);

    	$check_db = self::$db->query("SELECT token FROM password_reset WHERE personnel_id = '1'")->row['token'];

    	$this->assertNotEmpty($check_db);

    	$this->assertEquals($token, $check_db); 
    }



    public function testIfEntryWithSameIdRemovesTheOldSavesTheNew() {

    	$password_reset = new PasswordReset();

		$check_db = self::$db->query("SELECT token FROM password_reset WHERE personnel_id = '1'")->row['token'];

		$password_reset->requestReset(1);

		$updated_db = self::$db->query("SELECT token FROM password_reset WHERE personnel_id = '1'")->row['token'];

		$this->assertNotEquals($check_db, $updated_db);
	}	



    public function testIfReturnedRequestTimeMatchesWithTheOneInDb() {

    	$password_reset = new PasswordReset();

    	$password_reset->requestReset(2);

    	$check_db = self::$db->query("SELECT request_time FROM password_reset WHERE personnel_id = '2'")->row['request_time'];

    	$converted = new DateTime($check_db);

    	$this->assertEquals((new \DateTime('now'))->format('Y-m-d H:i:s'), $converted->format('Y-m-d H:i:s'));

    	$this->assertTrue((new \DateTime('now'))->getTimestamp() - $converted->getTimestamp() < 5 );


    }


    public function testIfDbExpiresInMatchesWithDefaultExpiresIn() {

    	$password_reset = new PasswordReset();

    	$password_reset->requestReset(3);

    	$db_expire = self::$db->query("SELECT expires_in FROM password_reset WHERE personnel_id = '3' ")->row['expires_in'];

		$config_expire = self::$config->get('password_reset_duration');
    
		$this->assertEquals($db_expire, $config_expire);
    }


    public function testIfTokenDoesntExistInDbReturnFalse() {

    	$password_reset = new PasswordReset();
 
    	$false_token = $password_reset->redeemToken('afklawfafwfk');

	    $this->assertFalse($false_token);

    }


    public function testReturnFalseIfTokenIsExpired() {


    	$password_reset = new PasswordReset();

    	self::$db->command("INSERT INTO password_reset (personnel_id, token, expires_in, request_time) VALUES ('4', 'WAFawoFKFWaawofo', '5', '2020-07-20 15:57:28' )");    	

    	$check_expired_token = $password_reset->redeemToken('WAFawoFKFWaawofo');

    	$this->assertFalse($check_expired_token);

    }


    public function testIfActionIsSuccessfulReturnPersonnelId() {

    	$password_reset = new PasswordReset();

    	self::$db->command("INSERT INTO password_reset (personnel_id, token, expires_in, request_time) VALUES ('5', 'kfafmakwfwafkmaw', '7500', '" . (new DateTime('now'))->format('Y-m-d H:i:s') . "')");

    	$db_token = $password_reset->redeemToken('kfafmakwfwafkmaw');

    	$this->assertEquals( 5 , $db_token);



    }

     
	public function testIfRedeemTokenClearsTheRow() {

		$password_reset = new PasswordReset();

		self::$db->command("INSERT INTO password_reset (personnel_id, token, expires_in, request_time) VALUES ('6', 'zzzoakfowfkAWF', '7500', '2020-07-20 15:57:28' )");    	

		$password_reset->redeemToken('zzzoakfowfkAWF');

		$check_db = self::$db->query("SELECT * FROM password_reset WHERE personnel_id = '6' ")->row;

		$this->assertEmpty($check_db);


	}    

	public function testIfCancelRequestClearsTheRow() {

		$password_reset = new PasswordReset();

		self::$db->command("INSERT INTO password_reset (personnel_id, token, expires_in, request_time) VALUES ('7', 'AOWkrroawokDOWAK', '7500', '2020-07-20 15:57:28' )");    	

		$check_cleared = $password_reset->cancelRequest(7);

		$this->assertEmpty($check_cleared);
	}




}


?>