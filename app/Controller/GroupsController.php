<?php
class GroupsController extends AppController{
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

    public function getData($filter = "(objectClass=organizationalUnit)", $attributes = ["ou"]) {
        $this->autoRender = false;
        try {
            // koneksi LDAP pakai helper
            $conn = $this->ldapConnect();

            // lakukan pencarian
            $search = ldap_search($conn, $this->baseDn, $filter, $attributes);
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

    public function hapus() {
        $this->autoRender = false;

        $ou = $_POST['nama'] ?? null;

        if (!$ou) {
            echo "gagal";
            return;
        }

        try {
            $conn = $this->ldapConnect();

            // DN OU yang mau dihapus
            $dn = "ou=$ou,".$this->baseDn;

            if (ldap_delete($conn, $dn)) {
                echo "sukses";
            } else {
                echo "gagal";
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        } finally {
            if (isset($conn)) {
                $this->ldapDisconnect($conn);
            }
        }
    }


    public function tambah() {
        $this->autoRender = false;
        $ou    = $_POST['ouBaru'] ?? '-';

        try {
            $conn = $this->ldapConnect();
            $dn = "ou=$ou," . $this->baseDn;
            $entry = [
                "ou" => $ou,
                "objectClass" => ["organizationalUnit", "top"],
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

        $ouLama = $_POST['ouLama'] ?? null;

        $ouBaru = $_POST['ouBaru'] ?? null;

        if (!$ouLama || !$ouBaru) {
            echo "gagal";
            return;
        }

        try {
            $conn = $this->ldapConnect();

            // DN OU lama
            $dn = "ou=$ouLama,dc=bagus,dc=local";

            // RDN baru
            $newrdn = "ou=$ouBaru";

            // parent tetap sama
            $newparent = "dc=bagus,dc=local";

            if (ldap_rename($conn, $dn, $newrdn, $newparent, true)) {
                echo "sukses";
            } else {
                echo "gagal";
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        } finally {
            $this->ldapDisconnect($conn);
        }
    }

}
	
?>

<!-- bind sebagai admin = 
1. berarti user bebas memasukkan password tanpa ppolicy.
2. setelah terbuat wajib ubah password lagi dengan metode bind sebagai user karena sebelumnya bind sebagai admin.

jika bind sebagai user =
1. user tidak bisa ubah password karena password lama kadaluarsa, aturannya wajib input password lama.

solusi 1 =
1. ketika password kadaluarsa, kemudian klik reset.
2. sistem melakukan bind admin kemuadian change password user tersebut ke default, misalnya "user123".
3. muncul form ubah password dengan menginputkan username,password lama yaitu "user123", dan password baru.

kelemahannya =
1. siapapun bisa mereset password user lain

solusi 2 = 
1. ketika password kadaluarsa, kemudian klik reset.
2. sistem mengirim link ubah password ke email user tersebut untuk memastikan bahwa user tersebut yg meminta ubah password. -->
