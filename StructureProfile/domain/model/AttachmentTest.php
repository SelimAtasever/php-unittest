<?php

use model\StructureProfile\domain\model\Attachment;
use model\StructureProfile\domain\model\AttachmentId;
use model\StructureProfile\domain\model\StructureId;
use model\StructureProfile\domain\model\PersonnelId;

use PHPUnit\Framework\TestCase;

class AttachmentTest extends TestCase{

	public function test_If_isOWner_Returns_True_When_Personnel_Is_Appointed_As_Owner(){

		$attachment = new Attachment(
			new AttachmentId(1),
			new StructureId(1),
			new PersonnelId(1),
			'attachment name',
			'base 64',
			null
		);

		$this->assertTrue( $attachment->isOwner(new PersonnelId(1)) );
		$this->assertFalse( $attachment->isOwner(new PersonnelId(2)) );

	}

	public function test_If_extension_Method_Returns_extension_Of_The_Name(){

		$attachment = new Attachment(
			new AttachmentId(1),
			new StructureId(1),
			new PersonnelId(1),
			'attachment.sap',
			'base 64',
			null
		);

		$extension = $attachment->extension();	
		$this->assertEquals($extension, 'sap');
	}
}


?>