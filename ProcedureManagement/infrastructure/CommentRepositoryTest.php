<?php

use model\ProcedureManagement\domain\model\Comment;
use model\ProcedureManagement\domain\model\CommentId;
use model\ProcedureManagement\domain\model\StepId;
use model\ProcedureManagement\domain\model\PersonnelId;
use model\ProcedureManagement\infrastructure\CommentRepository;

use PHPUnit\Framework\TestCase;

class CommentRepositoryTest extends TestCase{

	private static \DB $db;

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

       self::$db->command("DELETE FROM step_comment");
       self::$db->command("DELETE FROM step");
       self::$db->command("DELETE FROM step_comment_bin");
	}

	public function test_If_save_Method_Stores_New_Step_Comment_On_Db(){

		$comment_repository = new CommentRepository(self::$db);

		$comment_repository->save(new Comment(
			new CommentId(1),
			new StepId(1),
			new PersonnelId(1),
			'this is the comment message', 
			new DateTime(),
			new DateTime()
		), 
		new StepId(1));

		$comment_row = self::$db->query('SELECT * FROM step_comment WHERE id = 1')->rows;
		$this->assertNotEmpty($comment_row);

	}

	public function test_If_find_Method_Returns_A_Comment_From_Db_With_Given_Id(){

		$comment_repository = new CommentRepository(self::$db);

		$returned_comment = $comment_repository->find(new CommentId(1));
		$this->assertEquals($returned_comment->id(), new CommentId(1));
	}

	public function test_If_remove_Method_Carries_Comment_To_Step_Comment_Bin(){

		$comment_repository = new CommentRepository(self::$db);

		$comment_repository->save(new Comment(
			new CommentId(2),
			new StepId(2),
			new PersonnelId(1),
			'this will be removed',
			new DateTime(),
			new DateTime()
		),
		new StepId(2));

		$comment_repository->remove(new CommentId(2)); 

		$confirm_comment_removed = self::$db->query('SELECT * FROM step_comment_bin WHERE id = 2')->rows;
		$this->assertNotEmpty($confirm_comment_removed);
	}

	public function test_stepExists_Method_Returns_True_If_StepId_Is_Found(){

		$comment_repository = new CommentRepository(self::$db);
   	
		self::$db->command("INSERT INTO step (id, procedure_id, title, is_complete, `order`) VALUES ('1','1','title','1','1')");

		$confirm_step_exists = $comment_repository->stepExists(new StepId(1));
		$this->assertTrue($confirm_step_exists);
	}	

	public function test_If_nextId_Returns_A_New_Unique_Id(){

		$comment_repository = new CommentRepository(self::$db);

		$unique_id = $comment_repository->nextId();
		$this->assertNotEmpty($unique_id);
	}

	
}	

?>

