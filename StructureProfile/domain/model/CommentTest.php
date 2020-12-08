<?php 

use model\StructureProfile\domain\model\Comment;
use model\StructureProfile\domain\model\CommentId;
use model\StructureProfile\domain\model\StructureId;
use model\StructureProfile\domain\model\PersonnelId;

use \model\StructureProfile\domain\model\exception\CommentInsufficientPrivilegeException;

use PHPUnit\Framework\TestCase;


class CommentTest extends TestCase{

	public function test_If_editMessage_Updates_Message_Correctly(){

		$comment = new Comment(
			new CommentId(1),
			new StructureId(1),
			new PersonnelId(1),
			'old message',
			null,				/* commented_on */
			null 				/* edited_on */
		);

		$comment->editMessage('new message', new PersonnelId(1));

		$updated_msg = $comment->message();

		$this->assertNotEquals('old message', $updated_msg);
		$this->assertEquals('new message', $updated_msg);
	}

	public function test_If_editMessage_Throws_Exception_When_Personnel_Isnt_Permitted(){
		$this->expectException(CommentInsufficientPrivilegeException::class);

		$comment = new Comment(
			new CommentId(1),
			new StructureId(1),
			new PersonnelId(1),
			'old message',
			null,				/* commented_on */
			null 				/* edited_on */
		);

		$comment->editMessage('new message', new PersonnelId(3));

		foreach ($comment->exceptions() as $exception) {
			if(get_class($exception) == CommentInsufficientPrivilegeException::class)
				throw new CommentInsufficientPrivilegeException();
		}

	}
}

?>