
<?php

use \model\auth\login;
use \model\auth\session;


use PHPUnit\Framework\TestCase;


class DbLogin extends TestCase {


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

        self::$db->command("DELETE FROM login");
        self::$db->command("DELETE FROM login_bin");
    }


	public function testReturnFalseIfCantFindPersonnelId() {

		$login = new login();

        $login->addLogin(1, '123123', true);

		$validate = $login->loginIsValid('6', '123123');

		$this->assertFalse($validate);
	
	}

    public function testReturnTrueIfPasswordIsTrue() {

        $login = new login();

        $login->addLogin(2, '132132', true);
        
        $valid_password = $login->loginIsValid('2' , '132132');

        $this->assertTrue($valid_password);

    }

    public function testReturnFalseIfPasswordIsFalse() {

        $login = new login();
        
        $valid_password = $login->loginIsValid('1' , 'other');

        $this->assertFalse($valid_password);

    }

    public function testCheckIfNewPersonnelAddedToDb() {  


        $login = new login();

        $login->addLogin(3, '123123', true);

        $check_db = self::$db->query("SELECT * FROM login WHERE personnel_id = '3'")->row;

        $this->assertNotEmpty($check_db);


    }

    //---------returns true only when its created

    public function testIfDateAddedShowsTheCorrectTime() {

        $login = new login();

        $login->addLogin(4, '123323', true);

        $check_date_added = self::$db->query("SELECT date_added FROM login WHERE personnel_id = '4'")->row;

        $this->assertTrue((new \DateTime('now'))->getTimestamp() - (new DateTime($check_date_added['date_added']))->getTimestamp() < 5); 
    }


    /**
    * @depends testCheckIfNewPersonnelAddedToDb
    */


    public function testIfNewPersonnelsPasswordUpdated () {

        $login = new login();

        $login->addLogin(5, '123323', true);

        $current_password = self::$db->query("SELECT password FROM login WHERE personnel_id = '5'")->row;

        $login->updateLogin(5, '00000000');

        $updated_password = self::$db->query("SELECT password FROM login WHERE personnel_id = '5'")->row;

        $this->assertNotEquals($current_password['password'], $updated_password['password']);
    }


    public function testIfLastModificationShowsTheCurrentTime () {

        $login = new login();

        $login->updateLogin(3, '111111');

        $last_mod = self::$db->query("SELECT last_modification FROM login WHERE personnel_id = '3'")->row;

        $this->assertTrue((new \DateTime('now'))->getTimestamp() - (new DateTime($last_mod['last_modification']))->getTimestamp() < 5);
    
    }


    public function testWhenDataInLoginDeletedItsSavedInLoginBin() {

        $login = new login();

        $login->addLogin(6, '313213', true);

        $login->deleteLogin(6);

        $check_login = self::$db->query("SELECT * FROM login WHERE personnel_id= '6'")->row;

        $this->assertEmpty($check_login);

        $check_bin = self::$db->query("SELECT * FROM login_bin WHERE personnel_id = '6'")->row;

        $this->assertNotEmpty($check_bin);

    }

    public function testIfRemovalDateIsCorrect(){

        $login = new login();

        $login->addLogin(7, '1412412', true);

        $login->deleteLogin(7);

        $removal_check = self::$db->query("SELECT removal_date FROM login_bin WHERE personnel_id = '7'")->row;

        $this->assertEquals((new \DateTime('now'))->getTimestamp(), (new DateTime($removal_check['removal_date']))->getTimestamp());


    }
	
    public function testAddLoginPasswordCannotBeShorterThan6() {

        $this->expectException(PasswordLengthException::class);

        $login = new login();

        $login->addLogin(8, '134', true);

    }

    public function testAddLoginPasswordCannotBeLongerThan32() {

        $this->expectException(PasswordLengthException::class);

        $login = new login();

        $login->addLogin(9, str_repeat(3, 33), true);
    }


 }

?>
