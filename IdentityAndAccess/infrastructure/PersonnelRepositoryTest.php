<?php

use \model\FileManagement\infrastructure\FileRepository;
use \model\IdentityAndAccess\domain\model\Personnel;
use \model\IdentityAndAccess\domain\model\PersonnelId;
use \model\IdentityAndAccess\domain\model\RoleId;
use \model\IdentityAndAccess\domain\model\IPersonnelRepository;
use \model\IdentityAndAccess\infrastructure\PersonnelRepository;
use \model\common\QueryObject;

use PHPUnit\Framework\TestCase;


class PersonnelRepositoryTest extends TestCase {

    private static \DB $db;

    public static function setUpBeforeClass() : void {
        
        global $framework;
        $config = $framework->get('config');

        self::$db = new \DB(
            $config->get('db_iaa_type'),
            $config->get('db_iaa_hostname'),
            $config->get('db_iaa_username'),
            $config->get('db_iaa_password'),
            $config->get('db_iaa_database'),
            $config->get('db_iaa_port')
        );

        self::$db->command("DELETE FROM personnel");
        self::$db->command("DELETE FROM personnel_bin");

    }

    private function validPersonnelWithoutId($tcno, $phone, $email){
        return new Personnel(
            null,                          /* id */
            null,                          /* role id */
            null,                          /* department id */
            true,                          /* is active */
            null,                          /* image id */
            'john',                        /* firstname */
            'doe',                         /* lastname */
            $tcno,                         /* tc num */
            'male',                        /* gender */
            $phone,                        /* phone */
            $email,                        /* email */
            null,                          /* date added */
            null                           /* last modification */
        );
    }


    public function testIfSavePersonnelAddsNewPersonnelToDb() {

        $personnel_repository = new PersonnelRepository(self::$db);
        $personnel_id = $personnel_repository->save($this->validPersonnelWithoutId(
            '01223344556', '0049224591432', 'john_doe_0@kant.ist'
        ));

        $id = $personnel_id->getId();
        $db_personnel_id = self::$db->query("SELECT * FROM personnel WHERE id = $id")->row['id'];

        $this->assertEquals($id, $db_personnel_id);
    }

    public function testIfSavePersonnelUpdatesNewPersonnelOnDb () {

        $personnel_repository = new PersonnelRepository(self::$db);

        $personnel_id = $personnel_repository->save($this->validPersonnelWithoutId(
            '11223344556', '1049224591432', 'john_doe_1@kant.ist'
        ));

        $personnel_repository->save(new Personnel(
            $personnel_id, 
            new RoleId(1),
            null, 
            true, 
            null, 
            'mary', 
            'doe', 
            '11223344557', 
            'female', 
            '0040224591432', 
            'marydoe@mail.com', 
            null, 
            null
        ));

        $updated_personnel = $personnel_repository->findById($personnel_id);

        $this->assertEquals($updated_personnel->getFirstName(), 'mary');
    }


    public function testIfRemoveDeletesPersonnelDataAndCarriesItToPersonnelBin() {

        $personnel_repository = new PersonnelRepository(self::$db);

        $personnel_id = $personnel_repository->save($this->validPersonnelWithoutId(
            '21223344556', '2049224591432', 'john_doe_2@kant.ist'
        ));

        $id = $personnel_id->getId();

        $personnel_repository->remove($personnel_id);

        $personnel_empty = self::$db->query("SELECT * FROM personnel WHERE id = $id")->row;

        $this->assertEmpty($personnel_empty);

        $personnel_bin_id = self::$db->query("SELECT * FROM personnel_bin WHERE id = $id")->row['id'];
        $this->assertEquals($id, $personnel_bin_id);
    }


    public function testIfUpdatedPersonnelCanBeCalledByEmail () {

        $personnel_repository = new PersonnelRepository(self::$db);

        $personnel_id = $personnel_repository->save($this->validPersonnelWithoutId(
            '31223344556', '3049224591432', 'john_doe_3@kant.ist'
        ));

        $personnel_repository->save(new Personnel(
            $personnel_id, 
            new RoleId(1),
            null, 
            true, 
            null, 
            'mary II', 
            'doe', 
            '31223344556', 
            'female', 
            '3049224591432', 
            'john_doe_3@kant.ist', 
            null, 
            null
        ));

        $updated_personnel = $personnel_repository->findByEmail('john_doe_3@kant.ist');
        $this->assertEquals($updated_personnel->getEmail(), 'john_doe_3@kant.ist');

     }

     public function test_existsWithId_Returns_True_When_Id_Exits(){

        $personnel_repository = new PersonnelRepository(self::$db);

        $personnel_id = $personnel_repository->save($this->validPersonnelWithoutId(
            '41223344556', '4049224591432', 'john_doe_4@kant.ist'
        ));

        $check_id_exists = $personnel_repository->existsWithId($personnel_id);
        $this->assertTrue($check_id_exists);
     }


     public function testExistsWithEmailReturnsTrueIfEmailExists() {

        $personnel_repository = new PersonnelRepository(self::$db);

        $personnel_id = $personnel_repository->save($this->validPersonnelWithoutId(
            '51223344556', '5049224591432', 'john_doe_5@kant.ist'
        ));
        
        $check_email_exist = $personnel_repository->existsWithEmail('john_doe_5@kant.ist', null);
        $this->assertTrue($check_email_exist);

     }


     public function testExistsWithEmailReturnsFalseIfExistingEmailUsingExclude() {

        $personnel_repository = new PersonnelRepository(self::$db);

        $personnel_id = $personnel_repository->save($this->validPersonnelWithoutId(
            '61223344556', '6049224591432', 'john_doe_6@kant.ist'
        ));

        $check_email_exist = $personnel_repository->existsWithEmail('joedoe@mail.com', $personnel_id);
        $this->assertFalse($check_email_exist);

     }

     public function testExistsWithTcNoReturnsTrueIfTcNoExists() {

        $personnel_repository = new PersonnelRepository(self::$db);

        $personnel_id = $personnel_repository->save($this->validPersonnelWithoutId(
            '71223344556', '7049224591432', 'john_doe_7@kant.ist'
        ));

        $check_tcno_exist = $personnel_repository->existsWithTcno('71223344556', null);
        $this->assertTrue($check_tcno_exist);

    }


    public function testExistsWithTcNoReturnsFalseIfExistingTcNoUsingExclude () {

        $personnel_repository = new PersonnelRepository(self::$db);

        $personnel_id = $personnel_repository->save($this->validPersonnelWithoutId(
            '81223344556', '8049224591432', 'john_doe_8@kant.ist'
        ));

        $check_tcno_exist = $personnel_repository->existsWithTcno('81223344556', $personnel_id);

        $this->assertFalse($check_tcno_exist);
    
    }


    public function testExistsWithPhoneReturnsTrueIfPhoneExists () {

        $personnel_repository = new PersonnelRepository(self::$db);

        $personnel_id = $personnel_repository->save($this->validPersonnelWithoutId(
            '91223344556', '9049224591432', 'john_doe_9@kant.ist'
        ));

        $check_phone_exists = $personnel_repository->existsWithPhone('9049224591432', null);
        $this->assertTrue($check_phone_exists);
    }


    public function testExistsWithPhoneReturnsFalseIfExistingPhoneUsingExclude (){

        $personnel_repository = new PersonnelRepository(self::$db);

        $personnel_id = $personnel_repository->save($this->validPersonnelWithoutId(
            '92223344556', '9249224591432', 'john_doe_11@kant.ist'
        ));

         $check_phone_exists = $personnel_repository->existsWithPhone('9249224591432', $personnel_id);
         $this->assertFalse($check_phone_exists);

    }

    public function test_If_personnelCount_Returns_The_Number_Of_Personnels_On_Db(){

        $personnel_repository = new PersonnelRepository(self::$db);

        $number_of_personnels = $personnel_repository->personnelCount(new QueryObject());

        $this->assertEquals($number_of_personnels, 10);
    }


    public function test_If_fetchAll_Method_Returns_All_Personnels_On_Db_Inside_An_Array(){

        $personnel_repository = new PersonnelRepository(self::$db);

        $personnels_of_db = $personnel_repository->fetchAll(new QueryObject());

        $this->assertIsArray($personnels_of_db);
        $this->assertEquals(count($personnels_of_db), 10);
    }a
}

?>