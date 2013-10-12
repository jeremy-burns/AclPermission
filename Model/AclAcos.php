<?php
App::uses('AppModel', 'Model');

class AclAcos extends AclPermissionAppModel {

    public $useTable = 'acos';

    public function reverse_engineer() {

    	$aco = $this->find(
			'threaded',
			array(
				'contain' => 'ArosAcos'
			)
		);

		die(debug($aco));

    }

}