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
        self::$db->command("DELETE FROM subtask");
        self::$db->command("DELETE FROM subtask_assignee");
        self::$db->command("DELETE FROM task_bin");

	}

	protected function setUp() : void {

        $this->locator = $this->createMock(IFileLocator::class);
        $this->locator->method('getFilePath')->willReturn(DIR_REPOSITORY);

        $this->bin_locator = $this->createMock(IFileLocator::class);
        $this->bin_locator->method('getFilePath')->willReturn(DIR_REPOSITORY . './bin');

	}

	public function testIf_save_Function_Creates_A_New_Task(){

		$task_repository = new TaskRepository(self::$db, $this->locator, $this->bin_locator);

		$id = $task_repository->save(new Task(null, 'title', new PersonnelId(1), null, 'description', null,null, null, null, null,null, null, null, null, null, null));

		$check_task_saved = self::$db->query("SELECT * FROM task WHERE id = :id" , array(
			':id' => $id->getId()
		))->row;

		$this->assertNotEmpty($check_task_saved);
	}

	public function test_If_findBySubtask_Returns_The_Task(){

		$task_repository = new TaskRepository(self::$db, $this->locator, $this->bin_locator);

		$subtask_arr = array( new Subtask(new SubtaskId(2), new TaskId(1), 'title' ,new PersonnelId(1), null,'description', null, new DateTime('now'), null, null, null, null, null, null, new DateTime('now'))
		);

		$id = $task_repository->save(new Task(null, 'title', new PersonnelId(1), null, 'description',null, null, null, $subtask_arr, null,null, null, null, null, null, null)); //save returns task id !!!!!!!!!!!!!!!!!!!!!!!

		$task = $task_repository->findBySubtask(new SubtaskId(1));

		$this->assertEquals($id, $task->id());
	}

	public function test_If_find_Function_Returns_The_Task(){
		
		$task_repository = new TaskRepository(self::$db, $this->locator, $this->bin_locator);

		$id = $task_repository->save(new Task(null, 'title', new PersonnelId(1), null, 'description', null,null, null, null, null,null, null, null, null, null, null));  //save returns task id

		$task = $task_repository->find($id); // find needs 1 parameter which is taskid
		$this->assertEquals($id, $task->id());
	}

	public function test_If_taskRelatedTo_Returns_Tasks_Related_To_Assigner() {

		$task_repository = new TaskRepository(self::$db, $this->locator, $this->bin_locator);

		$task_repository->save(new Task(null, 'jon', new PersonnelId(2), null, 'samwise',null, null, null, null, null,null, null, null, null, null, null));

		$task_repository->save(new Task(null, 'snow', new PersonnelId(2), null, 'gamgee',null, null, null, null, null,null, null, null, null, null, null));
		
		$tasks = $task_repository->tasksRelatedTo(new PersonnelId(2), new QueryObject());
		$this->assertNotEmpty($tasks);
	}

	public function test_If_taskRelatedTo_Returns_Tasks_Related_To_Assignee() {

		$task_repository = new TaskRepository(self::$db, $this->locator, $this->bin_locator);

		$assignee_arr = array(
			new PersonnelId(1)
		);

		$task_repository->save(new Task(null, 'title0', null, $assignee_arr, 'desc00',null, null, null, null, null,null, null, null, null, null, null));
		
		$task_repository->save(new Task(null, 'title1', null, $assignee_arr, 'desc11',null, null, null, null, null,null, null, null, null, null, null));
		
		$tasks = $task_repository->tasksRelatedTo(new PersonnelId(1), new QueryObject());
		$this->assertNotEmpty($tasks);

	}

	public function test_If_taskRelatedTo_Returns_Tasks_Inside_Subtasks_Of_Assigner() {

		$subtask_arr = array( new Subtask(new SubtaskId(1), new TaskId(1), 'title' ,new PersonnelId(2), null,'description', null, new DateTime('now'), null, null, null, null, null, null, new DateTime('now'))
		);

		$task_repository = new TaskRepository(self::$db, $this->locator, $this->bin_locator);

		$task_repository->save(new Task(null, 'newtitle', new PersonnelId(1), null, 'decs00',null, null, null, $subtask_arr, null,null, null, null, null, null, null));

		$subtasks = $task_repository->tasksRelatedTo(new PersonnelId(2), new QueryObject());
		$this->assertNotEmpty($subtasks);

	}

	public function test_If_taskRelatedTo_Returns_Tasks_Inside_Subtasks_Of_Assignee() {

		$assignee_arr = array(
			new PersonnelId(1)
		);

		$subtask_arr = array( new Subtask(new SubtaskId(1), new TaskId(1), 'title' ,null, $assignee_arr,'description', null, new DateTime('now'), null, null, null, null, null, null, new DateTime('now'))
		);

		$task_repository = new TaskRepository(self::$db, $this->locator, $this->bin_locator);

		$task_repository->save(new Task(null, 'godfather', null, $assignee_arr, 'vito corleone',null, null, null, $subtask_arr, null,null, null, null, null, null, null));

		$subtasks = $task_repository->tasksRelatedTo(new PersonnelId(1), new QueryObject());
		$this->assertNotEmpty($subtasks);
	}

	public function test_If_tasksRelatedToCount_Returns_The_Number_Of_Related_Tasks_Of_Assigner(){

		$task_repository = new TaskRepository(self::$db, $this->locator, $this->bin_locator);

		$task_repository->save(new Task(null, 'jon', new PersonnelId(9), null, 'samwise',null, null, null, null, null,null, null, null, null, null, null));

		$task_repository->save(new Task(null, 'snow', new PersonnelId(9), null, 'gamgee',null, null, null, null, null,null, null, null, null, null, null));
		
		$tasks_of_assigner = $task_repository->tasksRelatedToCount(new PersonnelId(9), new QueryObject());
		$this->assertEquals($tasks_of_assigner, 2); // 2 tasks related to personnel 9.
	}

	public function test_If_tasksRelatedToCount_Returns_The_Number_Of_Related_Tasks_Of_Assignee(){

		$task_repository = new TaskRepository(self::$db, $this->locator, $this->bin_locator);

		$assignee_arr = array(
			new PersonnelId(8)
		);

		$task_repository->save(new Task(null, 'title0', null, $assignee_arr, 'desc00',null, null, null, null, null,null, null, null, null, null, null));
		
		$task_repository->save(new Task(null, 'title1', null, $assignee_arr, 'desc11',null, null, null, null, null,null, null, null, null, null, null));
		
		$tasks_of_assignee = $task_repository->tasksRelatedToCount(new PersonnelId(8), new QueryObject());
		$this->assertEquals($tasks_of_assignee, 2);

	}

	public function test_If_tasksRelatedToCount_Returns_The_Number_Of_Tasks_Inside_Of_Subtasks_Related_To_Assignee(){
		
		$subtask_arr = array( new Subtask(new SubtaskId(1), new TaskId(1), 'title' ,new PersonnelId(7), null,'description', null, new DateTime('now'), null, null, null, null, null, null, new DateTime('now'))
		);

		$task_repository = new TaskRepository(self::$db, $this->locator, $this->bin_locator);

		$task_repository->save(new Task(null, 'newtitle', new PersonnelId(1), null, 'decs00',null, null, null, $subtask_arr, null,null, null, null, null, null, null));

		$subtasks = $task_repository->tasksRelatedToCount(new PersonnelId(7), new QueryObject());
		$this->assertEquals($subtasks, 1);
	}

	public function test_If_tasksRelatedToCount_Returns_The_Number_Of_Tasks_Inside_Of_Subtasks_Related_To_Assigner(){

		$subtask_arr = array( new Subtask(new SubtaskId(1), new TaskId(1), 'title' ,new PersonnelId(5), null,'description', null, new DateTime('now'), null, null, null, null, null, null, new DateTime('now'))
		);

		$task_repository = new TaskRepository(self::$db, $this->locator, $this->bin_locator);

		$task_repository->save(new Task(null, 'newtitle', new PersonnelId(1), null, 'decs00',null, null, null, $subtask_arr, null,null, null, null, null, null, null));

		$subtasks = $task_repository->tasksRelatedToCount(new PersonnelId(5), new QueryObject());
		$this->assertEquals($subtasks, 1);
	}


	public function test_If_remove_Function_Carries_Task_To_TaskBin(){

		$task_repository = new TaskRepository(self::$db, $this->locator, $this->bin_locator);

		$id = $task_repository->save(new Task(null, 'new title', null, null, 'new description',null, null, null, null, null,null, null, null, null, null, null));

		$task_repository->remove($id);

		$confirm_carried_to_bin = self::$db->query("SELECT * FROM task_bin WHERE id = :id", array(
			':id' => $id->getId()
		))->row;

		$this->assertNotEmpty($confirm_carried_to_bin);
	}

}

?>

