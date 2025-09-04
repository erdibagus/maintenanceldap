<?php
class UsersController extends AppController{
	public $components = array('Function');
    
    private $ldapHost = "ldap://192.168.0.101";
    private $ldapPort = 389;
    private $ldapAdminDn = "cn=admin,dc=bagus,dc=local";
    private $ldapAdminPass = "bagus";
    private $baseDn = "dc=bagus,dc=local";
	
	function index(){
		// $this->Function->cekSession($this);
	}

    public function getData($filter = "(objectClass=*)", $attributes = []) {
        $this->autoRender = false;

        $ou = $_POST['ou'];
        $nama = $_POST['nama'];

        if($nama) $filter = "(cn=*$nama*)";

        try {
            $conn = $this->Function->ldapConnect(true);

            $search = ldap_search($conn, "ou=$ou,".$this->Function->ldapConfig['base_dn'], $filter, $attributes);
            if (!$search) {
                throw new Exception("Search gagal: " . ldap_error($conn));
            }

            // ambil hasil
            $entries = ldap_get_entries($conn, $search);
            ldap_unbind($conn);

            // output JSON
            echo json_encode($entries);

        } catch (Exception $e) {
            echo json_encode(["error" => $e->getMessage()]);
        } finally {
            $this->Function->ldapDisconnect($conn);
        }
    }

    public function hapus() {
        $this->autoRender = false;
        $uid = $_POST['uid'] ?? null;
        $ou = $_POST['ou'] ?? null;
        if (!$uid) return print "UID kosong";

        try {
            $conn = $this->Function->ldapConnect(true);
            $dn = "uid=$uid,ou=$ou," . $this->Function->ldapConfig['base_dn'];
            if (ldap_delete($conn, $dn)) {
                echo "sukses";
            } else {
                echo "gagal: " . ldap_error($conn);
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        } finally {
            $this->Function->ldapDisconnect($conn);
        }
    }

    public function tambah() {
        $this->autoRender = false;
        $uid   = $_POST['id'] ?? '';
        $sn    = $_POST['nama'] ?? '-';
        $cn    = $_POST['username'] ?? '';
        $pass  = $_POST['password'] ?? '';
        $email = $_POST['email'] ?? '';
        $ou = $_POST['ou'] ?? '';

        try {
            $conn = $this->Function->ldapConnect(true);
            $dn = "uid=$uid,ou=$ou," . $this->Function->ldapConfig['base_dn'];
            $salt = random_bytes(4);
            $hash = sha1($pass . $salt, true) . $salt;
            $entry = [
                "cn" => $cn,
                "sn" => $sn,
                "uid" => $uid,
                "mail" => $email,
                "objectClass" => ["inetOrgPerson", "top"],
                "userPassword" => "{SSHA}" . base64_encode($hash),
                "pwdPolicySubentry" => "cn=default,ou=policies,". $this->Function->ldapConfig['base_dn']
            ];
            if (ldap_add($conn, $dn, $entry)) {
                echo "sukses";
            } else {
                echo "gagal: " . ldap_error($conn);
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        } finally {
            $this->Function->ldapDisconnect($conn);
        }
    }

    public function ubah() {
        $this->autoRender = false;
        $uidLama   = $_POST['idLama'] ?? '';
        $uid   = $_POST['id'] ?? '';
        $sn    = $_POST['nama'] ?? '-';
        $cn    = $_POST['username'] ?? '';
        $pass = $_POST['password'] ?? '';
        $email = $_POST['email'] ?? '';
        $ouLama = $_POST['ouLama'] ?? '';
        $ou = $_POST['ou'] ?? '';

        try {
            // $conn = $this->ldapConnect();
            $conn = $this->Function->ldapConnect(true);
            $dn = "uid=$uidLama,ou=$ouLama," . $this->Function->ldapConfig['base_dn'];
            $salt = random_bytes(4);
            $hash = sha1($pass . $salt, true) . $salt;
            $entry = [
                "cn" => $cn,
                "sn" => $sn,
                "mail" => $email,
                "userPassword" => "{SSHA}" . base64_encode($hash),
                "pwdPolicySubentry" => "cn=default,ou=policies," . $this->Function->ldapConfig['base_dn']
            ];
            if (ldap_modify($conn, $dn, $entry)) {
                echo "sukses";
                if($uidLama !== $uid || $ouLama !== $ou){
                    $this->ubahUid($uidLama, $uid, $ouLama, $ou);
                }
            } else {
                echo "gagal: " . ldap_error($conn);
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        } finally {
            $this->Function->ldapDisconnect($conn);
        }
    }

    public function ubahUid($uidLama, $uidBaru, $ouLama, $ouBaru) {
        $this->autoRender = false;

        try {
            $conn = $this->ldapConnect();

            $dn = "uid=$uidLama,ou=$ouLama,dc=bagus,dc=local";

            $newrdn = "uid=$uidBaru";

            $newparent = "ou=$ouBaru,dc=bagus,dc=local";

            if (ldap_rename($conn, $dn, $newrdn, $newparent, true)) {
                // echo "sukses";
            } else {
                echo "gagal";
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        } finally {
            $this->Function->ldapDisconnect($conn);
        }
    }

}
	
?>
