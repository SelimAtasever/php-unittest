<?php  

use \model\StructureProfile\application\StructureQueryService;

use \model\StructureProfile\application\IStatementBuilder;
use \model\StructureProfile\application\SQLStatement;
use \model\StructureProfile\application\IInhabitantProvider;




use PHPUnit\Framework\TestCase;

class StructureQueryServiceTest extends TestCase{
	
	private static \DB $db;
	private static \DB $mysql;
	private $structre_query_service;

	public static function setUpBeforeClass() : void {
    	
    	global $framework;
        $config = $framework->get('config');

	        self::$db = new \DB(
	            $config->get('db_procedure_management_type'),
	            $config->get('db_procedure_management_hostname'),
	            $config->get('db_procedure_management_username'),
	            $config->get('db_procedure_management_password'),
	            $config->get('db_procedure_management_database'),
	            $config->get('db_procedure_management_port')
	        );

	       self::$db->command("DELETE FROM step");
  	    }

  	protected function setUp() : void {

  		$params = array();
  		$sql_statement = new SQLStatement('query', $params);

  		$inhabitant_provider = $this->createMock(IInhabitantProvider::class);
  		$inhabitant_provider->method('fetchInhabitantsOfIndependentSection')->willReturn('address_no');

  		$statement_builder = $this->createMock(IStatementBuilder::class);
  		$statement_builder->method('buildStatement')->willReturn($sql_statement);

  		$this->structre_query_service = new StructureQueryService(self::$db, self::$mysql, $sql_statement, $inhabitant_provider);

  	}


    public function test_If_getFeature_Returns_xx(){

    	$params = array();

    	$returned = $this->structre_query_service->getFeature(1);

   	}
		
}

?>