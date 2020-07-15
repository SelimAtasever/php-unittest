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


    public function testIfSavePersonnelAddsNewPersonnelToDb() {

    	$personnel_repository = new PersonnelRepository();

    	$new_personnel = $personnel_repository->save(new Personnel(null, new RoleId(1),true, 'john', 'doe', '11223344556', 'female', '0049224591432', 'johndoe@mail.com', null, null));


    	$db_check = self::$db->query("SELECT * FROM personnel WHERE id=:id", array(
    		':id' => $new_personnel->getId()
    	))->row;

    	$this->assertNotEmpty($db_check);
    }


    public function testIfSavePersonnelUpdatesNewPersonnelOnDb () {

    	$personnel_repository = new PersonnelRepository();

    	$new_personnel = $personnel_repository->save(new Personnel(null, new RoleId(1),true, 'john', 'doe', '11223344557', 'female', '0040224591432', 'ohndoe@mail.comj', null, null));

    	$personnel_repository->save(new Personnel($new_personnel, new RoleId(1),true, 'mary', 'doe', '11223344557', 'female', '0040224591432', 'marydoe@mail.com', null, null));

    	$updated_personnel = $personnel_repository->findById($new_personnel);

    	$this->assertEquals($updated_personnel->getFirstName(), 'mary');
    }


    public function testIfRemoveDeletesPersonnelDataAndCarriesItToPersonnelBin() {

    	$personnel_repository = new PersonnelRepository();

    	$new_personnel = $personnel_repository->save(new Personnel(null, new RoleId(1), true, 'will', 'smith', '11222244556', 'male', '0041224591432', 'will@mail.com',null ,null));

    	$personnel_repository->remove($new_personnel);

    	$db_check_personnel = self::$db->query("SELECT * FROM personnel WHERE id=:id", array(
    		':id' => $new_personnel->getId()
    	))->row;

    	$this->assertEmpty($db_check_personnel);

    	$db_check_bin = self::$db->query("SELECT * FROM personnel_bin WHERE id=:id", array(
    		':id' => $new_personnel->getId()
    	))->row;

    	$this->assertNotEmpty($db_check_bin);
    }


    public function testIfUpdatedPersonnelCanBeCalledByEmail () {

    	$personnel_repository = new PersonnelRepository();

    	$new_personnel = $personnel_repository->save(new Personnel(null, new RoleId(1),true, 'john', 'wick', '19223344557', 'female', '0030224591432', 'jonwick@mail.comj', null, null));

    	$personnel_repository->save(new Personnel($new_personnel, new RoleId(1),true, 'john', 'wick', '19223344557', 'female', '0030224591432', 'johnwick@mail.com', null, null));

    	$updated_personnel = $personnel_repository->findByEmail('johnwick@mail.com');

    	$this->assertEquals($updated_personnel->getEmail(), 'johnwick@mail.com');

     }


     public function testExistsWithEmailReturnsTrueIfEmailExists() {

     	$personnel_repository = new PersonnelRepository();

     	$new_personnel = $personnel_repository->save(new Personnel(null, new RoleId(1),true, 'ali', 'bezos', '00223344557', 'female', '1030224591432', 'aliali@mail.com', null, null));
     	
     	$check_email_exist = $personnel_repository->existsWithEmail('aliali@mail.com', null);

     	$this->assertTrue($check_email_exist);

     }


     public function testExistsWithEmailReturnsFalseIfExistingEmailUsingExclude() {

        $personnel_repository = new PersonnelRepository();

        $new_personnel = $personnel_repository->save(new Personnel(null, new RoleId(1),true, 'joe', 'doe', '00223354557', 'female', '1130224591432', 'joedoe@mail.com', null, null));

        $check_email_exist = $personnel_repository->existsWithEmail('joedoe@mail.com', $new_personnel);

        $this->assertFalse($check_email_exist);


     }

     public function testExistsWithTcNoReturnsTrueIfTcNoExists() {

        $personnel_repository = new PersonnelRepository();

        $new_personnel = $personnel_repository->save(new Personnel(null, new RoleId(1), true, 'zoe', 'doe', '10223344546', 'male' , '1131224591433', 'zoedoe@gmail.com', null, null));

        $check_tcno_exist = $personnel_repository->existsWithTcno('10223344546', null);

        $this->assertTrue($check_tcno_exist);

    }


    public function testExistsWithTcNoReturnsFalseIfExistingTcNoUsingExclude () {

        $personnel_repository = new PersonnelRepository();

        $new_personnel = $personnel_repository->save(new Personnel(null, new RoleId(1), true, 'can', 'doe', '12223344556', 'male', '1132214591439', 'candoe@gmail.com', null, null));

        $check_tcno_exist = $personnel_repository->existsWithTcno('12223344556', $new_personnel);

        $this->assertFalse($check_tcno_exist);
    
    }


    public function testExistsWithPhoneReturnsTrueIfPhoneExists () {

        $personnel_repository = new PersonnelRepository();

        $new_personnel = $personnel_repository->save(new Personnel(null, new RoleId(1), true, 'tio', 'doe', '02223344556', 'male', '1132214591430', 'tiodoe@gmail.com', null, null));

        $check_phone_exists = $personnel_repository->existsWithPhone('1132214591430', null);

        $this->assertTrue($check_phone_exists);
    }


    public function testExistsWithPhoneReturnsFalseIfExistingPhoneUsingExclude (){

         $personnel_repository = new PersonnelRepository();

         $new_personnel = $personnel_repository->save(new Personnel(null, new RoleId(1), true, 'mark', 'doe', '02223314556', 'female', '1932214591430','markdoe@gmail.com', null, null));

         $check_phone_exists = $personnel_repository->existsWithPhone('1932214591430', $new_personnel);

         $this->assertFalse($check_phone_exists);

    }

    public function testIfFetchAllReturnsExistingDatas() {

        //to work independent from the tests above. 
        
        self::$db->command("DELETE FROM personnel");
        self::$db->command("DELETE FROM personnel_bin");


        $personnel_repository = new PersonnelRepository();

        $new_personnel = $personnel_repository->save(new Personnel(null, new RoleId(1), true, 'jeff', 'doe', '82223314556', 'male', '1834214591430','jeffdoe@gmail.com', null, null));

        $new_personnel2 = $personnel_repository->save(new Personnel(null, new RoleId(1), true, 'matt', 'doe', '92223314556', 'male', '1932416591430','mattdoe@gmail.com', null, null));

        $fetched_users = $personnel_repository->fetchAll(new QueryObject());

        $this->assertEquals(2, count($fetched_users));
    }

}

?>