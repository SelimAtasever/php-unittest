public function<?php 

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
use \model\IdentityAndAccess\domain\model\PersonnelId;
use \model\IdentityAndAccess\domain\model\RoleId;
use \model\IdentityAndAccess\domain\model\Personnel;

use \model\common\ExceptionCollection;

class SelimTest extends TestCase {


	 private function validPersonnelWithId(int $id) {
        return new Personnel(
            new PersonnelId($id), 
            null,
            true,
            'firstname', 
            'doe', 
            '11223344556',
            'male',
            '0049224591432', 
            'john-doe@mail.com', 
            null, 
            null
       );

   }


	public function testPersonnelEmailCantBeLongerThan64(){
		
		$this->expectException(PersonnelEmailLengthException::class);

		try{
			$personnel = $this->validPersonnelWithId(1);

			$personnel->changeEmail(str_repeat('a', 60) . '@mail.com');
			// new Personnel(new PersonnelId(1), new RoleId(1), true, 'john', 'doe', '11223344556', 'female', '0049224591432', str_repeat('a', 60) . '@mail.com', null, null);	
		}

		catch(ExceptionCollection $z) {

			$this->throwFromExceptionCollection($z, PersonnelEmailLengthException::class);
		}	
	}

	public function testPersonnelEmailCantBeShorterThan9(){
		
		$this->expectException(PersonnelEmailLengthException::class);


		try {

		$personnel = $this->validPersonnelWithId(1);
		$personnel->changeEmail('aa@o.com');
		
		//new Personnel(new PersonnelId(1), new RoleId(1), true, 'john', 'doe', '11223344556', '0049224591432', 'jo@ma.st', null, null);	
		}

		catch(ExceptionCollection $w) {

			$this->throwFromExceptionCollection($w, PersonnelEmailLengthException::class);
		}
	}


	public function testLastNameCantContainSpeacialCharacters() {

		$this->expectException(PersonnelLastnameForbiddenCharacterException::class);

		try{
		
		$personnel = $this->validPersonnelWithId(2);
		$personnel->changeLastname('do!e');

		}

		catch(ExceptionCollection $w) {

			$this->throwFromExceptionCollection($w, PersonnelLastnameForbiddenCharacterException::class);
		}
	}

	public function testTcNoCannotBeNull() {

		$this->expectException(PersonnelTcnoIsNullException::class);

		try {

		$personel = $this->validPersonnelWithId(1);
		$personel->changeTcno('');

		//new Personnel(new PersonnelId(1), new RoleId(1), true ,'john','doe', '' ,'0049224591432' , 'john-doe@mail.com', null,null);
		}
		catch (ExceptionCollection $e){

			$this->throwFromExceptionCollection($e, PersonnelTcnoIsNullException::class);
		}


	}


	public function testTcNoCantBeNanCharacter () {

		$this->expectException(PersonnelTcnoNANCharacterException::class);

		try{
		
		$personel = $this->validPersonnelWithId(1);
		$personel->changeTcno('11223344z56');

		}

		catch(ExceptionCollection $e){
			$this->throwFromExceptionCollection($e, PersonnelTcnoNANCharacterException::class);

		}
	}

	public function testLastNameCanBeChanged() {

		// $personel = new Personnel(new PersonnelId(1), new RoleId(1), true ,'john','doe', '11223344556' ,'0049224591432' , 'john-doe@mail.com', null,null);
		// $this->assertEquals('doe' , $personel->getLastname());
		// $personel->changeLastname('Joe');
		// $this->assertEquals('Joe', $personel->getLastname());

		$personel = $this->validPersonnelWithId(1);
		$personel->changeLastname('cloe');
		$this->assertEquals('cloe', $personel->getLastname());

	}

	public function testPhoneLenghCantBeShorterThan7() {

		$this->expectException(PersonnelPhoneLengthException::class);


		try{

		$personel = $this->validPersonnelWithId(1);
		$personel->changePhone('04312');


		//new Personnel(new PersonnelId(1), new RoleId(1), true ,'john','doe', '11223344556' ,'03032', 'john-doe@mail.com', null,null);
		}
		catch(ExceptionCollection $e){

			$this->throwFromExceptionCollection($e, PersonnelPhoneLengthException::class);
		}
	}



	public function testPhoneLenghCantBeLongerThan24() {

		$this->expectException(PersonnelPhoneLengthException::class);

		try{

		$personel = $this->validPersonnelWithId(2);
		$personel->changePhone(str_repeat(10, 13));

		//new Personnel(new PersonnelId(1), new RoleId(1), true ,'john','doe', '11223344556' , str_repeat(12, 13), 'john-doe@mail.com', null,null);
		}

		catch(ExceptionCollection $e){

			$this->throwFromExceptionCollection($e, PersonnelPhoneLengthException::class);
		}

	}

	public function testPhoneNumberCanChange() {

		$personel = $this->validPersonnelWithId(1);
		$personel->changePhone('533 388 6868');
		$this->assertEquals('533 388 6868', $personel->getPhone());

	}

	public function testFirstNameCanChange() {

	$personel = $this->validPersonnelWithId(1);
	$personel->changeFirstname('joe');
	$this->assertEquals('joe', $personel->getFirstname());


	}

	public function testPhoneCantContainLetters(){

		$this->ExceptionCollection(PersonnelPhoneForbiddenCharacterException::class);

		try {
		$personel = $this->validPersonnelWithId(1);
		$personel->changePhone('004x9224591432');
		}
		catch(ExceptionCollection $p){
			$this->throwFromExceptionCollection($p, PersonnelPhoneForbiddenCharacterException::class);
		}
	}


	public function testTcNoMustBe11Characters() {

		$this->expectException(PersonnelTcnoLengthException::class);

		try {
		
		$personal = $this->validPersonnelWithId(1);
		$personal->changeTcno('1122334455');

		//$personel = new Personnel(new PersonnelId(1), new RoleId(1), true ,'john','doe', '1122334455','0049224591452', 'john-doe@mail.com', null,null);
		}
		catch(ExceptionCollection $e){

		$this->throwFromExceptionCollection($e, PersonnelTcnoLengthException::class);
		$this->assertEquals('1122334455', $personel->getTcno());

		}
	}


	public function testEmailMustBeInValidFormat() {

		$this->expectException(PersonnelEmailFormatException::class);

		try {
		
			$personel = $this->validPersonnelWithId(1);
			$personel->changeEmail('con@doe@mail.com');

		//$personel = new Personnel(new PersonnelId(1), new RoleId(1), true ,'john','doe', '1122334455','0049224591452', 'john@doe@mail.com', null,null);
	  	}
	  	catch (ExceptionCollection $e) {
	  		$this->throwFromExceptionCollection($e, PersonnelEmailFormatException::class);
	  	}

	}

	public function testPhoneCannotBeEmpty() {

		$this->expectException(PersonnelPhoneIsNullException::class);

		try {
			
			$personnel = $this->validPersonnelWithId(1);
			$personnel->changePhone('');
			//$personel = new Personnel(new PersonnelId(1), new RoleId(1), true ,'john','doe', '11223344556', '' , 'john-doe@mail.com', null,null);
		}
		catch (ExceptionCollection $e) {
			$this->throwFromExceptionCollection($e, PersonnelPhoneIsNullException::class);
		}

		//$this->assertNotNull($personel->getPhone());
	}

	private function throwFromExceptionCollection($exception_collection, $exception) {
		foreach($exception_collection->getExceptions() as $e) {
			if(get_class($e) == $exception) {
				throw new $exception;
			}
		}
	}

}





