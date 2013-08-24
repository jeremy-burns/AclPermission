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

    public function set_permissions() {

    	$permissions = $this->AclPermission->find('all');

    	if (!$permissions) {
    		echo 'No permissions to set';
    		exit;
    	}

    	$this->controllerAcoId = $this->__acoId('controllers', NULL, true);

    	if (!$this->controllerAcoId) {
    		echo 'No controller node';
    		exit;
    	}

    	foreach ($permissions as $permission) {

    		$permission = $permission['AclPermission'];

    		if ($this->__nodeExists($permission['controller'], $permission['action'], true)) {

    			if (!empty($permission['allow'])) {
    				$this->__set_permission('allow', $permission['allow'], $permission['controller'] . '/' . $permission['action']);
    			}
    			if (!empty($permission['deny'])) {
    				$this->__set_permission('deny', $permission['deny'], $permission['controller'] . '/' . $permission['action']);
    			}

    		}

    	}


    }

    private function __acoId($alias = '', $parentId = null, $autoCreate = false) {

    	$aco = $this->Acl->Aco->find(
			'first',
			array(
				'conditions' => array(
					'alias' => $alias,
					'parent_id' => $parentId
				)
			)
		);

		if (!empty($aco['Aco']['id'])) {
			return $aco['Aco']['id'];
		} else {
			return $this->__createNode($alias, $parentId);
		}

    }

	private function __nodeExists($controller = null, $action = null, $autoCreate = false) {

		$parentAcoId = $this->__acoId($controller, $this->controllerAcoId, $autoCreate);

		if ($parentAcoId) {
			return $this->__acoId($action, $parentAcoId, true);
		} else {
			return null;
		}

	}

	private function __createNode($alias = null, $parentAcoId = null) {

		if (!$alias) return false;

		$this->Acl->Aco->create(
			array(
				'parent_id' => $parentAcoId,
				'alias' => $alias
			)
		);

		if ($this->Acl->Aco->save()) {
			return $this->Acl->Aco->id;
		} else {
			return false;
		}

	}

	private function __getParentAco($acoNode = null) {

		foreach ($acoNodes as $key => $acoNode) {

			$parentAco = $this->Acl->Aco->find(
				'first',
				array(
					'conditions' => array(
						'alias' => $acoNode,
						'parent_id' => $parentAco
					)
				)
			);

			if ($parentAco) {
				$parentAco = $parentAco['Aco']['id'];
			} else {
				return null;
			}

		}

		return $parentAco;

	}

	private function __set_permission($permissionType = '', $groups = '', $newNode = '') {

		$groups = explode('/', $groups);

		foreach ($groups as $groupId) {

			$aclGroup = $this->AclGroup->findById($groupId);

			$group['Group'] = $aclGroup['AclGroup'];

			if ($group) {
				if ($permissionType == 'allow') {
					$this->Acl->allow($group, $newNode);
				} else {
					$this->Acl->deny($group, $newNode);
				}
			}

		}

		$this->Session->setFlash(
			'The Permissions have been set.',
			'flash/success'
		);

		$this->redirect('/');

	}

}