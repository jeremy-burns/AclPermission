<?php
App::uses('AppModel', 'Model');

class AclGroup extends AclPermissionAppModel {
    public $actsAs = array('Acl' => array('type' => 'requester'));

    public $useTable = 'groups';

    public function parentNode() {
        return null;
    }
}