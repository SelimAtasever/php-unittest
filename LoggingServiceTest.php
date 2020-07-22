<?php

use  \model\system\log\LoggingService;
use  \model\system\log\OperatorType;

use PHPUnit\Framework\TestCase;

class LoggingServiceTest extends TestCase {

	private static \DB $db;
   	private static $config;

	public static function setUpBeforeClass() : void {

    	global $framework; //kant

        self::$config = $framework->get('config');

    	self::$db = new \DB(
            self::$config->get('db_system_type'),
            self::$config->get('db_system_hostname'),
            self::$config->get('db_system_username'),
            self::$config->get('db_system_password'),
            self::$config->get('db_system_database'),
            self::$config->get('db_system_port')
        );


        self::$db->command("DELETE FROM log_node");
        self::$db->command("DELETE FROM log_operation");

    }


    public function testIfLogNodeDataColomnMatchesTheDataArray() {

        $login_service = new LoggingService(); 

        $stored_id = $login_service->addOperation(1, 1,'new_operation','type','1', array( 
            'name' => 'joe',
            'lastname' => 'rogan'
        ));

        $operation_row = self::$db->query("SELECT * FROM log_operation WHERE id = :id ", array(

            ':id' => $stored_id

        ))->row;


        $node_row = self::$db->query("SELECT * FROM log_node WHERE node_id = :node_id", array(

            ':node_id' => $operation_row['result']

        ))->row;

        $node_data = $node_row['data'];
        $json_to_array = (array)json_decode($node_data);

        $check = array(
            'name' => 'joe',
            'lastname' => 'rogan'
        );

        $this->assertEquals($json_to_array, $check);

    
    }


    public function testIfLogNodeTypeMatchesWithTheOneOnDb() {

        $login_service = new LoggingService(); 

        $stored_id = $login_service->addOperation(2,2,'operation2','type2','2', array( 
            'name' => 'mark',
            'lastname' => 'twain'
        ));     

        $operation_row = self::$db->query("SELECT * FROM log_operation WHERE id = :id", array(
            
            ':id' => $stored_id

        ))->row;

        $node_row = self::$db->query("SELECT * FROM log_node WHERE node_id = :node_id", array(

            'node_id' => $operation_row['result']

        ))->row;

        $node_type = $node_row['type'];

        $this->assertEquals('type2', $node_type);


    
    }


   public function testIfLogNodeVersionMatchesWithTheOneOnDb() {

        $login_service = new LoggingService();

        $stored_id = $login_service->addOperation(1, 2,'operation3','type3','3', array( 
            'name' => 'johnny',
            'lastname' => 'cash'
        ));     

        $operation_row = self::$db->query("SELECT * FROM log_operation WHERE id = :id", array(

            ':id' => $stored_id

        ))->row;

        $node_row = self::$db->query("SELECT * FROM log_node WHERE node_id = :node_id", array(

            'node_id' => $operation_row['result']

        ))->row;

        $node_version = $node_row['version'];

        $this->assertEquals(1, $node_version);

   }


   public function testIfOperationDateMatchesWithCurrentDatetime() {

        $login_service = new LoggingService();

        $stored_id = $login_service->addOperation(1,1,'operation4','type4', '4', array(
            'name' => 'eddy',
            'lastname' => 'norton'
        ));

        $operation_row = self::$db->query("SELECT operation_date FROM log_operation WHERE id = :id", array(

            ':id' => $stored_id

        ))->row;

        $operation_date = $operation_row['operation_date'];

        $converted = new DateTime($operation_date);
            
        $this->assertEquals((new \DateTime('now'))->format('Y-m-d H:i:s'), $converted->format('Y-m-d H:i:s'));

        $this->assertTrue((new \DateTime('now'))->getTimestamp() - $converted->getTimestamp() < 5 );

        
   
   }

   public function testIfIdMatchesWithTheOneOnDb() {

        $login_service = new LoggingService();

        $stored_id = $login_service->addOperation(1, 2,'operation3','type3','5', array( 
            'name' => 'tony',
            'lastname' => 'ferguson'
        ));     

        $operation_row = self::$db->query("SELECT * FROM log_operation WHERE id = :id", array(

            ':id' => $stored_id

        ))->row;

        $node_row = self::$db->query("SELECT * FROM log_node WHERE node_id = :node_id", array(

            'node_id' => $operation_row['result']

        ))->row;

        $log_node_id = $node_row['id'];

        $this->assertEquals(5, $log_node_id);
   }


}

?>