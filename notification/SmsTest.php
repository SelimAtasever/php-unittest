
<?php

use model\notification\Sms;

use PHPUnit\Framework\TestCase;


class SmsTest extends TestCase {
  
    private static \DB $db;

	public static function setUpBeforeClass() : void {
        
        global $framework;
        $config = $framework->get('config');

        self::$db = new \DB(
            $config->get('db_notification_type'),
            $config->get('db_notification_hostname'),
            $config->get('db_notification_username'),
            $config->get('db_notification_password'),
            $config->get('db_notification_database'),
            $config->get('db_notification_port')
        );

        self::$db->command("DELETE FROM sms");

    }

    public function testIfAddToQueueAddsNewPhoneCorrectly() {

        $sms = new Sms();

        $sms->addToQueue('first_entry', '004928148214');

        $check_sms_db = self::$db->query("SELECT * FROM sms WHERE id = :id", array(

            ':id' => self::$db->getLastId()
        
        ))->row;

        $db_sms_phone = $check_sms_db['phone'];

        $this->assertEquals('004928148214' , $db_sms_phone);

    }


    public function testIfMessageMatchesWithTheOneOnDb() {

        $sms = new Sms();

        $sms->addToQueue('db_message' , '001410299123');

        $check_sms_db = self::$db->query("SELECT * FROM sms WHERE id = :id", array(

            ':id' => self::$db->getLastId()
        
        ))->row;

        $db_sms_message = $check_sms_db['message'];
        $this->assertEquals('db_message', $db_sms_message);

    }

    public function testIfIsProcessedOnDbHasIncreased() {

        $sms = new Sms();

        $sms->addToQueue('third_msg', '91251290412');

        $get_from_queue_row = $sms->getFromQueue(1);

        $check_sms_db = self::$db->query("SELECT * FROM sms WHERE id = :id", array(

            ':id' => $get_from_queue_row[0]['id']
        
        ))->row;

        $db_sms_is_processed = $check_sms_db['is_processed'];

        $this->assertEquals(1, $db_sms_is_processed);

    }


    public function testIfSentOnMatchesWithCurrentTime() {

        $sms = new Sms();

        $sms->addToQueue('fourth msg', '00387915924');

        $get_from_queue_row = $sms->getFromQueue(1);

        $check_sms_db = self::$db->query("SELECT * FROM sms WHERE id= :id", array(

          ':id' => $get_from_queue_row[0]['id']

        ))->row;

        $db_is_sent_on = $check_sms_db['sent_on'];

        $converted = new DateTime($db_is_sent_on);
        
        $this->assertEquals((new \DateTime('now'))->format('Y-m-d H:i:s'), $converted->format('Y-m-d H:i:s'));

        $this->assertTrue((new \DateTime('now'))->getTimestamp() - $converted->getTimestamp() < 5 );

    }


    public function testIfQueuedOnMatchesWithCurrentTime() {

        $sms = new Sms();

        $sms->addToQueue('fifth message', '003390786543');

        $check_sms_db = self::$db->query("SELECT * FROM sms WHERE id= :id", array(

            ':id' => self::$db->getLastId()

        ))->row;

        $db_queued_on = $check_sms_db['queued_on'];

        $converted = new DateTime($db_queued_on);
        
        $this->assertEquals((new \DateTime('now'))->format('Y-m-d H:i:s'), $converted->format('Y-m-d H:i:s'));

        $this->assertTrue((new \DateTime('now'))->getTimestamp() - $converted->getTimestamp() < 5 );


    }

}

?>
