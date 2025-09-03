<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class User extends AppModel {
	public $useTable = 'client';	
    public $useDbConfig = 'default'; 
}
