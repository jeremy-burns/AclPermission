<?php
App::uses('AclComponent', 'Controller/Component');
App::uses('AuthComponent', 'Controller/Component');
App::uses('FlashComponent', 'Controller/Component');

class AclPermissionsController extends AclPermissionAppController {

    public $uses = [
        'AclPermission.AclPermission',
        'AclPermission.AclGroup',
        'AclPermission.AclAcos'
    ];

    public $name = 'AclPermissions';

    private $permissionTypes = [
        0 => 'allow',
        1 => 'deny'
    ];

    public function beforeFilter()
    {
        $this->userRoleModel = Configure::read('userRoleModel');

        parent::beforeFilter();

        $this->Auth->allow('set_permissions', 'reverse_engineer');

    }

    /**
     * The core function that is run from the address bar
     **/

    public function set_permissions()
    {

        // Get all of the permissions rows
        $permissions = $this->AclPermission->find(
            'all',
            [
                'conditions' => [
                    'AclPermission.complete' => 0
                ]
            ]
        );

        // If there are none, stop here
        if (! $permissions) {
            $this->Flash->error('No permissions to set.');
            $this->redirect('/');
        }

        // Make sure there is a root controllers Aco - this will try to create it if it's missing.
        $this->controllerAcoId = $this->acoId('controllers', NULL, true);

        // If it's missing there's something wrong beyond the scope of this plugin
        if (! $this->controllerAcoId) {
            $this->Flash->error('Couldn\'t create the controlelrs Aco.');
            $this->redirect('/');
        }

        // Loop through the permissions
        foreach ($permissions as $permission) {

            // Get the subkeys
            $permission = $permission['AclPermission'];

            // Create a node string - this handles plugin/no plugin etc.
            $node = implode(
                '/',
                [
                    $permission['plugin'],
                    $permission['controller'],
                    $permission['action']
                ]
            );

            // Strip a leading '/'
            $node = ltrim ($node,'/');

            // Check the node exists - this will create them if needed if the second parameter is set to true
            if ($this->nodeExists($node, true)) {

                // Examine and set each permission type
                // $permission[$permissionType] contains the concatentated list of group ids to set perms for
                foreach ($this->permissionTypes as $permissionType) {
                    $this->setPermission($permissionType, $permission, $node);
                }

            }

        }

        // Done
        $this->Flash->success('The permissions have been set.');

        $this->redirect('/');

    }

/**
 * Gets the id of a node and can create if it is missing
 *
 * @param string $alias
 * @param string $parentId
 * @param int $autoCreate
 * @return array id int
 */
    private function acoId($alias = '', $parentId = null, $autoCreate = false)
    {

        // Try and find the Aco using the alias and its parent
        $aco = $this->Acl->Aco->find(
            'first',
            [
                'conditions' => [
                    'alias' => $alias,
                    'parent_id' => $parentId
                ]
            ]
        );

        // If found, return its id
        if (!empty($aco['Aco']['id'])) {
            return $aco['Aco']['id'];
        } else {
            // If allowed, try to create the node
            if ($autoCreate) {
                return $this->createNode($alias, $parentId);
            } else {
                return null;
            }
        }

    }

/**
 * Checks that a node exists - all along its path
 *
 * @param string $node
 * @param int $autoCreate
 * @return array id int
 */
    private function nodeExists($node, $autoCreate = false)
    {

        // The node comes in as a path with an unknown number of elements, so strip it apart
        $node = explode('/', $node);

        // Start of with the controllers node as the parent (top of the tree)
        $parentAcoId = $this->controllerAcoId;

        if ($parentAcoId) {
            foreach ($node as $aco) {
                // Now loop through each node part checking and creating
                $parentAcoId = $this->acoId($aco, $parentAcoId, true);
            }

            return $parentAcoId;

        } else {

            return null;

        }

    }

/**
 * Creates a node
 *
 * @param string $alias
 * @param int $parentAcoId
 * @return array id int
 */
    private function createNode($alias = null, $parentAcoId = null)
    {

        // If no alias is suplpied, get outta here
        if (!$alias) return false;

        // Create the Aco - alias belongs to parent
        $this->Acl->Aco->create(
            [
                'parent_id' => $parentAcoId,
                'alias' => $alias
            ]
        );

        // Try and save it
        if ($this->Acl->Aco->save()) {
            // If successful, return the id
            return $this->Acl->Aco->id;
        } else {
            // else, return false
            return false;
        }

    }

/**
 * Set permissions for a given node
 *
 * @param string $permissionType
 * @param int $groups
 * @param int $node
 * @return array booelan
 */
    private function setPermission($permissionType = '', $permission, $node)
    {

        // $permission[$permissionType] holds the group ids concatenated with '/'
        // so break them apart
        $groups = explode('/', $permission[$permissionType]);

        $userRoleModel = Configure::read('userRoleModel');

        // Now loop through them
        foreach ($groups as $groupId) {

            // Get the actual Group
            $aclGroup = $this->AclGroup->findById($groupId);

            // If the Group does not exist, get outta here
            if (empty($aclGroup['AclGroup'])) {
                return false;
            }

            // As the key is AclGroup rather than Group, create a new properly formed variable
            $group[$userRoleModel] = $aclGroup['AclGroup'];

            // Make sure the group is OK
            if (! empty($group[$userRoleModel])) {

                // Then act on the permission type

                // and set the appropraite permissions
                if ($permissionType == 'allow') {
                    $this->Acl->allow($group, $node);
                } else {
                    $this->Acl->deny($group, $node);
                }

                $this->AclPermission->id = $permission['id'];
                $this->AclPermission->set('complete', 1);
                $this->AclPermission->save();

            }

        }

        return;

    }

    public function reverse_engineer()
    {

        $aco = $this->AclAcos->reverseEngineer();

    }

}