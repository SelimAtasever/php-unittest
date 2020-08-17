<?php

namespace tests\Common\application\domain\model;


use \model\common\domain\model\DomainEventDispatcher;
use \model\IdentityAndAccess\domain\model\PersonnelCreated;
use \model\common\domain\model\DomainEvent;
use \model\common\domain\model\DomainEventPublisher;
use \model\auth\AuthEventProcessor;



use PHPUnit\Framework\TestCase;

class DomainEventDispatcherTest extends TestCase {

	private static \DB $db;
	private static bool $method_is_called = false;

    public static function setUpBeforeClass() : void {
    	global $framework;
        $config = $framework->get('config');

    	self::$db = new \DB(
            $config->get('db_event_type'),
            $config->get('db_event_hostname'),
            $config->get('db_event_username'),
            $config->get('db_event_password'),
            $config->get('db_event_database'),
            $config->get('db_event_port')
        );

        self::$db->command("DELETE FROM event");
        self::$db->command("DELETE FROM subscription");


	}


    public function failTakingAction() { 

    	$domain_event_publisher = DomainEventPublisher::instance();
    	
    	self::$method_is_called = true;

    	$personnel_for_fail = new PersonnelCreated(4, 4, true, 'joe', 'biden', '11332200556', 'male', '000005055333888',  'biden@mail.it'); 
            
        $domain_event_publisher->publish($personnel_for_fail);
        
    }


	public function testIfIsProcessedIsTakingAction(){

		$domain_event_dispatcher = DomainEventDispatcher::instance();

		$domain_event_publisher = DomainEventPublisher::instance();
		
		$personnel_created = new PersonnelCreated(1, 1, true, 'john', 'doe', '11332244556', 'male', '009005055333888',  'johndoe@mail.it');

		$personnel_created2 = new PersonnelCreated(2, 2, true, 'mary', 'doe', '11332244556', 'female', '019005055333888',  'marydoe@mail.it');

		$personnel_created3 = new PersonnelCreated(3, 3, true, 'walter', 'white', '11332244566', 'female', '111005055333888',  'white@mail.it');


		self::$db->command("INSERT INTO subscription(type, action) VALUES ('model/IdentityAndAccess/domain/model/PersonnelCreated', 'model\\\\auth\\\\AuthEventProcessor.onPersonnelCreation') ");

        self::$db->command("INSERT INTO subscription(type, action) VALUES ('model/IdentityAndAccess/domain/model/PersonnelCreated', 'model\\\\auth\\\\AuthEventProcessor.onPersonnelCreation') "); //checking multiple action under same event

        self::$db->command("INSERT INTO subscription(type, action) VALUES ('model/IdentityAndAccess/domain/model/PersonnelCreated', 'tests\\\\Common\\\\application\\\\domain\\\\model\\\\DomainEventDispatcherTest.failTakingAction') ");


		$domain_event_publisher->publish($personnel_created);
		$domain_event_publisher->publish($personnel_created2);
		$domain_event_publisher->publish($personnel_created3);

		$domain_event_dispatcher->dispatchEvents(3);

		$check_is_processed = self::$db->query("SELECT * FROM event WHERE is_processed = '1'")->rows;

		$this->assertEquals(3, count($check_is_processed));	 ///testing dispatch is_processed

		$this->assertTrue(self::$method_is_called);   /// testing dispatch has_failed

		$check_has_failed = self::$db->query("SELECT * FROM event WHERE has_failed = '1'")->rows;

		$this->assertEquals(2, count($check_has_failed));

	}


}

?>