<?php
App::uses('AppModel', 'Model');

class AclAcos extends AclPermissionAppModel {

    public $useTable = 'acos';

    public function reverseEngineer() {

        $aco = $this->find(
            'threaded',
            ['contain' => 'ArosAcos']
        );

        die(debug($aco));

    }

}