<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class User extends AppModel {
	public $useTable = 'users';	
    public $useDbConfig = 'default'; 
}
