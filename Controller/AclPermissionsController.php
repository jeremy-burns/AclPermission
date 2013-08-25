<?php
App::uses('AclComponent', 'Controller/Component');
App::uses('AuthComponent', 'Controller/Component');
App::uses('SessionComponent', 'Controller/Component');

class AclPermissionsController extends AclPermissionAppController {

    public $uses = array(
    	'AclPermission.AclPermission',
    	'AclPermission.AclGroup'
    );

    public $name = 'AclPermissions';

    public function beforeFilter() {

    	parent::beforeFilter();

    	$this->Auth->allow();

    }

    /**
     * The core function that is run from the address bar
     **/

    public function set_permissions() {

    	// Get all of the permissions rows
    	$permissions = $this->AclPermission->find(
    		'all',
    		array(
    			'conditions' => array(
    				'AclPermission.complete' => 0
    			)
    		)
    	);

    	// If there are none, stop here
    	if (!$permissions) {

    		$this->Session->setFlash(
				'No permissions to set.',
				'flash/error'
			);

			$this->redirect('/');
    	}

    	// Make sure there is a root controllers Aco - this will try to create it if it's missing.
    	$this->controllerAcoId = $this->__acoId('controllers', NULL, true);

    	// If it's missing there's something wrong beyoind the scope of this plugin
    	if (!$this->controllerAcoId) {

    		$this->Session->setFlash(
				'Couldn\'t create the controlelrs Aco.',
				'flash/error'
			);

			$this->redirect('/');

    	}

    	// Loop through the permissions
    	foreach ($permissions as $permission) {

    		// Get the subkeys
    		$permission = $permission['AclPermission'];

    		// Create a node string - this handles plugin/no plugin etc.
    		$node = implode(
    			'/',
    			array(
    				$permission['plugin'],
    				$permission['controller'],
    				$permission['action']
    			)
    		);

    		// Strip a leading '/'
    		$node = ltrim ($node,'/');

    		// Check the node exists - this will create them if needed if the second parameter is set to true
    		if ($this->__nodeExists($node, true)) {

    			// A convenience array
    			$permissionTypes = array(
    				0 => 'allow',
    				1 => 'deny'
    			);

    			// Examine and set each permission type
    			// $permission[$permissionType] contains the concatentated list of group ids to set perms for
    			foreach ($permissionTypes as $permissionType) {
    				$this->__set_permission($permissionType, $permission, $node);
    			}

    		}

    	}

    	// Done
    	$this->Session->setFlash(
			'The permissions have been set.',
			'flash/success'
		);

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
    private function __acoId($alias = '', $parentId = null, $autoCreate = false) {

    	// Try and find the Aco using the alias and its parent
    	$aco = $this->Acl->Aco->find(
			'first',
			array(
				'conditions' => array(
					'alias' => $alias,
					'parent_id' => $parentId
				)
			)
		);

    	// If found, return its id
		if (!empty($aco['Aco']['id'])) {
			return $aco['Aco']['id'];
		} else {
			// If allowed, try to create the node
			if ($autoCreate) {
				return $this->__createNode($alias, $parentId);
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
	private function __nodeExists($node, $autoCreate = false) {

		// The node comes in as a path with an unknown number of elements, so strip it apart
		$node = explode('/', $node);

		// Start of with the controllers node as the parent (top of the tree)
		$parentAcoId = $this->controllerAcoId;

		if ($parentAcoId) {
			foreach ($node as $aco) {
				// Now loop through each node part checking and creating
				$parentAcoId = $this->__acoId($aco, $parentAcoId, true);
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
	private function __createNode($alias = null, $parentAcoId = null) {

		// If no alias is suplpied, get outta here
		if (!$alias) return false;

		// Create the Aco - alias belongs to parent
		$this->Acl->Aco->create(
			array(
				'parent_id' => $parentAcoId,
				'alias' => $alias
			)
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
	private function __set_permission($permissionType = '', $permission, $node) {

		// $permission[$permissionType] holds the group ids concatenated with '/'
		// so break them apart
		$groups = explode('/', $permission[$permissionType]);

		// Now loop through them
		foreach ($groups as $groupId) {

			// Get the actual Group
			$aclGroup = $this->AclGroup->findById($groupId);

			// If the Group does not exist, get outta here
			if (empty($aclGroup['AclGroup'])) {
				return false;
			}

			// As the key is AclGroup rather than Group, create a new properly formed variable
			$group['Group'] = $aclGroup['AclGroup'];

			// Make sure the group is OK
			if (!empty($group['Group'])) {

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

}