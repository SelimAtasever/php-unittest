<?php 

use \model\TaskManagement\domain\model\Task;
use \model\TaskManagement\domain\model\TaskId; 
use \model\TaskManagement\domain\model\PersonnelId;
use \model\TaskManagement\domain\model\Subtask;
use \model\TaskManagement\domain\model\SubtaskId;
use \model\TaskManagement\domain\model\Location;
use \model\TaskManagement\domain\model\TaskPriority;
use \model\TaskManagement\domain\model\TaskStatus;
use \model\TaskManagement\infrastructure\TaskRepository;
use \model\TaskManagement\infrastructure\IFileLocator;

use model\common\QueryObject;
use model\common\ExceptionCollection;

use PHPUnit\Framework\TestCase;

class TaskRepositoryTest extends TestCase{

	private static \DB $db;
	private IFileLocator $locator;
	private IFileLocator $bin_locator;

	public static function setUpBeforeClass() : void {
    	global $framework;
        $config = $framework->get('config');

    	self::$db = new \DB(
            $config->get('db_task_type'),
            $config->get('db_task_hostname'),
            $config->get('db_task_username'),
            $config->get('db_task_password'),
            $config->get('db_task_database'),
            $config->get('db_task_port')
        );

        self::$db->command("DELETE FROM task");
        self::$db->command("DELETE FROM task_assignee");
        self::$db->command("DELETE FROM subtask");
        self::$db->command("DELETE FROM subtask_assignee");
        self::$db->command("DELETE FROM task_bin");
        self::$db->command("DELETE FROM task_comment_bin");

	}

	protected function setUp() : void {

        $this->locator = $this->createMock(IFileLocator::class);
        $this->locator->method('getFilePath')->willReturn(DIR_REPOSITORY);

        $this->bin_locator = $this->createMock(IFileLocator::class);
        $this->bin_locator->method('getFilePath')->willReturn(DIR_REPOSITORY . './bin');

	}

	private function validTaskWithoutTaskId($id, $assignee_arr = null, $subtask_arr = null) {
        return new Task(
            null, 					/* id */
           'TASK-TEST TITLE', 		/* title */
			new PersonnelId($id), 	/* assigner */
			$assignee_arr, 			/* assignee[] */
			'ETHEREUM',				/* description */
			null, 					/* start_date */
			null, 					/* due_date */
			null,					/* location */ 
			$subtask_arr, 			/* subtasks[] */
			null,					/* priority */
			null,					/* status */
			null, 					/* triggers[] */
			null,					/* comments[] */ 
			null, 					/* events[] */
			null, 					/* attachments[] */
			null, 					/* created_on */
			null 					/* edited_on */
		);
    }

	public function testIf_save_Function_Creates_A_New_Task(){

		$task_repository = new TaskRepository(self::$db, $this->locator, $this->bin_locator);

		$id = $task_repository->save($this->validTaskWithoutTaskId(1));

		$db_id = self::$db->query("SELECT * FROM task WHERE id = :id" , array(
			':id' => $id->getId()
		))->row['id'];

		$this->assertEquals($id->getId(), $db_id);
	}

	public function test_If_findBySubtask_Returns_The_Task(){

		$task_repository = new TaskRepository(self::$db, $this->locator, $this->bin_locator);

		$subtask_arr = array(new Subtask(
			null,										/* id */
    		new TaskId(1),								/* task_id */
    		'Subtask Test Title',						/* subtitle_title */
    		new PersonnelId(1),							/* assigner_id */
    		null,										/* assignee[] */
    		'Subtask description',						/* subtask_description */
    		null,										/* start_date */
    		null,										/* due_date */
    		null,										/* location */
    		null,										/* priority */
    		null,										/* status */
    		null,										/* $comments */
    		null,										/* $events */
    		null,										/* $attachments */
    		new DateTime()			
		));

		$task_id = $task_repository->save($this->validTaskWithoutTaskId(1, null, $subtask_arr));
		$task_id_int = $task_id->getId();

		$subtask_id = self::$db->query("SELECT * FROM subtask WHERE task_id = $task_id_int ")->row['id'];
		$task = $task_repository->findBySubtask(new SubtaskId($subtask_id));

		$this->assertEquals($task_id_int, $task->id()->getId());
	}

	public function test_If_find_Function_Returns_The_Task(){
		
		$task_repository = new TaskRepository(self::$db, $this->locator, $this->bin_locator);

		$id = $task_repository->save($this->validTaskWithoutTaskId(1));

		$task = $task_repository->find($id); 

		$this->assertEquals($id, $task->id());
	}

	public function test_If_taskRelatedTo_Returns_Number_Of_Tasks() {

		$task_repository = new TaskRepository(self::$db, $this->locator, $this->bin_locator);

		$task_repository->save($this->validTaskWithoutTaskId(1));
		$task_repository->save($this->validTaskWithoutTaskId(1));
		
		$tasks = $task_repository->tasksRelatedTo(new PersonnelId(1), new QueryObject());

		$this->assertCount(5,$tasks);
	}

	public function test_If_taskRelatedTo_Returns_Tasks_Related_To_Assignee() {

		$task_repository = new TaskRepository(self::$db, $this->locator, $this->bin_locator);
		$task_repository->save($this->validTaskWithoutTaskId(1));
		
		$tasks = $task_repository->tasksRelatedTo(new PersonnelId(1), new QueryObject());

		$first_task = $tasks[0];
		$id_obj = $first_task->id();
		$id = $id_obj->getId();

		$id_from_db = self::$db->query("SELECT * FROM task WHERE id = $id")->row['id'];
		$this->assertEquals($id, $id_from_db);
	}

		//$id`li `personnel`in `assigner`i oldugu `Subtask`larin icerisinde bulundugu `Task`lar
	 	//`$id`li `personnel`in `assignee`si oldugu `Subtask`larin icerisinde bulundugu `Task`lar

	public function test_If_taskRelatedTo_Returns_Tasks_Contains_Subtasks_Of_Assigner(){

		$task_repository = new TaskRepository(self::$db, $this->locator, $this->bin_locator);

		$subtask_arr = array(new Subtask(
			null,										
    		new TaskId(1),								
    		'Subtask Test Title 1',						
    		new PersonnelId(2),							
    		null,										
    		'Subtask description 1',
    		null,										
    		null,										
    		null,										
    		null,										
    		null,										
    		null,										
    		null,										 
    		null,										 
    		new DateTime()			
		));

		$id = $task_repository->save($this->validTaskWithoutTaskId(2, $assignee_arr = null, $subtask_arr));
		$id_int = $id->getId();

		$tasks = $task_repository->tasksRelatedTo(new PersonnelId(2), new QueryObject());
		
		$id_from_db = self::$db->query("SELECT * FROM task WHERE id = $id_int")->row['id'];	
		$this->assertEquals($id_from_db, $tasks[0]->id()->getId());
	}

	public function test_If_taskRelatedTo_Returns_Tasks_Contains_Subtasks_Of_Assignee(){


		$task_repository = new TaskRepository(self::$db, $this->locator, $this->bin_locator);

		$assignee_arr = array(
			new PersonnelId(3)
		);

		$subtask_arr = array(new Subtask(
			null,										
    		null,								
    		'Subtask Test Title 2',						
    		new PersonnelId(4),							
    		$assignee_arr,										
    		'Subtask description 2',
    		null,										
    		null,										
    		null,										
    		null,										
    		null,										
    		null,										
    		null,										 
    		null,										 
    		new DateTime()			
		));

		$id = $task_repository->save($this->validTaskWithoutTaskId(2, $assignee_arr, $subtask_arr));
		$id_int = $id->getId();

		$tasks = $task_repository->tasksRelatedTo(new PersonnelId(3), new QueryObject());

		$id_from_db = self::$db->query("SELECT * FROM task WHERE id = $id_int")->row['id'];
		$this->assertEquals($tasks[0]->id()->getId(), $id_from_db);
	}

	public function test_If_tasksRelatedToCount_Returns_The_Number_Of_Related_Tasks_Of_Assigner(){

		$task_repository = new TaskRepository(self::$db, $this->locator, $this->bin_locator);

		$task_repository->save($this->validTaskWithoutTaskId(1));
		
		$tasks_of_assigner = $task_repository->tasksRelatedToCount(new PersonnelId(1), new QueryObject());
		$this->assertEquals($tasks_of_assigner, 7); 
	}

	public function test_If_tasksRelatedToCount_Returns_The_Number_Of_Related_Tasks_Of_Assignee(){

		$task_repository = new TaskRepository(self::$db, $this->locator, $this->bin_locator);

		$assignee_arr = array(
			new PersonnelId(1)
		);		
		$tasks_of_assignee = $task_repository->tasksRelatedToCount(new PersonnelId(1), new QueryObject());
		$this->assertEquals($tasks_of_assignee, 7);

	}

	public function test_If_remove_Function_Carries_Task_To_Task_Bin(){

		$task_repository = new TaskRepository(self::$db, $this->locator, $this->bin_locator);

		$id = $task_repository->save($this->validTaskWithoutTaskId(1));
		$id_int = $id->getId();

		$task_repository->remove($id);

		$task_id = self::$db->query("SELECT * FROM task WHERE id = $id_int")->row;
		
		$this->assertEmpty($task_id);

		$task_bin_id = self::$db->query("SELECT * FROM task_bin WHERE id = $id_int")->row['id'];

		$this->assertEquals($id_int, $task_bin_id);
	}
}

?>

