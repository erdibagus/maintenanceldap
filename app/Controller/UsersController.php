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


    private function ldapConnect($bindDn = null, $password = null) {
        $conn = ldap_connect($this->ldapHost, $this->ldapPort);
        if (!$conn) {
            throw new Exception("Tidak bisa konek ke LDAP server");
        }
        ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($conn, LDAP_OPT_REFERRALS, 0);

        $bindDn = $bindDn ?? $this->ldapAdminDn;
        $password = $password ?? $this->ldapAdminPass;

        if (!@ldap_bind($conn, $bindDn, $password)) {
            throw new Exception("Bind gagal: " . ldap_error($conn));
        }
        return $conn;
    }

    private function ldapDisconnect($conn) {
        if ($conn && is_resource($conn)) {
            ldap_unbind($conn);
        }
    }

    public function getData($filter = "(objectClass=*)", $attributes = []) {
        $this->autoRender = false;

        $ou = $_POST['ou'];
        $nama = $_POST['nama'];

        if($nama) $filter = "(sn=*$nama*)";

        try {
            $conn = $this->ldapConnect();

            $search = ldap_search($conn, "ou=$ou,".$this->baseDn, $filter, $attributes);
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
            $this->ldapDisconnect($conn);
        }
    }


    public function save() {
        $this->autoRender = false;
        $username     = $_POST['username'] ?? '';
        $old_password = $_POST['passwordlama'] ?? '';
        $new_password = $_POST['passwordbaru'] ?? '';

        // Validasi password baru
        if (strlen($new_password) < 8) return print "Password minimal 8 karakter";
        if (!preg_match('/[A-Z]/', $new_password)) return print "Harus ada huruf besar";
        if (!preg_match('/[a-z]/', $new_password)) return print "Harus ada huruf kecil";
        if (!preg_match('/[0-9]/', $new_password)) return print "Harus ada angka";

        $user_dn = "cn={$username}," . $this->baseDn;
        try {
            $conn = $this->ldapConnect($user_dn, $old_password);
            $mod = ['userPassword' => $new_password];
            if (!ldap_mod_replace($conn, $user_dn, $mod)) {
                throw new Exception("Gagal mengganti password: " . ldap_error($conn));
            }
            echo "sukses";
        } catch (Exception $e) {
            echo $e->getMessage();
        } finally {
            $this->ldapDisconnect($conn);
        }
    }

    public function ldapsearchposix() {
        $this->autoRender = false;
        try {
            $conn = $this->ldapConnect();
            $filter = "(memberUid=erdibgs)";
            $attributes = ["cn"];
            $search = ldap_search($conn, "dc=bagus,dc=local", $filter, $attributes);
            $entries = ldap_get_entries($conn, $search);

            if ($entries["count"] > 0) {
                foreach ($entries as $i => $entry) {
                    if (isset($entry["cn"][0])) {
                        echo "- " . $entry["cn"][0] . "<br>";
                    }
                }
            } else {
                echo "User tidak ditemukan di group manapun.";
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        } finally {
            $this->ldapDisconnect($conn);
        }
    }

    public function addbatch() {
        $this->autoRender = false;
        $this->loadModel('User');

        $sql = "SELECT ID, namaUser, pword FROM dpfdplnew.users u
                WHERE namaUser IS NOT NULL LIMIT 10";
        $result = $this->User->query($sql);

        foreach ($result as $data) {
            $uid = $data['u']['ID'];
            $salt = random_bytes(4);
            $hash = sha1($data['u']['pword'] . $salt, true) . $salt;
            $userPassword = '{SSHA}' . base64_encode($hash);

            echo "dn: uid={$uid}," . $this->baseDn . "<br>";
            echo "objectClass: inetOrgPerson<br>";
            echo "uid: {$uid}<br>";
            echo "sn: -<br>";
            echo "cn: {$data['u']['namaUser']}<br>";
            echo "mail: mail@bernofarm.com<br>";
            echo "userPassword: {$userPassword}<br><br>";
        }
    }

    public function hapus() {
        $this->autoRender = false;
        $uid = $_POST['uid'] ?? null;
        $ou = $_POST['ou'] ?? null;
        if (!$uid) return print "UID kosong";

        try {
            $conn = $this->ldapConnect();
            $dn = "uid=$uid,ou=$ou," . $this->baseDn;
            if (ldap_delete($conn, $dn)) {
                echo "sukses";
            } else {
                echo "gagal: " . ldap_error($conn);
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        } finally {
            $this->ldapDisconnect($conn);
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
            $conn = $this->ldapConnect();
            $dn = "uid=$uid,ou=$ou," . $this->baseDn;
            $salt = random_bytes(4);
            $hash = sha1($pass . $salt, true) . $salt;
            $entry = [
                "cn" => $cn,
                "sn" => $sn,
                "uid" => $uid,
                "mail" => $email,
                "objectClass" => ["inetOrgPerson", "top"],
                "userPassword" => "{SSHA}" . base64_encode($hash),
                "pwdPolicySubentry" => "cn=default,ou=policies,dc=bagus,dc=local"
            ];
            if (ldap_add($conn, $dn, $entry)) {
                echo "sukses";
            } else {
                echo "gagal: " . ldap_error($conn);
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        } finally {
            $this->ldapDisconnect($conn);
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
            $conn = $this->ldapConnect();
            $dn = "uid=$uidLama,ou=$ouLama," . $this->baseDn;
            $salt = random_bytes(4);
            $hash = sha1($pass . $salt, true) . $salt;
            $entry = [
                "cn" => $cn,
                "sn" => $sn,
                "mail" => $email,
                "userPassword" => "{SSHA}" . base64_encode($hash),
                "pwdPolicySubentry" => "cn=default,ou=policies,dc=bagus,dc=local"
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
            $this->ldapDisconnect($conn);
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
            $this->ldapDisconnect($conn);
        }
    }

    public function bind(){
        $this->autoRender = false;
        $ldap_host = "ldap://192.168.0.101"; // ganti IP LDAP server
        $ldap_dn   = "uid=rr,ou=Jakarta,dc=bagus,dc=local";
        $ldap_pass = "rr12345678";
        $ldap_base = "dc=bagus,dc=local";

        // 1. Konek ke server
        $ldapconn = ldap_connect($ldap_host);

        if (!$ldapconn) {
            die("Koneksi ke LDAP gagal");
        }

        // 2. Set protocol version
        ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);

        // 3. Bind pakai user
        if (@ldap_bind($ldapconn, $ldap_dn, $ldap_pass)) {
            echo "Bind sukses<br>";

            // 4. Lakukan pencarian
            $filter = "(objectClass=*)";
            $result = ldap_search($ldapconn, $ldap_base, $filter);

            if ($result) {
                $entries = ldap_get_entries($ldapconn, $result);
                echo "<pre>";
                print_r($entries);
                echo "</pre>";
            } else {
                echo "Search gagal";
            }
        } else {
            echo "Bind gagal (username/password salah atau kena policy)";
        }

        // 5. Tutup koneksi
        ldap_unbind($ldapconn);
    }

}
	
?>
