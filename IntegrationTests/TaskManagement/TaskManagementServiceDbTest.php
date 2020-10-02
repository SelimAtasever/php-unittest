<?php

use \model\TaskManagement\application\TaskManagementService;

use \model\TaskManagement\infrastructure\TaskRepository;
use \model\IdentityAndAccess\infrastructure\PersonnelRepository;
use \model\IdentityAndAccess\infrastructure\RoleRepository;
use \model\IdentityAndAccess\infrastructure\DepartmentRepository;

use \model\IdentityAndAccess\application\IdentityService;
use \model\IdentityAndAccess\application\DepartmentService;

use \model\TaskManagement\infrastructure\PersonnelAccessResolver;
use \model\TaskManagement\infrastructure\FileLocator;
use \model\TaskManagement\domain\model\Location;
use \model\TaskManagement\infrastructure\IdentityProvider;
use \model\TaskManagement\infrastructure\PersonnelValidator;
use \model\IdentityAndAccess\infrastructure\ImageDirectAccessLinkProvider;
use \model\IdentityAndAccess\infrastructure\ImagePathProvider;
use \model\TaskManagement\infrastructure\FileDirectAccessLinkProvider;

use \model\common\ExceptionCollection;
use \model\common\QueryObject;

use PHPUnit\Framework\TestCase;


class TaskManagementServiceDbTest extends TestCase{

	private static \DB $db_task;
    private static \DB $db_iaa;
    private static $jwToken;
    private TaskManagementService $task_management_service;

	public static function setUpBeforeClass() : void {
    	
    	global $framework;

        self::$jwToken = $framework->get('jwt');
        $config = $framework->get('config');

        self::$db_task = new \DB(
            $config->get('db_task_type'),
            $config->get('db_task_hostname'),
            $config->get('db_task_username'),
            $config->get('db_task_password'),
            $config->get('db_task_database'),
            $config->get('db_task_port')
        );

        self::$db_iaa = new \DB(
            $config->get('db_iaa_type'),
            $config->get('db_iaa_hostname'),
            $config->get('db_iaa_username'),
            $config->get('db_iaa_password'),
            $config->get('db_iaa_database'),
            $config->get('db_iaa_port')
        );

        self::$db_task->command("DELETE FROM task");
        self::$db_task->command("DELETE FROM task_bin");
        self::$db_task->command("DELETE FROM task_assignee");
        self::$db_task->command("DELETE FROM task_assignee_bin");
        self::$db_task->command("DELETE FROM task_comment");
        self::$db_task->command("DELETE FROM task_attachment");
        self::$db_task->command("DELETE FROM task_attachment_bin");
        self::$db_task->command("DELETE FROM task_comment_bin");
        self::$db_task->command("DELETE FROM task_event");
        self::$db_task->command("DELETE FROM task_event_bin");   
        self::$db_task->command("DELETE FROM subtask");
        self::$db_task->command("DELETE FROM subtask_assignee");
        self::$db_task->command("DELETE FROM subtask_assignee_bin");
        self::$db_task->command("DELETE FROM subtask_bin");
        self::$db_task->command("DELETE FROM subtask_comment");
        self::$db_task->command("DELETE FROM subtask_comment_bin");
        self::$db_task->command("DELETE FROM subtask_attachment");
        self::$db_task->command("DELETE FROM subtask_attachment_bin");
        self::$db_iaa->command("DELETE FROM personnel");
    }

    protected function setUp(): void {

        $image_path_provider = new ImagePathProvider('/Users/selimatasever/Desktop/GitHub/erp-api/v1/tests/yol');

        $image_direct_access_link_provider = new ImageDirectAccessLinkProvider(
            self::$jwToken, 'www.example.com', $image_path_provider, '0.0.0.0 8.8.4.4'
        );
        $department_repository = new DepartmentRepository(self::$db_iaa, null);
        $role_repository = new RoleRepository(self::$db_iaa, null);
        $personnel_repository = new PersonnelRepository(self::$db_iaa, null);

        $identity_service = new IdentityService(
            $personnel_repository, $role_repository, $department_repository, $image_direct_access_link_provider
        );

        $department_service = new DepartmentService($department_repository, $personnel_repository, $image_direct_access_link_provider);

        $personnel_access_resolver = new PersonnelAccessResolver($department_service);

        $personnel_validator = new PersonnelValidator($identity_service);
        $identity_provider = new IdentityProvider(1);

        $file_locator = new FileLocator(DIR_REPOSITORY . 'repo/test/dosya.txt');
        $file_bin_locator = new FileLocator(DIR_REPOSITORY . 'repo/test_bin/dosya.txt');
        $task_repository = new TaskRepository(self::$db_task, $file_locator, $file_bin_locator, null);

        $file_direct_access_link_provider = new FileDirectAccessLinkProvider(
            self::$jwToken, 'https://kant.ist', $file_locator, '0.0.0.0 8.8.4.4'
        );

        $this->task_management_service = new TaskManagementService(
            $task_repository, $identity_provider, $personnel_validator, $personnel_access_resolver, $file_direct_access_link_provider
        );
    }

    public function test_If_createTask_Creates_A_New_Task_On_Db_And_Return_Its_Id(){

        self::$db_iaa->insert('personnel', array(
            'id' => 1,
            'role_id' => 1,
            'department_id' => 1,
            'image_id' => null,
            'firstname' => 'Personnel Firstname 1',
            'lastname' => 'Personnel Lastname 1',
            'tcno' => 11223344550,
            'gender' => 'male',
            'phone' => '+90 12401220149',
            'email' => 'feridun@kant.ist',
            'is_active' => 1,
            'date_added' => (new DateTime())->format('Y-m-d H:i:s'),
            'last_modification' => (new DateTime())->format('Y-m-d H:i:s')
        ));

        $location = new stdClass();
        $location->latitude = '';
        $location->longitude = '';

        $assignee = [1];
        $task_id = $this->task_management_service->createTask(
            'Task Title 1',
            $assignee, 
            'Task Description 1',
            '2020-09-18 17:51:00',
            '2020-09-18 17:52:00',
            $location,
            1,
            1
        );       

        $db_task_id = self::$db_task->query("SELECT * FROM task WHERE id = :id", array(
            ':id' => $task_id
        ))->row['id'];
        
        $this->assertEquals($db_task_id, $task_id);
    }

    public function test_If_removeTask_Removes_Task_With_Given_Id(){

        self::$db_task->insert('task', array(
            'id' => 1,
            'title' => 'Task to be removed...',
            'assigner' => 1,
            'description' => 'removed',
            'due_date' => (new DateTime())->format('Y-m-d H:i:s'),
            'priority' => 1,
            'status' => 1,
        ));

        $this->task_management_service->removeTask(1);

        $confirm_task_removed = self::$db_task->query("SELECT * FROM task WHERE id = 1")->row;
        $this->assertEmpty($confirm_task_removed);
    }

    public function test_If_assignTaskTo_Adds_Assignee_To_Task_Assignee_Table_With_Given_Task_Id(){

        
        self::$db_iaa->insert('personnel', array(
            'id' => 2,
            'role_id' => 1,
            'department_id' => 1,
            'image_id' => null,
            'firstname' => 'Personnel Firstname 2',
            'lastname' => 'Personnel Lastname 2',
            'tcno' => 21223344550,
            'gender' => 'female',
            'phone' => '+90 12401220100',
            'email' => 'feridun@kant.x',
            'is_active' => 1,
            'date_added' => (new DateTime())->format('Y-m-d H:i:s'),
            'last_modification' => (new DateTime())->format('Y-m-d H:i:s')
        ));

        self::$db_task->insert('task', array(
            'id' => 2,
            'title' => 'Task Title 2',
            'assigner' => 1,
            'description' => 'Task Description 2',
            'due_date' => (new DateTime())->format('Y-m-d H:i:s'),
            'priority' => 1,
            'status' => 1,
        ));

        $this->task_management_service->assignTaskTo(2,1); 

        $arr_number_of_assignees = $this->task_management_service->getSelfOwnedTaskAssignees(2);
        $this->assertEquals($arr_number_of_assignees[0], 1);

    }

    public function test_If_deassignTaskFrom_Removes_Task_Assignee(){

         $this->task_management_service->deassignTaskFrom(2,1);

         $task_assignee_bin_id = self::$db->query("SELECT * FROM task_assignee_bin WHERE id = 1")->row['id'];

         $this->assertEquals(1, $task_assignee_bin);

    }

    public function test_If_addComment_Adds_A_New_Comment_To_Db(){

        self::$db_task->insert('task', array(
            'id' => 1,
            'title' => 'Task Title 2',
            'assigner' => 1,
            'description' => 'Task Description 2',
            'due_date' => (new DateTime())->format('Y-m-d H:i:s'),
            'priority' => 1,
            'status' => 1,
        ));
        
        $this->task_management_service->addComment(1, 'first message...');

        $task_id = self::$db_task->query('SELECT * FROM task_comment WHERE task_id = 1')->row['task_id'];
        $this->assertEquals(1, $task_id);
    }   

    public function test_If_editComment_Edits_Created_Comment(){

        $comment_id = 1;

        $params = array(
            'id' => $comment_id,
            'task_id' => 1,
            'commentator' => 1,
            'message' => 'second comment...',
            'commented_on' => (new DateTime())->format('Y-m-d H:i:s'),
            'edited_on' => (new DateTime())->format('Y-m-d H:i:s')
        );

        self::$db_task->insert('task_comment', $params);

        $this->task_management_service->editComment(1,1, 'edited task comment');

        $id = self::$db_task->query("SELECT * FROM task_comment WHERE id=1")->row['id'];
        $this->assertEquals(1, $id);
    }


    public function test_If_removeComment_Removes_Existing_Comment(){

        $this->task_management_service->removeComment(1,1);

        $confirm_removed = self::$db_task->query("SELECT * FROM task_comment WHERE id = 1")->row;
        $this->assertEmpty($confirm_removed);

        $confirm_added_to_bin = self::$db_task->query("SELECT * FROM task_comment_bin WHERE id = 1")->row['id'];
        $this->assertEquals(1, $confirm_added_to_bin);
    }


    public function test_If_addTaskAttachment_Adds_A_New_Task_Attachment(){

        $this->task_management_service->addTaskAttachment(1, 'base64,', 'Attachment Name 1');

        $name = self::$db_task->query("SELECT * FROM task_attachment WHERE name = 'Attachment Name 1'")->row['name'];
        $this->assertEquals($name, 'Attachment Name 1');

        $task_id = self::$db_task->query("SELECT * FROM task_attachment WHERE task_id = 1")->row['task_id'];
        $this->assertEquals(1, $task_id);
    }

    public function test_If_removeTaskAttachment_Removes_Attachment_With_Given_Id(){
         
        $attachment_vm = $this->task_management_service->getTaskMostRecentSelfOwnedAttachment(1);
        /* vm = view model */

        $attachment_arr = json_decode(json_encode($attachment_vm), true);

        $id = $attachment_arr['id'];

        $this->task_management_service->removeTaskAttachment(1, $id);

        $attachment_empty = self::$db_task->query("SELECT * FROM task_attachment WHERE id= $id")->row;
        $this->assertEmpty($attachment_empty);

        $attachment_bin = self::$db_task->query("SELECT * FROM task_attachment_bin WHERE id = $id")->row['id'];
        $this->assertEquals($attachment_bin, $id);
    }

    public function test_If_batchUpdateTask_Updates_Task(){

        $this->task_management_service->batchUpdateTask(
            1,
            'Updated Task Title',
            'Updated Task Description',
            null,
            null,
            null,
            1,
            1
        );

        $changed_title = self::$db_task->query("SELECT * FROM task WHERE id = 1")->row['title'];
        $this->assertEquals('Updated Task Title', $changed_title);

        $changed_description = self::$db_task->query("SELECT * FROM task WHERE id = 1")->row['description'];
        $this->assertEquals('Updated Task Description' ,$changed_description);
    }


    public function test_If_createSubtask_Creates_A_New_Subtask(){

        $this->task_management_service->createSubtask(
            
            1,
            'Subtask Title 1',
            null,
            'Subtask Description 1',
            null,
            null,
            null,
            1,
            1
        );

        $subtask_vm = $this->task_management_service->getSelfOwnedSubtasksOfTask(1);

        $subtask_arr = json_decode(json_encode($subtask_vm), true);

        $id = $subtask_arr[0]['id'];

        $subtask_id_from_db = self::$db_task->query("SELECT * FROM subtask WHERE id = $id")->row['id'];
        $this->assertEquals($id, $subtask_id_from_db);
    }


    public function test_If_removeSubtask_Removes_Created_Subtask(){
       self::$db_task->insert('subtask', array(
            'id' => 1,
            'task_id' => 1,
            'title' => 'Title 2',
            'assigner' => 1,
            'description' => 'Description 2',
            'due_date' => (new DateTime())->format('Y-m-d H:i:s'),
            'priority' => 1,
            'status' => 1,
        ));

       $this->task_management_service->removeSubtask(1);

       $subtask_empty = self::$db_task->query("SELECT * FROM subtask WHERE id = 1")->row;
       $this->assertEmpty($subtask_empty);

       $stored_to_subtask_bin = self::$db_task->query("SELECT * FROM subtask_bin WHERE id = 1")->row['id'];
       $this->assertEquals($stored_to_subtask_bin, 1);
    }

    public function test_If_assignSubtaskTo_Assigns_Subtask_To_Assignee(){

        self::$db_task->insert('subtask', array(
            'id' => 1,
            'task_id' => 1,
            'title' => 'Subtask Title 2',
            'assigner' => 1,
            'description' => 'Subtask Description 2',
            'due_date' => (new DateTime())->format('Y-m-d H:i:s'),
            'priority' => 1,
            'status' => 1
        ));

        $this->task_management_service->assignSubtaskTo(1,1); /* subtask id , assingee id */

        $arr_number_of_assignees = $this->task_management_service->getSelfOwnedSubtaskAssignees(1,1); /* returns an array which contains the number of the assignees */
        $this->assertEquals($arr_number_of_assignees[0], 1);
    }


    public function test_If_deassignSubtaskFrom_Removes_Subtask_Assignee(){ 

        $this->task_management_service->deassignSubtaskFrom(1,1); /* subtask_id , assignee_id */

        $subtask_assignee_bin_id = self::$db->query("SELECT * FROM subtask_assignee_bin WHERE id = 1")->row['id'];        

        $this->assertEquals(1, $subtask_assignee_bin_id);
    }


    public function test_If_commentOnSubtask_Adds_Comment_To_Subtask(){

        $this->task_management_service->commentOnSubtask(1, 'this is the subtask comment...');

        $subtask_comment_vm = $this->task_management_service->getSubtaskMostRecentSelfOwnedComment(1,1); /* task_id, subtask_id */

        $subtask_comment_arr = json_decode(json_encode($subtask_comment_vm), true);

        $id = $subtask_comment_arr['id'];
        $id_from_db = self::$db_task->query("SELECT * FROM subtask_comment WHERE id = $id")->row['id'];

        $this->assertEquals($id, $id_from_db);
    }   

    public function test_If_editSubtaskComment_Edits_Created_Comment(){

        self::$db_task->insert('subtask_comment' , array(
            'id' => 1,
            'subtask_id' => 1,
            'commentator' => 1,
            'message' => 'dummy message',
            'commented_on' => (new DateTime())->format('Y-m-d H:i:s'),
            'edited_on' => (new DateTime())->format('Y-m-d H:i:s'),
        ));

        $this->task_management_service->editSubtaskComment(1,1, 'Updated Subtask Comment');

        $db_msg = self::$db_task->query("SELECT * FROM subtask_comment WHERE id = 1")->row['message'];

        $this->assertEquals('Updated Subtask Comment', $db_msg);
    }

    public function test_If_removeSubtaskComment_Removes_Existing_Comment(){

        $this->task_management_service->removeSubtaskComment(1,1);

        $confirm_removed = self::$db_task->query("SELECT * FROM subtask_comment WHERE id = 1")->row;
        $this->assertEmpty($confirm_removed);

        $confirm_carried_to_bin = self::$db_task->query("SELECT * FROM subtask_comment_bin WHERE id = 1")->row['id'];
        $this->assertEquals(1, $confirm_carried_to_bin);
    }

    public function test_If_addSubtaskAttachment_Adds_New_Subtask_Attachment_To_Db(){

        $this->task_management_service->addSubtaskAttachment(1,1,'base64,', 'Subtask Attachment Name 1');

        $subtask_attachment_vm = $this->task_management_service->getSubtaskMostRecentSelfOwnedAttachment(1,1);

        $subtask_attachment_arr = json_decode(json_encode($subtask_attachment_vm), true);

        $id = $subtask_attachment_arr['id'];

        $id_from_db = self::$db_task->query("SELECT * FROM subtask_attachment WHERE id = $id")->row['id'];

        $this->assertEquals($id, $id_from_db);
    }

    public function test_If_removeSubtaskAttachment_Removes_Created_Subtask_Attachment(){

        $this->task_management_service->addSubtaskAttachment(1,1,'base64,', 'Subtask Attachment Name 2');

        $subtask_attachment_vm = $this->task_management_service->getSubtaskMostRecentSelfOwnedAttachment(1,1);

        $subtask_attachment_arr = json_decode(json_encode($subtask_attachment_vm), true);

        $id = $subtask_attachment_arr['id'];

        $this->task_management_service->removeSubtaskAttachment(1,1,$id); /* task_id, subtask_id, attachment_id */


        $confirm_removed = self::$db_task->query("SELECT * FROM subtask_attachment WHERE id = $id")->row;
        $this->assertEmpty($confirm_removed);

        $attachment_bin = self::$db_task->query("SELECT * FROM subtask_attachment_bin WHERE id = $id")->row['id'];
        $this->assertEquals($id, $attachment_bin);
    
    }

    public function test_If_batchUpdateSubtask_Updates_Existing_Subtask(){

        $this->task_management_service->batchUpdateSubtask(
            1,
            'Updated Subtask Title',
            'Updated Description',
            null,
            null,
            null,
            1,
            1
        );

        $updated_title = self::$db_task->query("SELECT * FROM subtask WHERE id = 1")->row['title'];
        $this->assertEquals('Updated Subtask Title', $updated_title);

        $updated_description = self::$db_task->query("SELECT * FROM subtask WHERE id = 1")->row['description'];
        $this->assertEquals('Updated Description', $updated_description);
    }

    public function test_If_getSelfOwnedTasks_Returns_Existing_Tasks_From_Db(){

        $task_query_dto = $this->task_management_service->getSelfOwnedTasks(new QueryObject());
        $dto_encode = json_decode(json_encode($task_query_dto), true);
        
        $tasks_arr = $dto_encode['tasks'];    
        $this->assertCount(3, $tasks_arr);

    }

    public function test_If_getSingleSelfOwnedTask_Returns_Task_ViewModel_From_Db(){

        $task_dto = $this->task_management_service->getSingleSelfOwnedTask(1);
        $task_arr = json_decode(json_encode($task_dto), true);

        $id = $task_arr['id'];
        $id_from_db = self::$db_task->query("SELECT * FROM task WHERE id = $id")->row['id'];

        $this->assertEquals($id, $id_from_db);
    }

    public function test_If_getSingleSelfOwnedSubtask_Returns_Subtask_VM_From_Db(){

        $subtask_dto = $this->task_management_service->getSingleSelfOwnedSubtask(1);
        $subtask_arr = json_decode(json_encode($subtask_dto), true);

        $id = $subtask_arr['id'];
        $id_from_db = self::$db_task->query("SELECT * FROM subtask WHERE id = $id")->row['id'];

        $this->assertEquals($id, $id_from_db);
    }

    public function test_If_getLastCreatedSelfOwnedSubtask_Returns_The_Last_Created_Subtask_Of_Given_Task_Id(){

        self::$db_task->insert('subtask', array(
            'id' => 2,
            'task_id' => 1,
            'title' => 'Last Subtask Title',
            'description' => 'Last Subtask Description',
            'due_date' => null,
            'priority' => 1,
            'status' => 1,
            'created_on' => (new DateTime())->format('Y-m-d H:i:s'),
            'assigner' => 1,
            'start_date' => 1
        ));

        $last_subtask_vm = $this->task_management_service->getLastCreatedSelfOwnedSubtask(1); /* task id */
        $subtask_arr = json_decode(json_encode($last_subtask_vm), true);

        $id = $subtask_arr['id'];
        $id_from_db = self::$db_task->query("SELECT * FROM subtask WHERE id = 2")->row['id'];
        
        $this->assertEquals($id, $id_from_db);
    }

    public function test_If_getSelfOwnedTaskComments_Returns_Array_Of_Comment_VM(){

        self::$db_task->insert('task_comment', array(
            'id' => 1,
            'task_id' => 1,
            'commentator' => 1,
            'message' => 'second message...',
            'commented_on' => (new DateTime())->format('Y-m-d H:i:s'),
            'edited_on' => (new DateTime())->format('Y-m-d H:i:s')
        ));

        $arr_subtask_comment_vm = $this->task_management_service->getSelfOwnedTaskComments(1);
        $subtask_arr = json_decode(json_encode($arr_subtask_comment_vm), true);

        $this->assertCount(2, $subtask_arr); /* 2 task comments exist in the db */
    }

    public function test_If_getSelfOwnedTaskCommentCommentator_Returns_The_Task_Commentator_Id()

    {
        $commentator_id = $this->task_management_service->getSelfOwnedTaskCommentCommentator(1,1); /* task_id , comment_id */
        $this->assertEquals(1, $commentator_id);
    }

    public function test_If_getTaskMostRecentSelfOwnedComment_Returns_The_Newest_Task_Comment(){


        self::$db_task->insert('task_comment', array(
            'id' => 2,
            'task_id' => 2,
            'commentator' => 1,
            'message' => 'latest message',
            'commented_on' => (new DateTime())->format('Y-m-d H:i:s'),
            'edited_on' => (new DateTime())->format('Y-m-d H:i:s')
        ));

        $comment_vm = $this->task_management_service->getTaskMostRecentSelfOwnedComment(2);
        $comment_arr = json_decode(json_encode($comment_vm), true);

        $id = $comment_arr['id'];
        $this->assertEquals($id, 2);
    }

    public function test_If_getTaskMostRecentEditedSelfOwnedComment_Returns_Most_Recent_Edited_Comment(){

        $this->task_management_service->editComment(1,1, 'this is the most recent edit !...');

        $recent_comment_vm = $this->task_management_service->getTaskMostRecentEditedSelfOwnedComment(1);
        $recent_comment_arr = json_decode(json_encode($recent_comment_vm), true);

        $id = $recent_comment_arr['id'];

        $id_from_db = self::$db_task->query("SELECT * FROM task_comment WHERE id = $id")->row['id'];
        $this->assertEquals($id, $id_from_db);
    }

    public function test_If_getSelfOwnedSubtaskComments_Returns_An_Array_Of_Comment_VM(){

        self::$db_task->insert('subtask_comment', array(
            'id' => 1,
            'subtask_id' => 1,
            'commentator' => 1,
            'message' => 'new subtask msg',
            'commented_on' => '2020-09-23 12:00:00',
            'edited_on' => '2020-09-23 12:00:00'
        ));

        $comment_arr_vm = $this->task_management_service->getSelfOwnedSubtaskComments(1);
        $this->assertCount(2, $comment_arr_vm); /* 2 comment vm arr inside */
    }

    public function test_If_getSelfOwnedSubtaskCommentCommentator_Returns_Commentator_Id(){

        $commentator_id = $this->task_management_service->getSelfOwnedSubtaskCommentCommentator(1,1,1);
        $this->assertEquals(1, $commentator_id);
    }

    public function test_If_getSubtaskMostRecentEditedSelfOwnedComment_Returns_Most_Recent_Comment_VM(){
        /*most recent comment*/
        self::$db_task->insert('subtask_comment', array(
            'id' => 2,
            'subtask_id' => 1,
            'commentator' => 1,
            'message' => 'this message should show...',
            'commented_on' => (new DateTime())->format('Y-m-d H:i:s'),
            'edited_on' => (new DateTime())->format('Y-m-d H:i:s')
        ));

        $comment_vm = $this->task_management_service->getSubtaskMostRecentEditedSelfOwnedComment(1,1);
        $comment_arr = json_decode(json_encode($comment_vm), true);

        $id = $comment_arr['id'];
        $db_id = self::$db_task->query("SELECT * FROM subtask_comment WHERE id = $id")->row['id'];

        $this->assertEquals($id, $db_id);
    }

    public function test_If_getSelfOwnedTaskEvents_Returns_Array_Of_Event_ViewModel(){

        $event_vm_arr = $this->task_management_service->getSelfOwnedTaskEvents(1);

        $events_arr = json_decode(json_encode($event_vm_arr), true);
        $this->assertCount(7, $events_arr);

        $events_vm_arr2 = $this->task_management_service->getSelfOwnedTaskEvents(2);

        $event_arr2 = json_decode(json_encode($events_vm_arr2), true);
        $this->assertCount(1, $event_arr2);
    }

    public function test_If_getSelfOwnedSubtaskEvents_Returns_Array_Of_Event_ViewModel(){

        $event_vm_arr = $this->task_management_service->getSelfOwnedSubtaskEvents(1);

        $event_arr = json_decode(json_encode($event_vm_arr), true);
        $this->assertCount(7, $event_arr);
    }

    public function test_If_getSelfOwnedTaskAttachments_Returns_An_Array_Of_Attachment_View_Model(){

        $this->task_management_service->addTaskAttachment(1,'base64,', 'Attachment 1');

        $task_attachment_vm = $this->task_management_service->getSelfOwnedTaskAttachments(1);

        $task_attachment_arr = json_decode(json_encode($task_attachment_vm), true);

        $this->assertIsArray($task_attachment_arr);

        $id = $task_attachment_arr[0]['id'];

        $db_id = self::$db_task->query("SELECT * FROM task_attachment WHERE id = $id")->row['id'];

        $this->assertEquals($id, $db_id);
    }

    public function test_If_getSelfOwnedTaskAttachmentUploader_Returns_Task_Attachment_Uploaders_Id(){

       $this->task_management_service->addTaskAttachment(2,'base64,', 'Attachment 2');

        $task_attachment_vm = $this->task_management_service->getSelfOwnedTaskAttachments(2);

        $task_attachment_arr = json_decode(json_encode($task_attachment_vm), true);

        $id = $task_attachment_arr[0]['id'];

        $task_attachment_uploader_id = $this->task_management_service->getSelfOwnedTaskAttachmentUploader(2,$id);

        $this->assertEquals(1, $task_attachment_uploader_id);
    }

    public function test_If_getTaskMostRecentSelfOwnedAttachment_Returns_Attachment_View_Model(){

        $task_attachment_vm = $this->task_management_service->getTaskMostRecentSelfOwnedAttachment(2); /* task_id */

        $task_attachment_arr = json_decode(json_encode($task_attachment_vm), true);

        $id = $task_attachment_arr['id'];

        $db_task_id = self::$db_task->query("SELECT * FROM task_attachment WHERE id = $id")->row['id'];

        $this->assertEquals($id, $db_task_id);
    }

    public function test_If_getSelfOwnedSubtaskAttachments_Returns_Subtask_Attachment_View_Model(){

        $subtask_attachment_vm = $this->task_management_service->getSelfOwnedSubtaskAttachments(1); /* subtask_id */

        $subtask_attachment_arr = json_decode(json_encode($subtask_attachment_vm), true);
        
        $id = $subtask_attachment_arr[0]['id'];

        $subtask_attachment_id_db = self::$db_task->query("SELECT * FROM subtask_attachment WHERE id = $id")->row['id'];

        $this->assertEquals($id, $subtask_attachment_id_db);
    }

    public function test_If_getSelfOwnedSubtaskAttachmentUploader_Returns_Uploader_Id(){

       $this->task_management_service->addSubtaskAttachment(1,1,'base64,', 'Subtask Attachment Name 2');

        $subtask_attachment_vm = $this->task_management_service->getSubtaskMostRecentSelfOwnedAttachment(1,1);

        $subtask_attachment_arr = json_decode(json_encode($subtask_attachment_vm), true);

        $id = $subtask_attachment_arr['id'];

        $subtask_uploader_id = $this->task_management_service->getSelfOwnedSubtaskAttachmentUploader(1,1,$id);
        $this->assertEquals(1, $subtask_uploader_id);
    }
}

?>