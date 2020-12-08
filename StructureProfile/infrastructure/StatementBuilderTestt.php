<?php 

use \model\StructureProfile\infrastructure\StatementBuilder;
use \model\StructureProfile\application\Profile;


use PHPUnit\Framework\TestCase;

class StatementBuilderTest Extends TestCase{

	

	public function test_If_buildStatement_Returns_An_Sql_Statement_Correctly(){

		$reflection = new ReflectionClass(StatementBuilder::class);
		$statement_builder = $reflection->newInstanceWithoutConstructor(); 

		$selection = array();
		$field     = 'field'; 

		$second_ref = new ReflectionClass(StatementBuilder::class);
		$profile = $second_ref->newInstanceWithoutConstructor(); 

		$sql_statement = $statement_builder->buildStatement($selection, $field, $profile);
		var_dump($sql_statement);
	} 

}

?>