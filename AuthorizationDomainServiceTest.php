<?php 

use PHPUnit\Framework\TestCase;

use model\IdentityAndAccess\domain\model\AuthorizationDomainService;
use model\IdentityAndAccess\domain\model\Personnel;
use model\IdentityAndAccess\domain\model\PersonnelId;
use model\IdentityAndAccess\domain\model\RoleId;
use model\IdentityAndAccess\domain\model\Role;
use model\common\domain\model\SubmoduleId;
use model\IdentityAndAccess\domain\model\Privilege;

/*
* personnel isActive degilse tumu false donmeli
* role null ise tumu false donmeli
* role icerisinde submodule_id ye denk gelen bir privilege yoksa tumu false donmeli
* 
*/

class AuthorizationDomainServiceTest extends TestCase {


	private AuthorizationDomainService $authorization_domain_service;


	protected function setUp() : void {
		$this->authorization_domain_service = new AuthorizationDomainService();
	}



	/************		Testing Role_Null		************/





	public function testPersonnelCannotViewIfRoleIsNull() {
      
        $personnel = new Personnel(new PersonnelId(1), null, 1, "asdadsad", "asdadasdasd", "12345678901", "female", "312313123213", "asdasd@asdads.com", null, null);

        $role = null;

        $submodule_id = new SubmoduleId(1);

        $this->assertFalse($this->authorization_domain_service->canView($personnel, $role, $submodule_id));
    }



	public function testPersonnelCannotCreateIfRoleIsNull() {
      
        $personnel = new Personnel(new PersonnelId(1), null, 1, "asdadsad", "asdadasdasd", "12345678901", "female", "312313123213", "asdasd@asdads.com", null, null);

        $role = null;

        $submodule_id = new SubmoduleId(1);

        $this->assertFalse($this->authorization_domain_service->canCreate($personnel, $role, $submodule_id));
    }




	public function testPersonnelCannotUpdateIfRoleIsNull() {
        
        $personnel = new Personnel(new PersonnelId(1), null, 1, "asdadsad", "asdadasdasd", "12345678901", "female", "312313123213", "asdasd@asdads.com", null, null);

        $role = null;

        $submodule_id = new SubmoduleId(1);

        $this->assertFalse($this->authorization_domain_service->canUpdate($personnel, $role, $submodule_id));
    }




	public function testPersonnelCannotDeleteIfRoleIsNull() {
        $personnel = new Personnel(new PersonnelId(1), null, 1, "asdadsad", "asdadasdasd", "12345678901", "female", "312313123213", "asdasd@asdads.com", null, null);

        $role = null;

        $submodule_id = new SubmoduleId(1);

        $this->assertFalse($this->authorization_domain_service->canDelete($personnel, $role, $submodule_id));
    }



    /************		Testing Can_View 		************/


    

    public function testPersonnelCanViewIfPrivileged() {
    	$personnel = new Personnel(new PersonnelId(1), new RoleId(1), 1, "asdadsad", "asdadasdasd", "12345678901", "female", "312313123213", "asdasd@asdads.com", null, null);

    	$role = new Role(new RoleId(1), 'test_role');

    	$privilege = new Privilege(new SubmoduleId(2), false, false, false);

    	$role->addPrivilege($privilege);

    	$this->assertTrue($this->authorization_domain_service->canView($personnel, $role, new SubmoduleId(2)));

    }


    public function testPersonnelCanCreateIfPrivileged() {

    	$personnel = new Personnel(new PersonnelId(1), new RoleId(1), 1, "asdadsad", "asdadasdasd", "12345678901", "female", "312313123213", "asdasd@asdads.com", null, null);

    	$role = new Role(new RoleId(1), 'test_role');

    	$privilege = new Privilege(new SubmoduleId(2), true, false, false);

    	$role->addPrivilege($privilege);

    	$this->assertTrue($this->authorization_domain_service->canCreate($personnel, $role, new SubmoduleId(2)));
    }


     public function testPersonnelCanUpdateIfPrivileged() {
    	$personnel = new Personnel(new PersonnelId(1), new RoleId(1), 1, "asdadsad", "asdadasdasd", "12345678901", "female", "312313123213", "asdasd@asdads.com", null, null);

    	$role = new Role(new RoleId(1), 'test_role');

    	$privilege = new Privilege(new SubmoduleId(2), false, true, false);

    	$role->addPrivilege($privilege);

    	$this->assertTrue($this->authorization_domain_service->canUpdate($personnel, $role, new SubmoduleId(2)));

    }


    public function testPersonnelCanDeleteIfPrivileged() {
    	$personnel = new Personnel(new PersonnelId(1), new RoleId(1), 1, "asdadsad", "asdadasdasd", "12345678901", "female", "312313123213", "asdasd@asdads.com", null, null);

    	$role = new Role(new RoleId(1), 'test_role');

    	$privilege = new Privilege(new SubmoduleId(2), false, false, true);

    	$role->addPrivilege($privilege);

    	$this->assertTrue($this->authorization_domain_service->canDelete($personnel, $role, new SubmoduleId(2)));

    }





	/************		Testing Is_Active 		************/





    public function testPersonnelCannotViewIfIsNotActive() {

    	$personnel = new Personnel(new PersonnelId(1), null, false, "asdadsad", "asdadasdasd", "12345678901", "female", "312313123213", "asdasd@asdads.com", null, null);

    	$role = null;

    	$submodule_id= new SubmoduleId(1);

    	$this->assertFalse($this->authorization_domain_service->canView($personnel, $role,  $submodule_id));
    }


     public function testPersonnelCannotCreateIfIsNotActive() {

    	$personnel = new Personnel(new PersonnelId(1), null, false, "asdadsad", "asdadasdasd", "12345678901", "female", "312313123213", "asdasd@asdads.com", null, null);

    	$role = null;

    	$submodule_id= new SubmoduleId(1);

    	$this->assertFalse($this->authorization_domain_service->canCreate($personnel, $role,  $submodule_id));
    }


     public function testPersonnelCannotUpdateIfIsNotActive() {

    	$personnel = new Personnel(new PersonnelId(1), null, false, "asdadsad", "asdadasdasd", "12345678901", "female", "312313123213", "asdasd@asdads.com", null, null);

    	$role = null;

    	$submodule_id= new SubmoduleId(1);

    	$this->assertFalse($this->authorization_domain_service->canUpdate($personnel, $role,  $submodule_id));
    }


     public function testPersonnelCannotDeleteIfIsNotActive() {

    	$personnel = new Personnel(new PersonnelId(1), null, false, "asdadsad", "asdadasdasd", "12345678901", "female", "312313123213", "asdasd@asdads.com", null, null);

    	$role = null;

    	$submodule_id= new SubmoduleId(1);

    	$this->assertFalse($this->authorization_domain_service->canDelete($personnel, $role,  $submodule_id));
    
    }



    /************		Testing Privilege 		************/




    public function testPersonnelCannotUpdateIfNotPrivileged() {
    	$authorization_domain_service = new AuthorizationDomainService();

    	$personnel = new Personnel(new PersonnelId(1), new RoleId(2), 1, "asdadsad", "asdadasdasd", "12345678901", "female", "312313123213", "asdasd@asdads.com", null, null);

    	$role = new Role(new RoleId(2), 'test_role');

    	$privilege = new Privilege(new SubmoduleId(1), true, false, true);

    	$role->addPrivilege($privilege);

    	$this->assertFalse($authorization_domain_service->canUpdate($personnel, $role, new SubmoduleId(1))); 
    }



    public function testPersonnelCannotCreateIfNotPrivileged() {

    	$authorization_domain_service = new AuthorizationDomainService();

    	$personnel = new Personnel(new PersonnelId(1), new RoleId(2), 1, "asdadsad", "asdadasdasd", "12345678901", "female", "312313123213", "asdasd@asdads.com", null, null);

    	$role = new Role(new RoleId(1), 'test_role');

    	$privilege = new Privilege(new SubmoduleId(2), false, true, true);

    	$role->addPrivilege($privilege);

    	$this->assertFalse($authorization_domain_service->canCreate($personnel, $role, new SubmoduleId(2)));
    }


    public function testPersonnelCannotDeleteIfNotPrivileged() {

    	$authorization_domain_service = new AuthorizationDomainService();

    	$personnel = new Personnel(new PersonnelId(1), new RoleId(2), 1, "asdadsad", "asdadasdasd", "12345678901", "female", "312313123213", "asdasd@asdads.com", null, null);

    	$role = new Role(new RoleId(1), 'test_role');

    	$privilege = new Privilege(new SubmoduleId(3), true, true, false);

    	$role->addPrivilege($privilege);

    	$this->assertFalse($authorization_domain_service->canDelete($personnel, $role, new SubmoduleId(3))); 
    }




}


?>