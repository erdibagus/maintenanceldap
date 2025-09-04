<?php
// header('Content-Type: application/json');
class UbahpasswordsController extends AppController{
	public $components = array('Function');

    function indexBindAdmin(){
        $this->autoRender = false;

        $ldap_host = "192.168.0.101";
        $ldap_port = 389;

        $username      = $_POST['username'] ?? '';
        $new_password  = $_POST['passwordBaru'] ?? '';
        $response      = [];

        try {
            // 🔒 Validasi password baru
            if (strlen($new_password) < 8) {
                throw new Exception("Password minimal 8 karakter");
            }
            if (!preg_match('/[A-Z]/', $new_password)) {
                throw new Exception("Password baru harus mengandung huruf besar");
            }
            if (!preg_match('/[a-z]/', $new_password)) {
                throw new Exception("Password baru harus mengandung huruf kecil");
            }
            if (!preg_match('/[0-9]/', $new_password)) {
                throw new Exception("Password baru harus mengandung angka");
            }
            if (!preg_match('/[\W_]/', $new_password)) { 
                throw new Exception("Password baru harus mengandung simbol (@#$%^&*?!_- dll)");
            }

            // ✅ Cari OU user secara dinamis
            $ou = $this->Function->cekOu($username);
            if (!$ou) {
                throw new Exception("User tidak ditemukan di LDAP.");
            }
            $user_dn = "uid={$username},ou={$ou},dc=bagus,dc=local";

            // 🔗 Koneksi ke LDAP
            $ldapconn = ldap_connect($ldap_host, $ldap_port);
            if (!$ldapconn) {
                throw new Exception("Tidak bisa terhubung ke server LDAP.");
            }

            ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);

            // 🔑 Bind sebagai admin
            $admin_dn  = "cn=admin,dc=bagus,dc=local";
            $admin_pwd = "bagus"; // ganti sesuai password admin
            if (!@ldap_bind($ldapconn, $admin_dn, $admin_pwd)) {
                throw new Exception("Bind admin gagal, periksa DN/password admin.");
            }

            // 🔄 Ganti password user
            $mod = ['userPassword' => $new_password];
            if (!ldap_mod_replace($ldapconn, $user_dn, $mod)) {
                throw new Exception("Gagal mengganti password user.");
            }

            $response = [
                "status"  => "success",
                "message" => "Password user berhasil diganti oleh admin"
            ];

        } catch (Exception $e) {
            $response = [
                "status"  => "error",
                "message" => $e->getMessage()
            ];
        } finally {
            if (isset($ldapconn) && $ldapconn) {
                ldap_unbind($ldapconn);
            }
        }

        header('Content-Type: application/json');
        echo json_encode($response);
    }
	
public function index() {
    $this->autoRender = false;

    $username     = $_POST['username'] ?? '';
    $old_password = $_POST['passwordLama'] ?? '';
    $new_password = $_POST['passwordBaru'] ?? '';
    $response     = [];
    $conn         = null;

    try {
        // 🔒 Validasi password baru
        if (strlen($new_password) < 8) {
            throw new Exception("Password minimal 8 karakter");
        }
        if (!preg_match('/[A-Z]/', $new_password)) {
            throw new Exception("Password baru harus mengandung huruf besar");
        }
        if (!preg_match('/[a-z]/', $new_password)) {
            throw new Exception("Password baru harus mengandung huruf kecil");
        }
        if (!preg_match('/[0-9]/', $new_password)) {
            throw new Exception("Password baru harus mengandung angka");
        }
        if (!preg_match('/[\W_]/', $new_password)) {
            throw new Exception("Password baru harus mengandung simbol (@#$%^&*?!_- dll)");
        }

        // ✅ Cari OU user
        $ou = $this->Function->cekOu($username);
        if (!$ou) {
            throw new Exception("User tidak ditemukan di LDAP.");
        }
        $user_dn = "uid={$username},ou={$ou}," . $this->Function->ldapConfig['base_dn'];

        // 🔗 Koneksi ke LDAP
        $conn = $this->Function->ldapConnect(false);

        // 🔑 Bind pakai password lama
        if (!@ldap_bind($conn, $user_dn, $old_password)) {
            throw new Exception("Password lama salah.");
        }

        // Tentukan cara simpan password
        $hashMode = $this->Function->ldapConfig['hash'] ?? 'plain';
        $passwordValue = null;

        switch (strtolower($hashMode)) {
            case 'ssha':
                $salt = random_bytes(4);
                $passwordValue = "{SSHA}" . base64_encode(sha1($new_password . $salt, true) . $salt);
                break;
            case 'sha':
                $passwordValue = "{SHA}" . base64_encode(sha1($new_password, true));
                break;
            default: // plain
                $passwordValue = $new_password;
        }

        // Ganti password
        $mod = ['userPassword' => $passwordValue];

        if (!@ldap_mod_replace($conn, $user_dn, $mod)) {
            throw new Exception("Gagal mengganti password.");
        }

        $response = [
            "status"  => "success",
            "message" => "Password berhasil diganti"
        ];

    } catch (Exception $e) {
        error_log("LDAP ubah password error [user=$username]: " . $e->getMessage());
        $response = [
            "status"  => "error",
            "message" => $e->getMessage()
        ];
    } finally {
        if ($conn) {
            $this->Function->ldapDisconnect($conn);
        }
    }

    echo json_encode($response);
}


    function ldapsearchposix(){
        $this->autoRender = false;
        $ldap_host = "ldap://192.168.0.101"; // ganti sesuai server LDAP Anda
        $ldap_dn   = "dc=bagus,dc=local"; // base DN

        // Koneksi ke server LDAP
        $ldap_conn = ldap_connect($ldap_host);
        if (!$ldap_conn) {
            die("Tidak bisa konek ke server LDAP");
        }

        // Set opsi LDAP
        ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);

        // Bind ke LDAP (gunakan akun admin LDAP)
        $ldap_bind = ldap_bind($ldap_conn, "cn=admin,dc=bagus,dc=local", "bagus");
        if (!$ldap_bind) {
            die("Gagal bind ke LDAP");
        }

        // Filter untuk mencari group yang punya memberUid = erdibgs
        $filter = "(memberUid=erdibgs)";
        $attributes = ["cn"]; // hanya ambil atribut cn

        // Jalankan pencarian
        $search = ldap_search($ldap_conn, $ldap_dn, $filter, $attributes);
        if ($search) {
            $entries = ldap_get_entries($ldap_conn, $search);

            if ($entries["count"] > 0) {
                echo "User 'erdibgs' ada di group berikut:\n";
                for ($i = 0; $i < $entries["count"]; $i++) {
                    echo "- " . $entries[$i]["cn"][0] . "\n";
                }
            } else {
                echo "User 'erdibgs' tidak ditemukan di group manapun.\n";
            }
        } else {
            echo "Query LDAP gagal.\n";
        }

        // Tutup koneksi
        ldap_unbind($ldap_conn);
    }

    function addbatch(){
        $this->autoRender = false;
        $this->loadModel('User');

        // Base DN LDAP
        $baseDN = "dc=bernofarm,dc=com";

        // Query data user
        $sql = "SELECT 
                    m.*,
                    IF(m.`nikawal` LIKE '%PA%' OR m.`nikawal` LIKE '%PB%' 
                    OR m.`nama` = 'AGNESYA ELYSABETH KODISU' OR m.`nama` = 'HARRIS JEFFERSON SUSANTO', 'sda', 'jkt') AS `ou`
                FROM ldap.`mi` m";
        $result = $this->User->query($sql);

        foreach ($result as $data) {
            $id       = $this->defaultDash($data['m']['id']);
            $nama     = $this->defaultDash($data['m']['nama']);
            $nik      = $this->defaultDash($data['m']['nik']);
            $divisi   = $this->defaultDash($data['m']['divisi']);
            $ket      = $this->defaultDash($data['m']['ket']);
            $status   = $this->defaultDash($data['m']['status']);
            $tgllahir = $this->defaultDash($data['m']['tgllahir']);
            $noktp    = $this->defaultDash($data['m']['noktp']);
            $nikawal  = $this->defaultDash($data['m']['nikawal']);
            $nikakhir = $this->defaultDash($data['m']['nikakhir']);
            $ou       = $this->defaultDash($data[0]['ou']);

            echo "dn: uid={$id},ou={$ou},{$baseDN}<br>";
            echo "objectClass: inetOrgPerson<br>";
            echo "objectClass: bernofarmPerson<br>";
            echo "uid: {$id}<br>";
            echo "sn: -<br>";
            echo "cn: {$nama}<br>";
            echo "userPassword: " . ($tgllahir !== '-' ? $this->ubahTanggal($tgllahir) : '-') . "<br>";
            echo "description: {$ket}<br>";
            echo "employeeNumber: {$nik}<br>";
            echo "departmentNumber: {$divisi}<br>";
            echo "employeeType: {$status}<br>";
            echo "noKTP: {$noktp}<br>";
            echo "firstNik: {$nikawal}<br>";
            echo "lastNik: {$nikakhir}<br>";
            echo "birthDate: " . ($tgllahir !== '-' ? $this->ubahTanggalLDAP($tgllahir) : '-') . "<br><br>";
        }
    }

    function defaultDash($value) {
        return (isset($value) && trim($value) !== '') ? $value : '-';
    }

    function ubahTanggal($tanggal) {
        $parts = explode("-", $tanggal);
        if (count($parts) !== 3) return null;
        return $parts[2] . $parts[1] . $parts[0];
    }

    function ubahTanggalLDAP($tanggal) {
        $parts = explode("-", $tanggal);
        if (count($parts) !== 3) return null;
        return $parts[0] . $parts[1] . $parts[2] . "000000Z";
    }

}
	
?>


