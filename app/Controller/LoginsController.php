<?php
class LoginsController extends AppController{
	public $components = array('Function');

    function index() {
        $this->autoRender = false;

        $user = $_POST['username'] ?? '';
        $pass = $_POST['password'] ?? '';
        $response = [];

        try {
            $ou = $this->Function->cekOu($user);
            if (!$ou) {
                $response = ["status" => "error", "message" => "User tidak ditemukan"];
                echo json_encode($response);
                return;
            }

            $ldapUser = "uid=$user,ou=$ou," . $this->Function->ldapConfig['base_dn'];
            $conn = $this->Function->ldapConnect(false);

            // cek expired sebelum bind
            $expired = $this->cekExpired($user, $ou, 0);
            if ($expired["status"] === "expired") {
                echo json_encode($expired);
                return;
            }

            // coba bind user
            if (@ldap_bind($conn, $ldapUser, $pass)) {
                $response = $this->cekExpired($user, $ou, 1);
            } else {
                $response = $this->cekLock($user, $ou);
            }

            ldap_unbind($conn);

        } catch (Exception $e) {
            $response = ["status" => "error", "message" => $e->getMessage()];
        }

        echo json_encode($response);
    }

    private function cekLock($username, $ou) {
        $conn = $this->Function->ldapConnect(true);
        $user_dn = "uid=$username,ou=$ou," . $this->Function->ldapConfig['base_dn'];

        $attributes = ['pwdaccountlockedtime'];
        $result = ldap_read($conn, $user_dn, '(objectClass=*)', $attributes);
        $entries = ldap_get_entries($conn, $result);
        ldap_unbind($conn);

        if (!empty($entries[0]['pwdaccountlockedtime'][0])) {
            $lockedTimeStr = $entries[0]['pwdaccountlockedtime'][0];
            $datetime = DateTime::createFromFormat('YmdHis\Z', $lockedTimeStr, new DateTimeZone('UTC'));
            $unlockTimestamp = $datetime->getTimestamp() + 60;
            $remaining = $unlockTimestamp - time();

            return [
                "status" => "locked",
                "message" => "Password salah 3x, akun terkunci",
                "remaining_seconds" => $remaining
            ];
        } else {
            return [
                "status" => "invalid",
                "message" => "Password salah"
            ];
        }
    }

    private function cekExpired($username, $ou, $mode = 0) {
        $conn = $this->Function->ldapConnect(true);
        $user_dn = "uid=$username,ou=$ou," . $this->Function->ldapConfig['base_dn'];

        $result = ldap_read($conn, $user_dn, '(objectClass=*)', ['+']);
        $entries = ldap_get_entries($conn, $result);
        ldap_unbind($conn);

        if (empty($entries[0]['pwdchangedtime'][0])) {
            return ["status" => "error", "message" => "Atribut pwdChangedTime tidak ditemukan"];
        }

        $pwdChangedTime = $entries[0]['pwdchangedtime'][0];
        $pwdMaxAge = 300; // masa berlaku password

        $datetime = DateTime::createFromFormat('YmdHis\Z', $pwdChangedTime, new DateTimeZone('UTC'));
        $changedEpoch = $datetime->getTimestamp();

        $expireEpoch = $changedEpoch + $pwdMaxAge;
        $nowEpoch = time();

        if ($nowEpoch > $expireEpoch) {
            return ["status" => "expired", "message" => "Password sudah kadaluarsa"];
        } elseif ($mode !== 0) {
            $remaining = $expireEpoch - $nowEpoch;
            return [
                "status" => "success",
                "message" => "Login berhasil",
                "remaining_seconds" => $remaining
            ];
        }

        return ["status" => "ok"];
    }

    function test(){
        $this->autoRender = false;
        $ldap_host = "ldap://192.168.0.101"; 
        $ldap_port = 389;

        $bind_dn = "uid=bagus123,ou=Jakarta,dc=bagus,dc=local"; 
        $bind_password = "Gagaso123!3"; 

        $ldap_conn = ldap_connect($ldap_host, $ldap_port) or die("Tidak bisa connect ke LDAP");

        ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);

        if (@ldap_bind($ldap_conn, $bind_dn, $bind_password)) {
            $filter = "(objectClass=*)";
            $attributes = ["mail"];

            $result = @ldap_read($ldap_conn, $bind_dn, $filter, $attributes);

            if ($result === false) {
                // Ambil error detail
                $err_no  = ldap_errno($ldap_conn);
                $err_msg = ldap_error($ldap_conn);

                // Jika masalah karena ppolicy (misalnya password expired / harus ganti password)
                if ($err_no == 49 || stripos($err_msg, "Insufficient access") !== false) {
                    $output = [
                        "error" => true,
                        "message" => "Akun harus mengganti password terlebih dahulu sebelum bisa digunakan."
                    ];
                } else {
                    $output = [
                        "error" => true,
                        "message" => "LDAP Error ($err_no): $err_msg"
                    ];
                }
            } else {
                $entries = ldap_get_entries($ldap_conn, $result);
                $output = [];
                if ($entries["count"] > 0 && isset($entries[0]["mail"])) {
                    $output["dn"] = $entries[0]["dn"];
                    $output["mail"] = $entries[0]["mail"][0];
                }
            }
        } else {
            $output = [
                "error" => true,
                "message" => "Bind gagal. Username/password salah atau akun terkunci."
            ];
        }

        ldap_unbind($ldap_conn);

        // Tampilkan JSON
        header("Content-Type: application/json");
        echo json_encode($output, JSON_PRETTY_PRINT);

    }
}
	
?>
