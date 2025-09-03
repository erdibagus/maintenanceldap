<?php
/**
 * Static content controller.
 *
 * This file will render views from views/pages/
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::uses('AppController', 'Controller');

/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class PagesController extends AppController {

/**
 * This controller does not use a model
 *
 * @var array
 */
	public $uses = array();

/**
 * Displays a view
 *
 * @param mixed What page to display
 * @return void
 * @throws NotFoundException When the view file could not be found
 *	or MissingViewException in debug mode.
 */
 	
public function display() 
{
	$path = func_get_args();

	$count = count($path);
	if (!$count) {
		return $this->redirect('/');
	}
	$page = $subpage = $title_for_layout = null;

	if (!empty($path[0])) {
		$page = $path[0];
	}
	if (!empty($path[1])) {
		$subpage = $path[1];
	}
	if (!empty($path[$count - 1])) {
		$title_for_layout = Inflector::humanize($path[$count - 1]);
	}
	$this->set(compact('page', 'subpage', 'title_for_layout'));

	try {
		$this->render(implode('/', $path));
	} catch (MissingViewException $e) {
		if (Configure::read('debug')) {
			throw $e;
		}
		throw new NotFoundException();
	}
}

public function logProcess(){
	$this->autoRender = false;

	$user=$_POST['username'];
	$pass=$_POST['password'];

	$ldapServer = "192.168.0.101";
	$ldapPort = 389; // Port default LDAP
	$ou = $this->cekOu($user);
	// var_dump($ou);exit();
	$ldapUser = "uid=$user,ou=$ou,dc=bagus,dc=local";
	$ldapPassword = $pass;

	$ldapConn = ldap_connect($ldapServer, $ldapPort) or die("Tidak bisa terhubung ke server LDAP.");

	if ($ldapConn) {
		$this->cekExpired($user,$ou,0);

		ldap_set_option($ldapConn, LDAP_OPT_PROTOCOL_VERSION, 3);
		ldap_set_option($ldapConn, LDAP_OPT_REFERRALS, 0);
		try {
			// Coba bind dengan user dan password
			$bind = @ldap_bind($ldapConn, $ldapUser, $ldapPassword);

			if (!$bind) {
				ldap_get_option($ldapConn, LDAP_OPT_DIAGNOSTIC_MESSAGE, $msg);
				throw new Exception($msg);
			}

			$this->cekExpired($user,$ou,1);
			
		} catch (Exception $e) {
			$this->cekLock($user, $ou);
		}

		// Tutup koneksi LDAP
		ldap_unbind($ldapConn);
	}
}

function cekLock($username, $ou){
	$this->autoRender = false;
	$ldap_host = "ldap://192.168.0.101";
	$ldap_dn = "cn=admin,dc=bagus,dc=local";     
	$ldap_password = "bagus";          
	$user_dn = "uid=$username,ou=$ou,dc=bagus,dc=local"; 
	$lockout_duration = 1000000; 
	

	$ldap_conn = ldap_connect($ldap_host);
	ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
	ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);

	if (!$ldap_conn) {
		die("Gagal koneksi ke LDAP");
	}

	if (!ldap_bind($ldap_conn, $ldap_dn, $ldap_password)) {
		die("Gagal bind sebagai admin LDAP");
	}

	$attributes = ['pwdaccountlockedtime'];
	$result = ldap_read($ldap_conn, $user_dn, '(objectClass=*)', $attributes);
	$entries = ldap_get_entries($ldap_conn, $result);

	// echo json_encode($entries);exit();
	if (!empty($entries[0]['pwdaccountlockedtime'][0])) {
		$lockedTimeStr = $entries[0]['pwdaccountlockedtime'][0]; //20250807032540Z

		$datetime = DateTime::createFromFormat('YmdHis\Z', $lockedTimeStr, new DateTimeZone('UTC'));
		$lockedTimestamp = $datetime->getTimestamp();

		$unlockTimestamp = $lockedTimestamp + $lockout_duration;
		$now = time();

		$remaining = $unlockTimestamp - $now;

		$minutes = floor($remaining / 60);
		$seconds = $remaining % 60;
		echo "salahpw3x!$remaining";
	} else {
		echo "password";
	}

	ldap_unbind($ldap_conn);
}

function cekOu($username){
	$this->autoRender = false;
	$ldap_host = "ldap://192.168.0.101";
	$ldap_dn   = "cn=admin,dc=bagus,dc=local"; 
	$ldap_pass = "bagus"; 
	$base_dn   = "dc=bagus,dc=local";

	// koneksi ke server
	$ldapconn = ldap_connect($ldap_host) or die("Tidak bisa konek ke LDAP");

	// set opsi
	ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
	ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);

	// bind pakai admin/admin
	if (@ldap_bind($ldapconn, $ldap_dn, $ldap_pass)) {
		// cari user berdasarkan uid
		$filter = "(uid=$username)";
		
		$result = ldap_search($ldapconn, $base_dn, $filter, ["dn"]);
		$entries = ldap_get_entries($ldapconn, $result);

		if ($entries["count"] > 0) {
			$dn = $entries[0]["dn"];

			// ambil OU dari DN
			preg_match('/ou=([^,]+)/i', $dn, $matches);
			if (!empty($matches[1])) {
				return $matches[1];
			}
		} else {
			echo "User tidak ditemukan" . PHP_EOL;
		}
	} else {
		echo "Gagal bind ke LDAP" . PHP_EOL;
	}

	ldap_unbind($ldapconn);

}

function cekExpired($username,$ou,$mode){
	$this->autoRender = false;
	$ldap_host = "ldap://192.168.0.101";
	$ldap_dn = "cn=admin,dc=bagus,dc=local";     
	$ldap_password = "bagus";          
	$user_dn = "uid=$username,ou=$ou,dc=bagus,dc=local"; 
	$lockout_duration = 30; 


	$ldap_conn = ldap_connect($ldap_host);
	ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
	ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);

	if (!$ldap_conn) {
		die("Gagal koneksi ke LDAP");
	}

	if (!ldap_bind($ldap_conn, $ldap_dn, $ldap_password)) {
		die("Gagal bind sebagai admin LDAP");
	}

	$attributes = ['+'];
	$result = ldap_read($ldap_conn, $user_dn, '(objectClass=*)', $attributes);
	$entries = ldap_get_entries($ldap_conn, $result);

	
	$pwdChangedTime = $entries[0]['pwdchangedtime'][0]; // format GeneralizedTime LDAP

	$pwdMaxAge = 1000000; // waktu kadaluarsa

	// Konversi pwdChangedTime ke timestamp (epoch)
	$year   = substr($pwdChangedTime, 0, 4);
	$month  = substr($pwdChangedTime, 4, 2);
	$day    = substr($pwdChangedTime, 6, 2);
	$hour   = substr($pwdChangedTime, 8, 2);
	$minute = substr($pwdChangedTime, 10, 2);
	$second = substr($pwdChangedTime, 12, 2);

	$changedEpoch = gmmktime($hour, $minute, $second, $month, $day, $year);

	// Hitung waktu kadaluarsa
	$expireEpoch = $changedEpoch + $pwdMaxAge;
	$nowEpoch    = time(); 

	// var_dump($nowEpoch,$expireEpoch);

	// Bandingkan
	if ($nowEpoch > $expireEpoch) {
		echo "expired";exit();
	} else {
		if($mode!=0){
			$remaining = $expireEpoch - $nowEpoch;
			$seconds = $remaining % 60;
			echo "sukses!$remaining";
		}
		
	}
}

}


#password expired 6 bulan
#apakah LDAP perlu pgBouncer seperti di posgreSql
#pssword 8 karakter