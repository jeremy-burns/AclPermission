<?php
	Router::connect(
		'/set-permissions',
		array(
			'plugin' => 'acl_permission',
			'controller' => 'acl_permissions',
			'action' => 'set_permissions'
		)
	);

	Router::connect(
		'/reverse-engineer',
		array(
			'plugin' => 'acl_permission',
			'controller' => 'acl_permissions',
			'action' => 'reverse_engineer'
		)
	);