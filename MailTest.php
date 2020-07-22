<?php

use \model\notification\Mail;

use PHPUnit\Framework\TestCase;

class MailTest extends TestCase {

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

        self::$db->command("DELETE FROM mail");

    }


    public function testIfIsProccedOnDbTurnedIsIncreased() {

        $mail = new Mail();

        $mail->addToQueue('subject', 'hey' , 'recipient');

        $get_from_queue_row = $mail->getFromQueue(1);

        $query_db_is_processed = self::$db->query("SELECT * FROM mail WHERE id = :id", array(

            ':id' => $get_from_queue_row[0]['id']

        ))->row;

        $db_is_processed = $query_db_is_processed['is_processed'];

        $this->assertEquals(1, $db_is_processed);

    }


    public function testIfSentOnMatchesTheCurrentTime() {

        $mail = new Mail();

        $mail->addToQueue('subject0', 'zzz', '2190412904');

        $get_from_queue_row = $mail->getFromQueue(1);

        $query_db_is_processed = self::$db->query("SELECT * FROM mail WHERE id = :id", array(

            ':id' => $get_from_queue_row[0]['id']

        ))->row;

        $db_sent_on = $query_db_is_processed['sent_on'];

        $converted = new DateTime($db_sent_on);
            
        $this->assertEquals((new \DateTime('now'))->format('Y-m-d H:i:s'), $converted->format('Y-m-d H:i:s'));

        $this->assertTrue((new \DateTime('now'))->getTimestamp() - $converted->getTimestamp() < 5 );


    }


    public function testIfQueuedOnMatchesWithCurrentTime() {

        $mail = new Mail();

        $mail->addToQueue('subject1', 'world', '53123012401');

        $queued_on_db = self::$db->query("SELECT * FROM mail WHERE id = :id", array(

            ':id' => self::$db->getLastId()

        ))->row;

        $queued_date = $queued_on_db['queued_on'];

        $converted = new DateTime($queued_date);
            
        $this->assertEquals((new \DateTime('now'))->format('Y-m-d H:i:s'), $converted->format('Y-m-d H:i:s'));

        $this->assertTrue((new \DateTime('now'))->getTimestamp() - $converted->getTimestamp() < 5 );
    
    }

    public function testSubjectMatchesWithTheOneOnDb() {

        $mail = new Mail();

        $mail->addToQueue('subject2', 'this' , '51501250');
        
        $mail_subject = self::$db->query("SELECT * FROM mail WHERE id = :id", array(

            ':id' => self::$db->getLastId()

        ))->row;

        $subject = $mail_subject['subject'];

        $this->assertEquals('subject2', $subject);

    }


    public function testMessageMatchesWithTheOneOnDb() {

        $mail = new Mail();

        $mail->addToQueue('subject3', 'new_message' , '00491501250');
        
        $mail_message = self::$db->query("SELECT * FROM mail WHERE id = :id", array(

            ':id' => self::$db->getLastId()

        ))->row;

        $message = $mail_message['message'];

        $this->assertEquals('new_message', $message);


    }


    public function testRecipientMatchesWithTheOneOnDb() {

        $mail = new Mail();

        $mail->addToQueue('subject4', 'is_delivered' , '0011501250');
        
        $mail_recipient = self::$db->query("SELECT * FROM mail WHERE id = :id", array(

            ':id' => self::$db->getLastId()

        ))->row;

        $recipient = $mail_recipient['recipient'];

        $this->assertEquals('0011501250', $recipient);

    }

}

?>