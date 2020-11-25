<?php 

use model\ProcedureManagement\domain\model\Attachment;
use model\ProcedureManagement\domain\model\AttachmentId;
use model\ProcedureManagement\domain\model\StepId;
use model\ProcedureManagement\domain\model\PersonnelId;

use PHPUnit\Framework\TestCase;


class AttachmentTest extends TestCase{

	public function test_isOwner_Returns_True_If_Personnel_Id_Is_Uploader(){

		$attachment = new Attachment(
			new AttachmentId(1),
			new StepId(1),
			new PersonnelId(4),		/* uploader */
			'attachment name',
			'base64',
			null 					/* date added */

		);

		$this->assertTrue($attachment->isOwner(new PersonnelId(4)));
		$this->assertFalse($attachment->isOwner(new PersonnelId(1)));
	}

	public function test_If_extension_Method_Returns_An_Array_Of_Extension(){
	
		$attachment = new Attachment(
			new AttachmentId(1),
			new StepId(1),
			new PersonnelId(4),		
			'attachment.saitama',
			'base64',
			null 					

		);

		$this->assertEquals('saitama', $attachment->extension());
	}

}

?>