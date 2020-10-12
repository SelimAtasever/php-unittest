<?php

use \model\common\domain\model\FormData;
use \model\common\domain\model\FormFile;

use PHPUnit\Framework\TestCase;


class FormDataTest extends TestCase{

	public function test_If_data_Method_Returns_Data_As_String(){

		$data = 'data to be returned';
		$files = [];

		$form_data = new FormData($data, $files);
		$this->assertEquals('data to be returned', $form_data->data());
	}

	public function test_If_fileFields_Number_Matches_With_Files(){

		$form_file_1 = new FormFile(
			'example name 1',
			'example type 1',
			'example tmp_name 1',
			'example error 1',
			'example size 1'
		);

		$form_file_2 = new FormFile(
			'name_2',
			'type_2',
			'tmp_name_2',
			'error_2',
			'size_2'
		);

		$data = '[{"type":"image","config":{"placeholder":"file 1","required":"true"},"file":{"name":"Screen Shot 2020-10-05 at 16.01.17.png"}},{"type":"image","config":{"placeholder":"file 2","required":"true"},"file":{"name":"Screen Shot 2020-09-17 at 18.02.34.png"}}]';

		/* data should be in this specific json format */
		$files = array($form_file_1, $form_file_2);

		$form_data = new FormData($data, $files);
		$file_fields = $form_data->fileFields();

		$this->assertEquals(count($files), count($file_fields));
	
	}

	public function test_If_files_Method_Returns_Files_Correctly(){

		$form_file_1 = new FormFile(
			'first',
			'example type 1',
			'example tmp_name 1',
			'example error 1',
			'example size 1'
		);

		$form_file_2 = new FormFile(
			'name_2',
			'type_2',
			'tmp_name_2',
			'error_2',
			'size_2'
		);

		$data = '[{"type":"image","config":{"placeholder":"file 1","required":"true"},"file":{"name":"Screen Shot 2020-10-05 at 16.01.17.png"}},{"type":"image","config":{"placeholder":"file 2","required":"true"},"file":{"name":"Screen Shot 2020-09-17 at 18.02.34.png"}}]';

		$files = array($form_file_1, $form_file_2);
		$form_data = new FormData($data, $files);

		$files = $form_data->files();

		$this->assertEquals($files[0]->name(), 'first');
		$this->assertEquals($files[1]->type(), 'type_2');
	}
}

?>