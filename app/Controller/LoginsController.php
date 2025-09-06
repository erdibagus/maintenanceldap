<?php
class LoginsController extends AppController{
	public $components = array('Function');

    function index() {
        $this->autoRender = false;

        $user = $_POST['username'] ?? '';
        $pass = $_POST['password'] ?? '';

        $ldap_host = "ldap://103.123.63.108:7766"; 
        $ldap_port = null;

        // Default response
        $response = [
            "status"  => "error",
            "message" => "Terjadi kesalahan"
        ];

        $ou = $this->Function->cekOu($user);
        if (!$ou) {
            $response = ["status" => "error", "message" => "User tidak ditemukan"];
            $this->sendJson($response);
            return;
        }

        $expired = $this->cekExpired($user, $ou, 0);
        if ($expired === "expired") {
            $response = ["status" => "error", "message" => "Password Expired"];
            $this->sendJson($response);
            return;
        }

        $bind_dn       = "uid=$user,ou=$ou,dc=bernofarm,dc=com"; 
        $bind_password = "$pass"; 

        $ldap_conn = ldap_connect($ldap_host, $ldap_port) or die("Tidak bisa connect ke LDAP");
        ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);

        if (@ldap_bind($ldap_conn, $bind_dn, $bind_password)) {
            $filter     = "(objectClass=*)";
            $attributes = ["uid","cn","departmentnumber","employeenumber","firstnik","lastnik","birthdate"];

            $result = @ldap_read($ldap_conn, $bind_dn, $filter, $attributes);

            if ($result === false) {
                $err_no  = ldap_errno($ldap_conn);
                $err_msg = ldap_error($ldap_conn);

                if ($err_no == 49 || stripos($err_msg, "Insufficient access") !== false) {
                    $response = [
                        "status"  => "error",
                        "message" => "Akun harus mengganti password terlebih dahulu sebelum bisa digunakan."
                    ];
                } else {
                    $response = [
                        "status"  => "error",
                        "message" => "LDAP Error ($err_no): $err_msg"
                    ];
                }
            } else {
                $entries = ldap_get_entries($ldap_conn, $result);
                
                if ($entries["count"] > 0) {
                    $response = [
                        "status"  => "success",
                        "message" => "Login berhasil",
                        "data"    => $this->cleanLdapEntry($entries[0])
                    ];
                } else {
                    $response = [
                        "status"  => "error",
                        "message" => "Data user tidak ditemukan"
                    ];
                }
            }
        } else {
            $lock = $this->cekLock($user, $ou);
            if ($lock === "locked") {
                $response = [
                    "status"  => "error",
                    "message" => "Akun terkunci"
                ];
            } else {
                $response = [
                    "status"  => "error",
                    "message" => "Bind gagal. Username/password salah."
                ];
            }
        }

        ldap_unbind($ldap_conn);

        $this->sendJson($response);
    }

    // Helper untuk output JSON rapi
    private function sendJson($data) {
        header("Content-Type: application/json");
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit();
    }


    private function cekLock($username, $ou) {
        try {
            $conn = $this->Function->ldapConnect(true);
            $user_dn = "uid=$username,ou=$ou," . $this->Function->ldapConfig['base_dn'];

            $attributes = ['pwdaccountlockedtime'];
            $result = ldap_read($conn, $user_dn, '(objectClass=*)', $attributes);
            $entries = ldap_get_entries($conn, $result);

            if (!empty($entries[0]['pwdaccountlockedtime'][0])) {
                $lockedTimeStr = $entries[0]['pwdaccountlockedtime'][0];
                $datetime = DateTime::createFromFormat('YmdHis\Z', $lockedTimeStr, new DateTimeZone('UTC'));
                $unlockTimestamp = $datetime->getTimestamp() + 300;
                $remaining = $unlockTimestamp - time();

                return [
                    "status" => "locked",
                    "message" => "Password salah 3x, akun terkunci",
                    "remaining_seconds" => $remaining
                ];
            } else {
                return [
                    "status" => "password"
                ];
            }
        } catch (Exception $e) {
            error_log("LDAP cekLock() error [user=$username]: " . $e->getMessage());
            return ["status" => "error", "message" => $e->getMessage()];
        } finally {
            $this->Function->ldapDisconnect($conn);
        }
    }

    private function cekExpired($username, $ou, $mode = 0) {
        $conn = null;
        try {
            $conn = $this->Function->ldapConnect(true);
            $user_dn = "uid=$username,ou=$ou," . $this->Function->ldapConfig['base_dn'];

            $result = ldap_read($conn, $user_dn, '(objectClass=*)', ['+']);
            $entries = ldap_get_entries($conn, $result);

            if (empty($entries[0]['pwdchangedtime'][0])) {
                return ["status" => "error", "message" => "Atribut pwdChangedTime tidak ditemukan"];
            }

            $pwdChangedTime = $entries[0]['pwdchangedtime'][0];
            $pwdMaxAge = 15552000; // masa berlaku password 

            $datetime = DateTime::createFromFormat('YmdHis\Z', $pwdChangedTime, new DateTimeZone('UTC'));
            $changedEpoch = $datetime->getTimestamp();

            $expireEpoch = $changedEpoch + $pwdMaxAge;
            $nowEpoch = time();

            if ($nowEpoch > $expireEpoch) {
                return "expired";
            }

            return "ok";
        } catch (Exception $e) {
            error_log("LDAP cekExpired() error [user=$username]: " . $e->getMessage());
            return ["status" => "error", "message" => $e->getMessage()];
        } finally {
            $this->Function->ldapDisconnect($conn);
        }
    }

    function cleanLdapEntry($entry) {
        $result = [];

        foreach ($entry as $key => $value) {
            // Ambil hanya key string, buang key numerik
            if (is_string($key)) {
                // Kalau array LDAP, ambil elemen pertama saja
                if (isset($value['count']) && $value['count'] == 1) {
                    $result[$key] = $value[0];
                } else {
                    $result[$key] = $value;
                }
            }
        }

        return $result;
    }
}
	
?>
