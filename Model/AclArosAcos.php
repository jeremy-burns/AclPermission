<?php
App::uses('AppModel', 'Model');

class AclArosAcos extends AclPermissionAppModel {

    public $useTable = 'aros_acos';

    public $belongsTo = [
        'AclAros',
        'AclAcos'
    ];

}