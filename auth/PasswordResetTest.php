<?php

use \model\auth\PasswordReset;


use PHPUnit\Framework\TestCase;


class PasswordResetTest extends TestCase {

    private static \DB $db;
    private static $config;

    public static function setUpBeforeClass() : void {

        global $framework; 

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


    public function test_If_Returned_Token_Matches_With_The_One_In_Db() {

        $password_reset = new PasswordReset();

        $token = $password_reset->requestReset(1);

        $check_db = self::$db->query("SELECT token FROM password_reset WHERE personnel_id = '1'")->row['token'];

        $this->assertEquals($token, $check_db); 
    }


    public function test_If_Entry_With_Same_Id_Removes_The_Old_And_Saves_The_New() {

        $password_reset = new PasswordReset();

        $check_db = self::$db->query("SELECT token FROM password_reset WHERE personnel_id = '1'")->row['token'];

        $password_reset->requestReset(1);

        $updated_db = self::$db->query("SELECT token FROM password_reset WHERE personnel_id = '1'")->row['token'];

        $this->assertNotEquals($check_db, $updated_db);
    }   


    public function test_If_Returned_Request_Time_Matches_With_The_One_On_Db(){

        $password_reset = new PasswordReset();

        $password_reset->requestReset(2);

        $check_db = self::$db->query("SELECT request_time FROM password_reset WHERE personnel_id = '2'")->row['request_time'];

        $converted = new DateTime($check_db);

        $this->assertTrue((new \DateTime('now'))->getTimestamp() - $converted->getTimestamp() < 5 );

    }


    public function testIfDbExpiresInMatchesWithDefaultExpiresIn() {

        $password_reset = new PasswordReset();

        $password_reset->requestReset(3);

        $db_expire = self::$db->query("SELECT expires_in FROM password_reset WHERE personnel_id = '3' ")->row['expires_in'];

        $config_expire = self::$config->get('password_reset_duration');
    
        $this->assertEquals($db_expire, $config_expire);
    }


    public function test_If_Token_Doesnt_Exist_In_Db_Test_Returns_False() {

        $password_reset = new PasswordReset();
 
        $false_token = $password_reset->redeemToken('invalid token');

        $this->assertFalse($false_token);

    }

    public function test_Return_False_If_Token_Is_Expired() {


        $password_reset = new PasswordReset();

        $date   = new DateTime(); 
        $new_date = date_sub($date, date_interval_create_from_date_string("5 seconds"));
        $result = $new_date->format('Y-m-d H:i:s');

        self::$db->insert('password_reset', array(
            'personnel_id' => 4,
            'token' => 'token',
            'expires_in' => 3,
            'request_time' => $result
        ));        

        $return_value = $password_reset->redeemToken('token');
        $this->assertFalse($return_value);

    }


    public function testIfActionIsSuccessfulReturnPersonnelId() {

        $password_reset = new PasswordReset();

        self::$db->command("INSERT INTO password_reset (personnel_id, token, expires_in, request_time) VALUES ('5', 'kfafmakwfwafkmaw', '7500', '" . (new DateTime('now'))->format('Y-m-d H:i:s') . "')");

        $db_token = $password_reset->redeemToken('kfafmakwfwafkmaw');

        $this->assertEquals(5 , $db_token);

    }

     
    public function test_If_Redeem_Token_Clears_The_Row() {

        $password_reset = new PasswordReset();

        self::$db->command("INSERT INTO password_reset (personnel_id, token , expires_in, request_time) VALUES ('6', 'zzz1oakfowfkAWF', '7500', '" . (new DateTime('now'))->format('Y-m-d H:i:s') . "')");      

        $password_reset->redeemToken('zzz1oakfowfkAWF');

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