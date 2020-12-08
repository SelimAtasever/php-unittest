<?php

use model\StructureProfile\infrastructure\GeoJsonStatementBuilder;
use model\StructureProfile\application\Profile;
use model\StructureProfile\application\DTO\GeoJsonSelection;
use model\StructureProfile\application\DTO\SelectionType;

use PHPUnit\Framework\TestCase;


class GeoJsonStatementBuilderTest extends TestCase {

	private static \DB $db;
	public static function setUpBeforeClass() : void {
	
    	global $framework;
        $config = $framework->get('config');

        self::$db = new \DB(
            $config->get('db_structure_type'),
            $config->get('db_structure_hostname'),
            $config->get('db_structure_username'),
            $config->get('db_structure_password'),
            $config->get('db_structure_database'),
            $config->get('db_structure_port')
        );	

   		self::$db->command("DELETE FROM feature");

    }

	public function test_If_StatementBuilder_Returns_Sql_Statement_Correctly() {

       	self::$db->command("CREATE TABLE IF NOT EXISTS feature (
		    id SERIAL PRIMARY KEY, 
		    cad_object_id INT NULL, 
		    type VARCHAR(32) NOT NULL, 
		    geometry GEOMETRY NOT NULL  
		)"); 


		self::$db->command( "INSERT INTO feature (id, cad_object_id, type, geometry) VALUES (1, 1, 'kale', geometry(POINT(2,2)) ) ");

		$reflection = new ReflectionClass(GeoJsonStatementBuilder::class);
		$statement_builder = $reflection->newInstanceWithoutConstructor(); 
		

	   	$geojson = array(     // <- geojson in php format 
        'type' => 'Point',
        'features' => array(
            'type' => 'Feature',
            "geometry" => array(
                'type' => 'Point',
                'coordinates' => array(
                     [125.6, 10.1],
                     [125.6, 10.2]
                    )
                )
            )
        );

		$geo_json_selection = new GeoJsonSelection(1, $geojson, null);

		$selection = array($geo_json_selection, SelectionType::Point() );
		$field     = 'field'; 

		$second_ref = new ReflectionClass(Profile::class);
		$profile = $second_ref->newInstanceWithoutConstructor(); 

		$sql_statement = $statement_builder->buildStatement($geo_json_selection, $field, $profile);

		$this->assertEquals($geojson['type'], 'Point');
		$this->assertEquals($geojson['features']['geometry']['coordinates'][0][0], 125.6);
		$this->assertEquals($geojson['features']['geometry']['coordinates'][0][1], 10.1);
	}
}

?>