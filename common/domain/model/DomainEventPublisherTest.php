<?php

use \model\common\domain\model\DomainEventPublisher;
use \model\IdentityAndAccess\domain\model\PersonnelCreated;
use \model\IdentityAndAccess\domain\model\PersonnelEmailChanged;

use \model\common\domain\model\DomainEvent;

use PHPUnit\Framework\TestCase;


class DomainEventPublisherTest extends TestCase {


	private static \DB $db;

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

    public function testIfPublishCarriesEventsToDb() {

        self::$db->command("DELETE FROM event");
        self::$db->command("DELETE FROM subscription");

        $domain_event_publisher = DomainEventPublisher::instance();

        $personnel_created = new PersonnelCreated(1, 2, true, 'rocky', 'marciano', '11332244556', 'male', '009005055333888',  'r-marciano@mail.it');

        $add_new_action_to_db = self::$db->command("INSERT INTO subscription(type, action) VALUES ('model/IdentityAndAccess/domain/model/PersonnelCreated', 'model/auth/AuthEventProcessor.onPersonnelCreation') ");

        $add_new_action_to_db2 = self::$db->command("INSERT INTO subscription(type, action) VALUES ('model/IdentityAndAccess/domain/model/PersonnelCreated', 'model/auth/AuthEventProcessor.onPersonnelCreation') "); //checking multiple action under same event

        $domain_event_publisher->publish($personnel_created);

        $verify_from_db = self::$db->query("SELECT * FROM event WHERE type='model\\\\IdentityAndAccess\\\\domain\\\\model\\\\PersonnelCreated'")->rows;

        $this->assertEquals(2, count($verify_from_db)); //count yap

    }

    
    public function testIfActionEnteredOnDbCorrectly() {

        // self::$db->command("DELETE FROM event");
        // self::$db->command("DELETE FROM subscription");

        $domain_event_publisher = DomainEventPublisher::instance();

        $personnel_created = new PersonnelCreated(2, 4, true, 'joe', 'louis', '01332244556', 'male', '009005151333888',  'joelouis@mail.us'); 
        
        // $add_new_action_to_db = self::$db->command("INSERT INTO subscription(type, action) VALUES ('model/IdentityAndAccess/domain/model/PersonnelCreated', 'model/auth/AuthEventProcessor.onPersonnelCreation') ");

        $domain_event_publisher->publish($personnel_created);

        $check_action = self::$db->query("SELECT * FROM event WHERE action = 'model/auth/AuthEventProcessor.onPersonnelCreation' ORDER BY occured_on DESC LIMIT 1")->rows;

        $this->assertEquals(1, count($check_action));

    }

    public function testIfOccuredOnEnteredToDbCorrectly() {


        $domain_event_publisher = DomainEventPublisher::instance();

        $personnel_created = new PersonnelCreated(3, 4, true, 'morgan', 'freeman', '01332244550', 'male', '099005151333888',  'freeman@mail.us');

        $domain_event_publisher->publish($personnel_created);

        $check_occured_on = self::$db->query("SELECT * FROM event ORDER BY occured_on DESC LIMIT 1")->row['occured_on'];

        $converted = new DateTime($check_occured_on);

        $this->assertEquals((new \DateTime('now'))->format('Y-m-d H:i:s'), $converted->format('Y-m-d H:i:s'));

        $this->assertTrue((new DateTime())->getTimestamp() - $converted->getTimestamp() < 5); 


    }

    public function testIfVersionEnteredOnDbCorrectly() {

        $domain_event_publisher = DomainEventPublisher::instance();

        $personnel_created = new PersonnelCreated(3, 4, true, 'leo', 'doe', '01332244550', 'male', '999905151333888',  'leo@mail.ar');

        $domain_event_publisher->publish($personnel_created);

        $check_version = self::$db->query("SELECT * FROM event ORDER BY version DESC LIMIT 1")->row['version'];
    
        $this->assertEquals(1, $check_version);
    }


    public function testIfPersonnelCreatedMatchesWithDataOnDb() {

        $domain_event_publisher = DomainEventPublisher::instance();

        $personnel_created = new PersonnelCreated(4, 4, true, 'tom', 'hanks', '01332204510', 'male', '299005251333828',  'hanx@mail.us');

        $domain_event_publisher->publish($personnel_created);

        $get_data = self::$db->query("SELECT * FROM event ORDER BY `id` DESC LIMIT 1")->row['data'];

        //$converted = array_map('strval', $get_data);
        $json_to_array = json_decode($get_data, true);

        $this->assertEquals(4, $json_to_array['id']);

    }


}


?>