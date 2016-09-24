<?php
App::uses('AppModel', 'Model');

class AclGroup extends AclPermissionAppModel {
    public $actsAs = [
        'Acl' => [
            'type' => 'requester'
        ]
    ];

    public $useTable = false;

    public function beforeFind($query = []) {
		$this->useTable = Inflector::tableize(Configure::read('userRoleModel'));
    }

    public function parentNode() {
        return null;
    }
}