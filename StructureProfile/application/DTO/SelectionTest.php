<?php

use model\StructureProfile\application\DTO\Selection;

use model\StructureProfile\application\exception\InvalidSelectionTypeException;
use model\StructureProfile\application\exception\InvalidSelectionObjectException;
use model\StructureProfile\application\exception\InvalidLinePointCountException;
use model\StructureProfile\application\exception\InvalidPolygonSideCountException;

use PHPUnit\Framework\TestCase;

class SelectionTest extends TestCase{

	/* testing abstract class :: static method */

	public function test_Factory_Method_Throws_An_Exception_If_Invalid_Type_Is_Provided(){
		$this->expectException(InvalidSelectionTypeException::class);

	 	$geojson = array(      
        'type' => 'None', 		
        'features' => array(
            'type' => 'Feature',
            "geometry" => array(
                'type' => 'point',
                'coordinates' => array(
                     [125.6, 10.1],
                     [125.6, 10.2]
                    )
                )
            )
        );

		$mock = $this->getMockBuilder(Selection::class)
					 ->disableOriginalConstructor()
					 ->getMockForAbstractClass();

		var_dump( $mock::Factory($geojson) ); 

	}

	public function test_Factory_Method_Throws_An_Exception_If_Selection_Is_Invalid(){
		$this->expectException(InvalidSelectionObjectException::class);

		$geojson = array(
        'type' => 'point',
        'selection' => array(
        	'latitude' => null,  //
        	'longitude' => null,  // <-- these fields cant be null/invalid 
        	'radius' => null  	  //
        ),
        'features' => array(
            'type' => 'Feature',
            "geometry" => array(
                'type' => 'point',
                'coordinates' => array(
                     [125.6, 10.1],
                     [125.6, 10.2]
                    )
                )
            )
        );

		$mock = $this->getMockBuilder(Selection::class)
					 ->disableOriginalConstructor()
					 ->getMockForAbstractClass();

		var_dump( $mock::Factory($geojson) ); 
	}


	public function test_If_Factory_Method_Returns_Selection_Type_Point_Correctly(){

		$geojson = array(
        'type' => 'point',
        'selection' => array(
        	'latitude' => '45.641158147526',
        	'longitude' => '2.0556640625005',
        	'radius' => '2.2'
        ),
        'features' => array(
            'type' => 'Feature',
            "geometry" => array(
                'type' => 'point',
                'coordinates' => array(
                     [125.6, 10.1],
                     [125.6, 10.2]
                    )
                )
            )
        );

		$selection = $this->getMockBuilder(Selection::class)
					 ->disableOriginalConstructor()
					 ->getMockForAbstractClass();

		$this->assertEquals( json_decode(json_encode( $selection::Factory($geojson) ), true)['type']  , 3 ); // enum 3 : point

	}

	public function test_If_Factory_Method_Returns_Selection_Type_Circle_Correctly(){

		$geojson = array(
        'type' => 'circle',
        'selection' => array(
        	'latitude' => '45.641158147526',
        	'longitude' => '2.0556640625005',
        	'radius' => '2.2'
        ),
        'features' => array(
            'type' => 'Feature',
            "geometry" => array(
                'type' => 'circle',
                'coordinates' => array(
                     [125.6, 10.1],
                     [125.6, 10.2]
                    )
                )
            )
        );

		$selection = $this->getMockBuilder(Selection::class)
					 ->disableOriginalConstructor()
					 ->getMockForAbstractClass();

		$this->assertEquals( json_decode(json_encode( $selection::Factory($geojson) ), true)['type']  , 0 ); // enum 0 : circle

	}


	public function test_Factory_Method_Throws_An_Exception_If_Geojson_Type_Is_Null(){
		$this->expectException(InvalidSelectionObjectException::class);

		$geojson = array(
        'type' => null,
        'selection' => 1,
        'features' => array(
            'type' => 'Feature',
            "geometry" => array(
                'type' => 'line',
                'coordinates' => array(
                     [125.6, 10.1],
                     [125.6, 10.2]
                    )
                )
            )
        );

		$selection = $this->getMockBuilder(Selection::class)
					 ->disableOriginalConstructor()
					 ->getMockForAbstractClass();

		var_dump(( json_decode(json_encode( $selection::Factory($geojson) ), true) )); 

	}

	public function test_Factory_Method_Throws_An_Exception_If_Selection_Points_Isnt_An_Array(){
		$this->expectException(InvalidSelectionObjectException::class);

		$geojson = array(
        'type' => 'line',
        'selection' => array(
        	'points' => '1.2', // point should be an array, not string.
        ),
        'features' => array(
            'type' => 'Feature',
            "geometry" => array(
                'type' => 'line',
                'coordinates' => array(
                     [125.6, 10.1],
                     [125.6, 10.2]
                    )
                )
            )
        );

		$selection = $this->getMockBuilder(Selection::class)
					 ->disableOriginalConstructor()
					 ->getMockForAbstractClass();

		var_dump(( json_decode(json_encode( $selection::Factory($geojson) ), true) )); 


	}

	public function test_If_Factory_Method_Returns_Selection_Type_Line_Have_Missing_Points(){
		$this->expectException(InvalidLinePointCountException::class);

		$geojson = array(
        'type' => 'line',
        'selection' => array(
        	'points' => array(

        		'2.1'
        	)
        ),
        'features' => array(
            'type' => 'Feature',
            "geometry" => array(
                'type' => 'line',
                'coordinates' => array(
                     [125.6, 10.1],
                     [125.6, 10.2]
                    )
                )
            )
        );

		$selection = $this->getMockBuilder(Selection::class)
					 ->disableOriginalConstructor()
					 ->getMockForAbstractClass();

		$this->assertEquals( json_decode(json_encode( $selection::Factory($geojson) ), true)['type']  , 1 ); // enum 1 : line
	}


	public function test_If_Factory_Method_Returns_Selection_Type_Line_Correctly(){

		$geojson = array(
        'type' => 'line',
        'selection' => array(
        	'points' => array( 
	    		['45.641158147526',
	        	'2.0556640625005'] ,   //  first point

	        	['45.641158147526',    // second point. (longitude & latitude inside brackets are accepted as 1 point)
	        	'2.0556640625005']
        	)
        ),
        'features' => array(
            'type' => 'Feature',
            "geometry" => array(
                'type' => 'line',
                'coordinates' => array(
                     [125.6, 10.1],
                     [125.6, 10.2]
                    )
                )
            )
        );

		$selection = $this->getMockBuilder(Selection::class)
					 ->disableOriginalConstructor()
					 ->getMockForAbstractClass();

		$this->assertEquals( json_decode(json_encode( $selection::Factory($geojson) ), true)['type']  , 1 ); // enum 1 : line

	}

	public function test_If_Factory_Method_Throws_Exception_When_Polygon_Has_Missing_Points(){
		$this->expectException(InvalidPolygonSideCountException::class);

		/* polygon cant have less than 3 points */

		$geojson = array(
        'type' => 'polygon',
        'selection' => array(
        	'points' => array( 
	    		['45.641158147526',
	        	'2.0556640625005'] ,   

	        	['45.641158147526',    
	        	'2.0556640625005']
        	)
        ),
        'features' => array(
            'type' => 'Feature',
            "geometry" => array(
                'type' => 'polygon',
                'coordinates' => array(
                     [125.6, 10.1],
                     [125.6, 10.2]
                    )
                )
            )
        );

		$selection = $this->getMockBuilder(Selection::class)
					 ->disableOriginalConstructor()
					 ->getMockForAbstractClass();

		$this->assertEquals( json_decode(json_encode( $selection::Factory($geojson) ), true)['type']  , 2 ); 

	}

	public function test_If_Factory_Method_Returns_Selection_Type_Polygon_Correctly(){

		$geojson = array(
        'type' => 'polygon',
        'selection' => array(
        	'points' => array( 
	    		['45.641158147526',
	        	'2.0556640625005'] ,   

	        	['35.141158147526',    
	        	'4.0556640625005'],

	        	['12.041158147526',    
	        	'9.0556640625005'],

	        	['99.041158147526',    
	        	'0.0556640625005']
        	)
        ),
        'features' => array(
            'type' => 'Feature',
            "geometry" => array(
                'type' => 'polygon',
                'coordinates' => array(
                     [125.6, 10.1],
                     [125.6, 10.2]
                    )
                )
            )
        );

		$selection = $this->getMockBuilder(Selection::class)
					 ->disableOriginalConstructor()
					 ->getMockForAbstractClass();

		$this->assertEquals( json_decode(json_encode( $selection::Factory($geojson) ), true)['type']  , 2 ); // enum 2 : polygon
	}
}

?>