<?php
class UbahpasswordsController extends AppController{
	public $components = array('Function');

    // function indexBindAdmin(){
    //     $this->autoRender = false;

    //     $ldap_host = "192.168.0.101";
    //     $ldap_port = 389;

    //     $username      = $_POST['username'] ?? '';
    //     $new_password  = $_POST['passwordBaru'] ?? '';
    //     $response      = [];

    //     try {
    //         // 🔒 Validasi password baru
    //         if (strlen($new_password) < 8) {
    //             throw new Exception("Password minimal 8 karakter");
    //         }
    //         if (!preg_match('/[A-Z]/', $new_password)) {
    //             throw new Exception("Password baru harus mengandung huruf besar");
    //         }
    //         if (!preg_match('/[a-z]/', $new_password)) {
    //             throw new Exception("Password baru harus mengandung huruf kecil");
    //         }
    //         if (!preg_match('/[0-9]/', $new_password)) {
    //             throw new Exception("Password baru harus mengandung angka");
    //         }
    //         if (!preg_match('/[\W_]/', $new_password)) { 
    //             throw new Exception("Password baru harus mengandung simbol (@#$%^&*?!_- dll)");
    //         }

    //         // ✅ Cari OU user secara dinamis
    //         $ou = $this->Function->cekOu($username);
    //         if (!$ou) {
    //             throw new Exception("User tidak ditemukan di LDAP.");
    //         }
    //         $user_dn = "uid={$username},ou={$ou},dc=bagus,dc=local";

    //         // 🔗 Koneksi ke LDAP
    //         $ldapconn = ldap_connect($ldap_host, $ldap_port);
    //         if (!$ldapconn) {
    //             throw new Exception("Tidak bisa terhubung ke server LDAP.");
    //         }

    //         ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
    //         ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);

    //         // 🔑 Bind sebagai admin
    //         $admin_dn  = "cn=admin,dc=bagus,dc=local";
    //         $admin_pwd = "bagus"; // ganti sesuai password admin
    //         if (!@ldap_bind($ldapconn, $admin_dn, $admin_pwd)) {
    //             throw new Exception("Bind admin gagal, periksa DN/password admin.");
    //         }

    //         // 🔄 Ganti password user
    //         $mod = ['userPassword' => $new_password];
    //         if (!ldap_mod_replace($ldapconn, $user_dn, $mod)) {
    //             throw new Exception("Gagal mengganti password user.");
    //         }

    //         $response = [
    //             "status"  => "success",
    //             "message" => "Password user berhasil diganti oleh admin"
    //         ];

    //     } catch (Exception $e) {
    //         $response = [
    //             "status"  => "error",
    //             "message" => $e->getMessage()
    //         ];
    //     } finally {
    //         if (isset($ldapconn) && $ldapconn) {
    //             ldap_unbind($ldapconn);
    //         }
    //     }

    //     header('Content-Type: application/json');
    //     echo json_encode($response);
    // }
	
    function index() {
        $this->autoRender = false;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405); 
            header('Content-Type: application/json');
            echo json_encode([
                "status" => "error",
                "message" => "Hanya POST yang diperbolehkan"
            ]);
            exit();
        }

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
                throw new Exception("User tidak ditemukan.");
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
            $pwd = ['userPassword' => $passwordValue];

            if (!@ldap_mod_replace($conn, $user_dn, $pwd)) {
                $errno = ldap_errno($conn);
                $error = ldap_error($conn);

                // Tangani error khusus
                if ($errno == 19) {
                    throw new Exception("Password baru tidak boleh sama dengan 5 password terakhir Anda.");
                } else {
                    throw new Exception("Gagal mengganti password (LDAP Error $errno: $error)");
                }
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

        $this->sendJson($response);
    }

    private function sendJson($data) {
        header("Content-Type: application/json");
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit();
    }
}
	
?>