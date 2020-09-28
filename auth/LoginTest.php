<?php

use \model\auth\login;
use \model\auth\session;
use \model\auth\exception\PasswordLengthException;

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

    public function test_If_Login_Valid_Returns_Correct_Id() {

        $login = new login();

        $login->addLogin(1,'johndoe1@mail.com' , '123123', true);
        
        $valid_id = $login->loginIsValid('johndoe1@mail.com' , '123123');

        $this->assertEquals(1, $valid_id);

    }

     public function test_If_validLoginWithEmail_Returns_User_Info_From_Db(){

        $login = new login();

        $info = $login->validLoginWithEmail('johndoe1@mail.com');

        $id = $info['id'];        

        $db_id = self::$db->query("SELECT * FROM login WHERE id = $id")->row['id'];

        $this->assertEquals($id, $db_id);
    }


    public function test_Return_False_If_Password_Is_Wrong() { 

        $login = new login();

        $login->addLogin(2, 'johndoe2@kant.ist', '123456', true);
        
        $valid_password = $login->loginIsValid('johndoe2@kant.ist' , 'other');

        $this->assertEquals(0, $valid_password);
    }

    public function test_Return_False_If_IsValid_Email_Is_Different() {

        $login = new login();

        $validate = $login->loginIsValid('atasever@gmail.ist', '111111'); /* no existing email with such name */

        $this->assertEquals($validate, 0);
    }


    public function test_Check_If_New_Personnel_Added_To_Db() {  


        $login = new login();

        $login->addLogin(3,'example@kant.ist' , '123123', true);

        $login_db = self::$db->query("SELECT * FROM login WHERE personnel_id = '3'")->row;

        $this->assertEquals($login_db['email'], 'example@kant.ist');
        $this->assertEquals($login_db['personnel_id'], 3);

    }

    public function test_If_Date_Added_Shows_The_Time_Correctly() {

        $login = new login();

        $check_date_added = self::$db->query("SELECT date_added FROM login WHERE personnel_id = '1'")->row;

        $this->assertTrue((new \DateTime('now'))->getTimestamp() - (new DateTime($check_date_added['date_added']))->getTimestamp() < 5); 
    }

    public function test_If_New_Personnels_Password_Updated () {

        $login = new login();

        $login->addLogin(5,'newpersonnel@kant.ist' , '123323', true);

        $current_password = self::$db->query("SELECT password FROM login WHERE personnel_id = '5'")->row;

        $login->updateLogin(5, 'newpersonnel@kant.ist' , '00000000');

        $updated_password = self::$db->query("SELECT password FROM login WHERE personnel_id = '5'")->row;

        $this->assertNotEquals($current_password['password'], $updated_password['password']);
    }


    public function test_If_Last_Modification_Shows_The_Current_Time() {

        $login = new login();

        $last_mod = self::$db->query("SELECT last_modification FROM login WHERE personnel_id = '1'")->row;

        $this->assertTrue((new \DateTime('now'))->getTimestamp() - (new DateTime($last_mod['last_modification']))->getTimestamp() < 5);
    
    }


    public function test_If_deleteLogin_Deletes_Login_And_Carries_It_To_Login_Bin() {

        $login = new login();

        $login->addLogin(6,'to_be_deleted@kant.ist' , '313213', true);

        $login->deleteLogin(6);

        $check_login = self::$db->query("SELECT * FROM login WHERE personnel_id= '6'")->row;

        $this->assertEmpty($check_login);

        $personnel_id = self::$db->query("SELECT * FROM login_bin WHERE personnel_id = '6'")->row['personnel_id'];

        $this->assertEquals(6, $personnel_id);

    }


    public function test_If_Removal_Date_Is_Correct(){

        $login = new login();

        $login->addLogin(7,'kazuya8@kant.ist' , '14124012', true);

        $login->deleteLogin(7);

        $removal_check = self::$db->query("SELECT removal_date FROM login_bin WHERE personnel_id = '7'")->row;

        $this->assertTrue((new \DateTime('now'))->getTimestamp() - (new DateTime($removal_check['removal_date']))->getTimestamp() < 5);
    }

    public function test_If_deactiveLogin_Deactivates_An_Active_Login(){

        $login = new login();

        $login->addLogin(4, 'person@gmail.com', '123123', true);

        $login->deactivateLogin(4);

        $is_active = self::$db->query("SELECT * FROM login WHERE personnel_id = '4'")->row['is_active'];

        $this->assertEquals($is_active, 0); /* 0 indicates inactive user */
    }

    public function test_If_activateLogin_Activates_Inactive_Personnel(){

        $login = new login();

        $login->addLogin(6, 'inactiveuser@gmail.com', '123213', false);

        $login->activateLogin(6);

        $is_active = self::$db->query("SELECT * FROM login WHERE personnel_id = '6'")->row['is_active'];

        $this->assertEquals(1, $is_active);
    }


    public function test_addLogin_Throws_Exception_If_Password_Is_Shorter_Than_6_Characters() {

        $this->expectException(PasswordLengthException::class);

        $login = new login();

        $login->addLogin(8,'johndoe@kant.ist' , '12345', true);

    }

    public function test_addLogin_Throws_Exception_If_Password_Is_Longer_Than_32_Characters(){

        $this->expectException(PasswordLengthException::class);

        $login = new login();

        $login->addLogin(8, 'johndoe@mail.com', str_repeat('1',33), true);

    }
 }

?>