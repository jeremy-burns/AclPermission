AclPermission
=============

A really simple bit of code that takes permissions for new or existing Acos, creates them if needed and then sets the permissions in a repeatable manner.

What it does
------------

The Acl data is a bit hard to understand by simply looking at it and it's not that easy to add new Acos or their permisisons. There are some plugins out there that work fine, but I find them a bit slow, especially when operating on a large CakePHP application. I got a bit frustrated and also a bit apprehensive as it seems the whole Acl data could burst out of control at any time as my application evolves. I also tend to use a lot of 'eyeology' when checking and setting the permissions and wanted something more reliable.

I wrote this small plugin in a few hours to so some really simple stuff.

Objective: When I add a new controller or action to my app, I want to create the Aco for the new controller and actions and then set some permissions for it. I also wanted to be able to repeat this reliably during a release to staging and production when ready.

There are two key elements; a database table and a controller. The table stores the functions in a flat format, making it it easy to see at a glance what the permissions are going to be. The columns are:

* plugin: the name of the plugin - leave blank if this is a core code Aco (i.e. not in a plugin)
* controller: the name of the controller (case matches the name of the controller class, e.g. ContactManager)
* action: the name of the action you want to add
* allow: the ids of the groups you want to set this permission for, separated by '/' e.g. 1/3/4 - can be letters or numbers, but must match the ids of your groups
* deny: same as allow

I generally build sites that set permissions at the group level rather than by individual users, but I guess this could be accomodated at some future point.

When you run the code, the plugin loops through the records. For each row, it checks to see if the Aco already exists. If the Aco for 'controllers' (the base Aco that is parent for everything), the plugin, the controller or the action doesn't exist, it will create them. The code uses the id of the Aco as the parent of each subsquent Aco so that they are stored in the right place. The Acl component takes care of the tree structure. It then examines the 'allow' and 'deny' columns. It breaks the ids apart (so 1/3/4 becomes 1, 3 and 4 stored in an array) and calls the Acl component to set the allow or deny permissions on that function for each group in turn.

To install the Plugin
---------------------
* Find and run the acl_permissions.sql script in app/Config/Schema into your database
* Copy the Plugin into app/Plugin/AclPermission
* Edit app/Config/bootstrap.php:
CakePlugin::load(array(
	// your existing plugins...,
	'AclPermission'
);
* Add this function to a controller:

public function set_permissions() {
	$this->AclPermission->AclPermission->set_permissions();
}

* Grant Auth permissions to that action in the controller's beforeFilter():
$this->Auth->allow('set_permissions');

You could this to the acl_permissions tbale so that permissions are set properly, then remove the Auth->allow statement for future runs. The row would look like this:
* plugin: 'AclPermission'
* controller: 'AclPermission'
* action: 'set_permissions'
* allow: '1' (assuming your admin role/group has an id of 1)
* deny: blank

To add new Acos and their permissions
-------------------------------------

To add new Acos and their permissions, add rows to the acl_permissions table that match your new objects. See column definitions above.

To run the permission setting code
----------------------------------

Navigate to [yoursite]/[controller_name]/acl_permission/acl_permissions/set_permissions

When complete, it redirects to the route of your site with a success message.

What's missing?
---------------

No tests yet, but they will come.

What's coming?
--------------

* Some views for showing and changing the permissions you want to set
* A 'reverse engineer' function that extracts the rows from your acos and aros_acos tables into the flat format that's easy to digest
* A 'complete rebuild' option so that your acos and aros_acos tables are cleared and rebuilt based on your new permissions
