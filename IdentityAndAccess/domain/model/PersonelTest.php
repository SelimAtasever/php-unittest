<?php 

use PHPUnit\Framework\TestCase;

use \model\IdentityAndAccess\domain\model\exception\PersonnelFirstnameIsNullException;
use \model\IdentityAndAccess\domain\model\exception\PersonnelFirstnameIsTooLongException;
use \model\IdentityAndAccess\domain\model\exception\PersonnelFirstnameForbiddenCharacterException;
use \model\IdentityAndAccess\domain\model\exception\PersonnelLastnameIsNullException;
use \model\IdentityAndAccess\domain\model\exception\PersonnelLastnameIsTooLongException;
use \model\IdentityAndAccess\domain\model\exception\PersonnelLastnameForbiddenCharacterException;
use \model\IdentityAndAccess\domain\model\exception\PersonnelTcnoIsNullException;
use \model\IdentityAndAccess\domain\model\exception\PersonnelTcnoLengthException;
use \model\IdentityAndAccess\domain\model\exception\PersonnelTcnoNANCharacterException;
use \model\IdentityAndAccess\domain\model\exception\PersonnelPhoneIsNullException;
use \model\IdentityAndAccess\domain\model\exception\PersonnelPhoneLengthException;
use \model\IdentityAndAccess\domain\model\exception\PersonnelPhoneForbiddenCharacterException;
use \model\IdentityAndAccess\domain\model\exception\PersonnelEmailIsNullException;
use \model\IdentityAndAccess\domain\model\exception\PersonnelEmailLengthException;
use \model\IdentityAndAccess\domain\model\exception\PersonnelEmailFormatException;
use \model\IdentityAndAccess\domain\model\exception\PersonnelInvalidGenderException;

use \model\IdentityAndAccess\domain\model\PersonnelId;
use \model\IdentityAndAccess\domain\model\RoleId;
use \model\IdentityAndAccess\domain\model\Personnel;
use model\IdentityAndAccess\domain\model\Department;
use model\IdentityAndAccess\domain\model\DepartmentId;


use \model\common\ExceptionCollection;

class PersonnelTest extends TestCase {


	private function validPersonnelWithId($id){
        return new Personnel(
            new PersonnelId($id),		   /* id */
            null,					       /* role id */
            null,                          /* department id */
            true,                          /* is active */
            null,                          /* image id */
            'john',                        /* firstname */
            'doe',                         /* lastname */
            11223344550,                   /* tc num */
            'male',                        /* gender */
            '+90 29104091248',             /* phone */
            'john_doe@kant.ist',		   /* email */
            null,                          /* date added */
            null                           /* last modification */
        );
    }

	public function test_If_changeEmail_Throws_Exception_When_Email_Is_Too_Long(){
		
		$this->expectException(PersonnelEmailLengthException::class);

		try{
			$personnel = $this->validPersonnelWithId(1);
			$personnel->changeEmail(str_repeat('a', 60) . '@mail.com');
		}

		catch(ExceptionCollection $z) {
			$this->throwFromExceptionCollection($z, PersonnelEmailLengthException::class);
		}	
	}


	public function test_If_changeEmail_Throws_Exception_When_Email_Is_Too_Short(){
		
		$this->expectException(PersonnelEmailLengthException::class);

		try{
			$personnel = $this->validPersonnelWithId(1);
			$personnel->changeEmail('aa@o.com');
		}

		catch(ExceptionCollection $w) {
			$this->throwFromExceptionCollection($w, PersonnelEmailLengthException::class);
		}
	}

	public function test_changeTcno_Throws_Exception_If_Tcno_Is_Null() {

		$this->expectException(PersonnelTcnoIsNullException::class);

		try{
			$personel = $this->validPersonnelWithId(1);
			$personel->changeTcno('');
		}

		catch (ExceptionCollection $e){
			$this->throwFromExceptionCollection($e, PersonnelTcnoIsNullException::class);
		}
	}

	public function test_If_changeTcno_Throws_Exception_When_Tcno_Contains_Nan_Characters() {

		$this->expectException(PersonnelTcnoNANCharacterException::class);

		try{
			$personel = $this->validPersonnelWithId(1);
			$personel->changeTcno('11223344z56');
		}

		catch(ExceptionCollection $e){
			$this->throwFromExceptionCollection($e, PersonnelTcnoNANCharacterException::class);
		}
	}

	public function test_If_changeLastname_Changes_Personnel_Lastname() {

		$personnel = $this->validPersonnelWithId(1);

		$personnel->changeLastname('cloe'); //new lastname

		$this->assertNotEquals('doe', $personnel->getLastname());
		$this->assertEquals('cloe', $personnel->getLastname());

	}

	public function test_If_changeLastname_Throws_Exception_When_Lastname_Is_Null(){

		$this->expectException(PersonnelLastnameIsNullException::class);

		try{
			$personnel = $this->validPersonnelWithId(1);	
			$personnel->changeLastname('');

		} catch(ExceptionCollection $e){
			$this->throwFromExceptionCollection($e, PersonnelLastnameIsNullException::class);
		}
	}

	public function test_changeLastname_Throws_Exception_If_Personnel_Lastname_Is_Longer_Than_64_Characters(){

		$this->expectException(PersonnelLastnameIsTooLongException::class);

		try{
			$personnel = $this->validPersonnelWithId(1);	
			$personnel->changeLastname(str_repeat('a', 65));

		} catch(ExceptionCollection $e){
			$this->throwFromExceptionCollection($e, PersonnelLastnameIsTooLongException::class);
		}
	}

	public function test_If_changePhone_Throws_Exception_When_Phone_Number_Shorter_Than_7() {

		$this->expectException(PersonnelPhoneLengthException::class);

		try{
			$personel = $this->validPersonnelWithId(1);
			$personel->changePhone('04312');
		}

		catch(ExceptionCollection $e){
			$this->throwFromExceptionCollection($e, PersonnelPhoneLengthException::class);
		}
	}

	public function test_If_changePhone_Throws_Exception_When_Phone_Number_Longer_Than_24() {

		$this->expectException(PersonnelPhoneLengthException::class);

		try{
			$personel = $this->validPersonnelWithId(2);
			$personel->changePhone(str_repeat(10, 13));
		}
		catch(ExceptionCollection $e){
			$this->throwFromExceptionCollection($e, PersonnelPhoneLengthException::class);
		}

	}

	public function test_If_changePhone_Can_Change_Phone_Number() {

		$personel = $this->validPersonnelWithId(1);

		$personel->changePhone('533 388 6868');
		$this->assertEquals('533 388 6868', $personel->getPhone());

	}

	public function test_If_changeFirstname_Changes_Personnel_Firstname() {

		$personnel = $this->validPersonnelWithId(1);

		$personnel->changeFirstname('mark');

		$this->assertNotEquals('john', $personnel->getFirstname());
		$this->assertEquals('mark', $personnel->getFirstname());
	}

	public function test_changeFirstname_Throws_Exception_If_Firstname_Is_Null(){

		$this->expectException(PersonnelFirstnameIsNullException::class);

		try{
			$personnel = $this->validPersonnelWithId(1);
			$personnel->changeFirstname('');
		
		} catch(ExceptionCollection $e){
			$this->throwFromExceptionCollection($e, PersonnelFirstnameIsNullException::class);
		}
	}

	public function test_changeFirstname_Throws_Exception_If_Firstname_Is_Longer_Than_64_Characters(){

		$this->expectException(PersonnelFirstnameIsTooLongException::class);

		try{
			$personnel = $this->validPersonnelWithId(1);
			$personnel->changeFirstname(str_repeat('z', 65));
		
		} catch(ExceptionCollection $e){
			$this->throwFromExceptionCollection($e, PersonnelFirstnameIsTooLongException::class);
		}
	}


	public function test_changePhone_Throws_Exception_If_Phone_Contains_A_Letter(){

		$this->expectException(PersonnelPhoneForbiddenCharacterException::class);

		try {
			$personel = $this->validPersonnelWithId(1);
			$personel->changePhone('004x9224591432');
		}
		catch(ExceptionCollection $p){
			$this->throwFromExceptionCollection($p, PersonnelPhoneForbiddenCharacterException::class);
		}
	}

	public function test_If_changeTcno_Throws_Exception_If_Tcno_Shorter_Than_11_Characters() {

		$this->expectException(PersonnelTcnoLengthException::class);

		try{
			$personal = $this->validPersonnelWithId(1);
			$personal->changeTcno('1122334455');
		}

		catch(ExceptionCollection $e){
		$this->throwFromExceptionCollection($e, PersonnelTcnoLengthException::class);
		}
	}

	public function test_If_changeTcno_Throws_Exception_If_Tcno_Longer_Than_11_Characters() {

		$this->expectException(PersonnelTcnoLengthException::class);

		try{
			$personal = $this->validPersonnelWithId(1);
			$personal->changeTcno('112233445500');
		}

		catch(ExceptionCollection $e){
		$this->throwFromExceptionCollection($e, PersonnelTcnoLengthException::class);
		}
	}

	public function test_If_changeEmail_Throws_Exception_When_Email_Is_In_Invalid_Format() {

		$this->expectException(PersonnelEmailFormatException::class);

		try{
			$personel = $this->validPersonnelWithId(1);
			$personel->changeEmail('con@doe@mail.com');
	  	}

	  	catch (ExceptionCollection $e) {
	  		$this->throwFromExceptionCollection($e, PersonnelEmailFormatException::class);
	  	}
	}

	public function test_If_changePhone_Throws_Exception_When_Phone_Number_Is_Null() {

		$this->expectException(PersonnelPhoneIsNullException::class);

		try {
			$personnel = $this->validPersonnelWithId(1);
			$personnel->changePhone('');
		}

		catch (ExceptionCollection $e) {
			$this->throwFromExceptionCollection($e, PersonnelPhoneIsNullException::class);
		}
	}

	public function test_If_changeGender_Changes_Personnel_Gender(){

		$personnel = $this->validPersonnelWithId(1);
		$personnel->changeGender('female');

		$this->assertNotEquals('male', $personnel->getGender());
		$this->assertEquals('female', $personnel->getGender());
	}


	public function test_If_changeGender_Throws_Exception_When_Gender_Isnt_Valid(){

		$this->expectException(PersonnelInvalidGenderException::class);

		try{
			$personnel = $this->validPersonnelWithId(1);
			$personnel->changeGender('non binary');
		}

		catch(ExceptionCollection $e){
			$this->throwFromExceptionCollection($e, PersonnelInvalidGenderException::class);
		}
	}

	public function test_If_changeImage_Changes_ImageId(){

		$personnel = $this->validPersonnelWithId(1);

		$personnel->changeImage(5);

		$this->assertNotEquals($personnel->getImageId(), null);
		$this->assertEquals($personnel->getImageId(), 5);
	}


	public function test_If_deactive_Turns_Is_Active_To_False(){

		$personnel = $this->validPersonnelWithId(1);
		$personnel->deactivate();

		$personnel_deactivate = $personnel->isActive();
		
		$this->assertFalse($personnel_deactivate);
	}	

	public function test_If_activate_Turns_Is_Active_Back_To_True(){

		$personnel = $this->validPersonnelWithId(1);

		$personnel->deactivate();
		$personnel->activate();

		$personnel_activate = $personnel->isActive();
		
		$this->assertTrue($personnel_activate);
	}

	public function test_If_clearRole_Removes_Given_Role_Id(){

		$personnel = new Personnel(
            new PersonnelId(1),		   
            new RoleId(1),					       
            null,                      
            true,                  
            null,                 
            'john',                
            'doe',                
            11223344550,        
            'male',             
            '+90 29104091248', 
            'john_doe@kant.ist',		
            null,                   
            null                           
        );	

		$personnel->clearRole();

		$this->assertNotEquals($personnel->getRoleId(), 1);
		$this->assertNull($personnel->getRoleId());

	}


	public function test_If_assignToDepartment_Changes_DepartmentId(){

		$personnel = $this->validPersonnelWithId(1);

		$personnel->assignToDepartment(
			new Department(
				new DepartmentId(3), 
				'New Department',
				null, 
				new PersonnelId(1),
				0,
				0,
				0,
				0
		));

		$this->assertNotEquals($personnel->getDepartmentId()->getId(), null);
		$this->assertEquals($personnel->getDepartmentId()->getId(), 3);
	}


	public function test_If_clearDepartment_Removes_Appointed_Department(){

		$personnel = $this->validPersonnelWithId(1);

		$personnel->assignToDepartment(
			new Department(
				new DepartmentId(3), 
				'New Department',
				null, 
				new PersonnelId(1),
				0,
				0,
				0,
				0
		));

		$personnel->clearDepartment();

		$this->assertEquals($personnel->getDepartmentId(), null);
	
	}

	public function test_If_assignRole_Gives_New_Role_Id_To_Personnel(){

		$personnel = $this->validPersonnelWithId(1);

		$this->assertNull($personnel->getRoleId());

		$personnel->assignRole(new RoleId(2));

		$this->assertEquals($personnel->getRoleId()->getId(), 2);
	}


	public function test_isDirector_Returns_True_If_DirectorId_Matches_The_PersonnelId(){

		$personnel = $this->validPersonnelWithId(1);

		$confirm_director = $personnel->isDirector(
			
			new Department(
				new DepartmentId(3), 
				'New Department',
				null, 
				new PersonnelId(1),
				0,
				0,
				0,
				0
		));

		$this->assertTrue($confirm_director);
	}

	public function test_If_equals_Method_Returns_True_If_Personnels_Match(){

		$personnel = $this->validPersonnelWithId(1);

		$personnel2 = $this->validPersonnelWithId(1);

		$confirm_equals = $personnel->equals($personnel2);

		$this->assertTrue($confirm_equals);

	}

	private function throwFromExceptionCollection($exception_collection, $exception) {
		foreach($exception_collection->getExceptions() as $e) {
			if(get_class($e) == $exception) {
				throw new $exception;
			}
		}
	}

}





