<?php
	
use \model\FileManagement\application\ImageLinkingService;
use \model\FileManagement\domain\model\IFileRepository;
use \model\FileManagement\infrastructure\IImagePathProvider;
use \model\FileManagement\infrastructure\IImageResizer;
use \model\FileManagement\domain\model\Directory;
use \model\common\domain\model\SubmoduleId;
use \model\FileManagement\domain\model\DirectoryId;
use \model\FileManagement\domain\model\File;
use \model\FileManagement\domain\model\FileId;


use PHPUnit\Framework\TestCase;


class ImageLinkingServiceTest extends TestCase{

	private ImageLinkingService $image_linking_service;

	protected function setUp() : void {

		$file = new File(new FileId(1), new SubmoduleId(1), null, 'base-61', 'name_file', null);

		$mock_file_repo = $this->createMock(IFileRepository::class);
		$mock_file_repo->method('findFileInAnySubmodule')->willReturn($file);

		$mock_IImagePath = $this->createMock(IImagePathProvider::class);
		$mock_IImagePath->method('providePath')->willReturn('/Users/kant/Desktop/GitHub/erp-api/v1/bin');

		$mock_IImageResizer = $this->createMock(IImageResizer::class);
		$mock_IImageResizer->method('resize')->willReturn('ok');
	
		
		$this->image_linking_service = new ImageLinkingService($mock_file_repo, $mock_IImagePath, $mock_IImageResizer);

	}

	public function testIf_ProvidePath_Returns_Correct_Path() {


		$check = $this->image_linking_service->getImageDirectAccessUrl('1', 'resize', 100 , 150);

		$this->assertEquals('/Users/kant/Desktop/GitHub/erp-api/v1/bin', $check);
	}

}


?>