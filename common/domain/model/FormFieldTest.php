<?php 

use \model\common\domain\model\FormField;
use \model\common\domain\model\FormFieldType;

use \model\common\domain\model\EmptyRequiredFieldException;


use PHPUnit\Framework\TestCase;


class FormFieldTest extends TestCase {

	public function test_If_Exception_Is_Thrown_When_Required_File_Name_Is_Missing(){
		$this->expectException(EmptyRequiredFieldException::class);

		$data = '{"type":"image","config":{"placeholder":"file 2","required":"true"},"file":{"name":""}}';

		$str_to_arr = json_decode($data);

		$obj = (object) $str_to_arr;

		$form_field = new FormField($obj);

	}

	public function test_If_Exception_Is_Thrown_When_Required_Value_Isnt_Given(){

		$this->expectException(EmptyRequiredFieldException::class);

		$data = '{"type":"input","value":null,"config":{"placeholder":"file 2","required":"true"},"file":{"name":"wx"}}';

		$str_to_arr = json_decode($data);

		$obj = (object) $str_to_arr;

		$form_field = new FormField($obj);

	}
}

?>