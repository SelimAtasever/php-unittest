<?php 

use \model\ProcedureManagement\domain\model\Comment;
use \model\ProcedureManagement\domain\model\CommentId;
use \model\ProcedureManagement\domain\model\StepId;
use \model\ProcedureManagement\domain\model\PersonnelId;;
use model\ProcedureManagement\domain\model\exception\CommentInsufficientPrivilegeException;
use \model\common\ExceptionCollection;

use PHPUnit\Framework\TestCase;


class CommentTest extends TestCase{

	public function test_If_isCommentOwner_Returns_True_When_Commentor_Ids_Are_Same(){

		$comment = new Comment(
			new CommentId(1),
			new StepId(1),
			new PersonnelId(3), 	/* commentator */
			'comment message',
			null, 					/* commented_on (daytime) */
			null 					/* edited_on (daytime) */
		);

		$this->assertTrue($comment->isCommentOwner(new PersonnelId(3)));
		$this->assertFalse($comment->isCommentOwner(new PersonnelId(1)));
	}


	public function test_If_editMessage_Method_Edites_Message(){

		$comment = new Comment(
			new CommentId(1),
			new StepId(1),
			new PersonnelId(2), 	
			'comment message',
			null, 					
			null 					
		);

		$old_message = $comment->message();
		$comment->editMessage('edited message', new PersonnelId(2));

		$new_message = $comment->message();

		$this->assertNotEquals($old_message, $new_message);
		$this->assertNotEquals($new_message, 'edited_message');
	}

	public function test_editMessage_Throws_An_Exception_When_Commentator_Ids_Arent_Same(){
		$this->expectException(CommentInsufficientPrivilegeException::class);

		$comment = new Comment(
			new CommentId(1),
			new StepId(1),
			new PersonnelId(1), 	
			'comment message',
			null, 					
			null 					
		);

		$comment->editMessage('doom', new PersonnelId(2) );
		$exceptions = $comment->exceptions();


		foreach ($exceptions as $exception) {
			if(get_class($exception) == CommentInsufficientPrivilegeException::class)
				throw new CommentInsufficientPrivilegeException();
		}
	}
}

?>