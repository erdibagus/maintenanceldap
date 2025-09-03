<?php
class GroupsController extends AppController{
	public $components = array('Function');
	
	function index(){

	}

    public function getData($filter = "(objectClass=organizationalUnit)", $attributes = ["ou"]) {
        $this->autoRender = false;
        try {
            // koneksi LDAP pakai helper
            $conn = $this->Function->ldapConnect(true);

            // lakukan pencarian
            $search = ldap_search($conn, $this->Function->ldapConfig['base_dn'], $filter, $attributes);
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
            if (isset($conn)) {
                $this->Function->ldapDisconnect($conn);
            }
        }
    }

    public function hapus() {
        $this->autoRender = false;

        // var_dump($_POST);exit();

        $ou = $_POST['nama'] ?? null;

        if (!$ou) {
            echo "gagal";
            return;
        }

        try {
            $conn = $this->Function->ldapConnect(true);

            // DN OU yang mau dihapus
            $dn = "ou=$ou,".$this->Function->ldapConfig['base_dn'];
            // var_dump($dn);exit();

            if (ldap_delete($conn, $dn)) {
                echo "sukses";
            } else {
                echo "gagal";
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        } finally {
            if (isset($conn)) {
                $this->Function->ldapDisconnect($conn);
            }
        }
    }


    public function tambah() {
        $this->autoRender = false;
        $ou    = $_POST['ouBaru'] ?? '-';

        try {
            $conn = $this->Function->ldapConnect(true);
            $dn = "ou=$ou," . $this->Function->ldapConfig['base_dn'];
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
            if (isset($conn)) {
                $this->Function->ldapDisconnect($conn);
            }
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
            $conn = $this->Function->ldapConnect(true);

            // DN OU lama
            $dn = "ou=$ouLama,". $this->Function->ldapConfig['base_dn'];

            // RDN baru
            $newrdn = "ou=$ouBaru";

            // parent tetap sama
            $newparent = $this->Function->ldapConfig['base_dn'];

            if (ldap_rename($conn, $dn, $newrdn, $newparent, true)) {
                echo "sukses";
            } else {
                echo "gagal";
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        } finally {
            if (isset($conn)) {
                $this->Function->ldapDisconnect($conn);
            }
        }
    }

}
	
?>
