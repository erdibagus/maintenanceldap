<?php
class LoginsController extends AppController{
	public $components = array('Function');

    function index() {
        $this->autoRender = false;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendJson([
                "status" => "error",
                "message" => "Hanya POST yang diperbolehkan"
            ], 405);
            exit();
        }

        $username = $_POST['username'] ?? '';
        $pass     = $_POST['password'] ?? '';
        $conn     = null;

        try {
            $user = $this->Function->cekUid($username);
            if (!$user) {
                throw new Exception("User tidak ditemukan");
            }

            $ou = $this->Function->cekOu($user);
            if (!$ou) {
                throw new Exception("User tidak ditemukan");
            }

            // cek expired
            $expiredStatus = $this->cekExpired($user, $ou, 0);
            if ($expiredStatus === "expired") {
                throw new Exception("Password Expired");
            }

            $bind_dn = "uid={$user},ou={$ou}," . $this->Function->ldapConfig['base_dn'];

            $conn = $this->Function->ldapConnect(false); 

            // bind
            if (!@ldap_bind($conn, $bind_dn, $pass)) {
                // Cek apakah terkunci jika gagal login
                $lock = $this->cekLock($user, $ou);
                if ($lock['status'] === "locked") {
                    $response = [
                        "status"  => "error",
                        "message" => "Password salah 5x, akun terkunci",
                        "remaining_lock_seconds" => $lock['remaining']
                    ];
                    $this->sendJson($response);
                    return;
                }
                throw new Exception("Bind gagal. Password salah.");
            }

            $filter = "(objectClass=*)";
            $attributes = ["uid","cn","departmentnumber","employeenumber","firstnik","lastnik","birthdate","employeetype","pwdchangedtime","bernoMail"];
            
            $result = @ldap_read($conn, $bind_dn, $filter, $attributes);

            if ($result === false) {
                $errno = ldap_errno($conn);
                $error = ldap_error($conn);
                if ($errno == 49 || stripos($error, "Insufficient access") !== false) {
                    throw new Exception("Akun harus mengganti password terlebih dahulu sebelum bisa digunakan.");
                }
                throw new Exception("LDAP Read Error ($errno): $error");
            }

            $entries = ldap_get_entries($conn, $result);
            
            if ($entries["count"] > 0) {
                $status = $entries[0]['employeetype'][0] ?? '';
                
                if ($status === "aktif") {
                    $pwdChangedTime = $entries[0]['pwdchangedtime'][0] ?? null;
                    $expiredInfo = $this->hitungExpired($pwdChangedTime);
                    
                    $response = [
                        "status"  => "success",
                        "message" => "Login berhasil",
                        "data"    => $this->cleanLdapEntry($entries[0]),
                        "remaining_expired_second" => $expiredInfo["sisa_detik"]
                    ];
                } else {
                    throw new Exception("User tidak aktif");
                }
            } else {
                throw new Exception("Data user tidak ditemukan");
            }

        } catch (Exception $e) {
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
                    "remaining" => $remaining
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

    function formatTglLahir($ldapDate) {
        $datetime = DateTime::createFromFormat('YmdHis\Z', $ldapDate, new DateTimeZone('UTC'));
        if (!$datetime) {
            return null;
        }
        return $datetime->format('d-m-Y');
    }

    function cleanLdapEntry($entry) {
        $result = [];

        // Mapping field lama ke baru
        $map = [
            "uid"              => "id",
            "cn"               => "nama",
            "employeenumber"   => "nik",
            "departmentnumber" => "divisi",
            "firstnik"         => "nik_awal",
            "lastnik"          => "nik_akhir",
            "birthdate"        => "tgl_lahir"
        ];

        foreach ($map as $oldKey => $newKey) {
            if (isset($entry[$oldKey])) {
                $value = is_array($entry[$oldKey]) && isset($entry[$oldKey][0]) 
                    ? $entry[$oldKey][0] 
                    : $entry[$oldKey];

                // Khusus tgl_lahir 
                if ($oldKey === "birthdate") {
                    $value = $this->formatTglLahir($value);
                }

                $result[$newKey] = $value;
            }
        }

        if (isset($entry["bernomail"])) {
            $emails = array_filter(
                $entry["bernomail"],
                fn($v) => $v !== "count" && !is_int($v)
            );

            $result["bernoMail"] = array_values($emails); 
        }

        return $result;
    }


    private function hitungExpired($pwdChangedTime, $pwdMaxAge = 15552000) {
        // Konversi pwdChangedTime ke DateTime
        $datetime = DateTime::createFromFormat('YmdHis\Z', $pwdChangedTime, new DateTimeZone('UTC'));
        if (!$datetime) {
            return ["status" => "error", "message" => "Format pwdChangedTime tidak valid"];
        }

        $changedEpoch = $datetime->getTimestamp();
        $expireEpoch  = $changedEpoch + $pwdMaxAge;
        $nowEpoch     = time();

        $sisaDetik = $expireEpoch - $nowEpoch;

        if ($sisaDetik <= 0) {
            return [
                "status"      => "expired",
                "sisa_detik"  => $sisaDetik 
            ];
        }

        return [
            "status"      => "ok",
            "sisa_detik"  => $sisaDetik
        ];
    }

}
	
?>
