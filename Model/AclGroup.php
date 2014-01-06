<?php
App::uses('AppModel', 'Model');

class AclGroup extends AclPermissionAppModel {
    public $actsAs = array('Acl' => array('type' => 'requester'));

    public $useTable = false;

    public function beforeFind($query = array()) {
		$this->useTable = Inflector::tableize(Configure::read('userRoleModel'));
    }

    public function parentNode() {
        return null;
    }
}