<?php
class OusController extends AppController{
	public $components = array('Function');
    
	function index(){
        $this->autoRender = false;
        $ldapHost = "ldap://103.123.63.108:7766";
        $ldapPort = null;
        $bindDn   = "cn=admin,dc=bernofarm,dc=com";
        $bindPass = "You4tourlah";
        $baseDn   = "dc=bernofarm,dc=com";
		$result = $this->sinkronOuDariDn($ldapHost, $ldapPort, $bindDn, $bindPass, $baseDn);
        print_r($result);
	}

    function sinkronOuDariDn($ldapHost, $ldapPort, $bindDn, $bindPass, $baseDn) {
        // Koneksi ke LDAP
        $ldapconn = ldap_connect($ldapHost, $ldapPort);
        if (!$ldapconn) {
            return ["status" => "error", "message" => "Tidak bisa konek ke LDAP server"];
        }

        ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);

        // Bind admin
        if (!ldap_bind($ldapconn, $bindDn, $bindPass)) {
            return ["status" => "error", "message" => "Bind gagal, cek user/pass"];
        }

        // Cari semua user inetOrgPerson
        $filter = "(objectClass=inetOrgPerson)";
        $search = ldap_search($ldapconn, $baseDn, $filter, ["dn"]);
        $entries = ldap_get_entries($ldapconn, $search);

        $updated = [];
        $failed = [];

                // index idmkts
        $mapIdMkt = [];
        foreach ($sqlMasterIdMkts as $idMkt) {
            $mapIdMkt[$idMkt[0]['idkry']] = $idMkt;
        }

        for ($i = 0; $i < $entries["count"]; $i++) {
            $dn = $entries[$i]["dn"];

            // Ambil ou dari DN (regex cari 'ou=xxx')
            if (preg_match('/ou=([^,]+)/i', $dn, $matches)) {
                $ouValue = $matches[1];

                // Tambahkan attribute ou ke user
                $entry = ["ou" => $ouValue];

                // Coba add dulu
                if (@ldap_mod_add($ldapconn, $dn, $entry)) {
                    $updated[] = "$dn (add: $ouValue)";
                } 
                // Kalau gagal, coba replace
                elseif (@ldap_modify($ldapconn, $dn, $entry)) {
                    $updated[] = "$dn (replace: $ouValue)";
                } 
                else {
                    $failed[] = "$dn (gagal update ou)";
                }
            }
        }

        ldap_unbind($ldapconn);

        return [
            "status" => "done",
            "updated" => $updated,
            "failed"  => $failed
        ];
    }

    public function searchUser() {
        $uid = $_POST['uid'] ?? '';

        $conn = null;
        $response = [];

        try {
            if (empty($uid)) {
                throw new Exception("UID wajib diisi.");
            }

            // admin bind
            $conn = $this->ldapConnect(true);

            $base_dn = "ou=jkt,dc=bernofarm,dc=com";
            $filter  = "(uid={$uid})";
            $attributes = ["uid", "cn", "employeeType"];

            // search
            $search = ldap_search($conn, $base_dn, $filter, $attributes);
            if (!$search) {
                throw new Exception("LDAP search gagal dijalankan.");
            }

            $entries = ldap_get_entries($conn, $search);

            if ($entries["count"] > 0) {
                $user = $entries[0];

                $employeeType = $user['employeetype'][0] ?? '';

                if ($employeeType === "aktif") {
                    $response = [
                        "status"       => "success",
                        "data"         => [
                            "uid"          => $user['uid'][0] ?? '',
                            "cn"           => $user['cn'][0] ?? ''
                        ]
                    ];
                } else {
                    throw new Exception("User dengan UID {$uid} tidak aktif.");
                }
            } else {
                throw new Exception("User dengan UID {$uid} tidak ditemukan.");
            }

        } catch (Exception $e) {
            error_log("LDAP search error [uid=$uid]: " . $e->getMessage());
            $response = [
                "status"  => "error",
                "message" => $e->getMessage()
            ];
        } finally {
            if ($conn) {
                $this->ldapDisconnect($conn);
            }
        }
        
        $this->sendJson($response);
    }


public function sinkronDataEmail() {
    $ldapHost = "ldap://103.123.63.108:7766";
    $ldapPort = null;
    $bindDn   = "cn=admin,dc=bernofarm,dc=com";
    $bindPass = "You4tourlah";
    $baseDn   = "dc=bernofarm,dc=com";

    $this->autoRender = false;
    $this->loadModel('User');

    // Ambil semua data email dari tabel emailkry
    $sqlMasterIdMkts = $this->User->query("SELECT * FROM ldap.emailkry e");

    $mapIdMkt = [];
    foreach ($sqlMasterIdMkts as $idMkt) {
        $mapIdMkt[$idMkt['e']['id_karyawan']] = $idMkt['e']['email'];
    }

    // Koneksi ke LDAP
    $ldapconn = ldap_connect($ldapHost, $ldapPort);
    if (!$ldapconn) {
        return ["status" => "error", "message" => "Tidak bisa konek ke LDAP server"];
    }

    ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);

    // Bind admin
    if (!ldap_bind($ldapconn, $bindDn, $bindPass)) {
        return ["status" => "error", "message" => "Bind gagal, cek user/pass"];
    }

    // Cari semua user inetOrgPerson
    $filter = "(objectClass=inetOrgPerson)";
    $search = ldap_search($ldapconn, $baseDn, $filter, ["uid","cn","mail","dn"]);
    $entries = ldap_get_entries($ldapconn, $search);

    $added = [];
    $updated = [];
    $failed = [];

    for ($i = 0; $i < $entries["count"]; $i++) {
        $uid = isset($entries[$i]["uid"][0]) ? $entries[$i]["uid"][0] : null;
        $dn  = $entries[$i]["dn"];

        if ($uid && isset($mapIdMkt[$uid])) {
            $emailBaru = $mapIdMkt[$uid];
            $emailLama = isset($entries[$i]["mail"][0]) ? $entries[$i]["mail"][0] : null;

            // Jika belum ada mail → tambah baru
            if ($emailLama === null) {
                $entry = ["mail" => $emailBaru];
                if (ldap_mod_add($ldapconn, $dn, $entry)) {
                    $added[] = [
                        "uid" => $uid,
                        "email_baru" => $emailBaru
                    ];
                } else {
                    $failed[] = [
                        "uid" => $uid,
                        "aksi" => "add",
                        "error" => ldap_error($ldapconn)
                    ];
                }
            }
            // Jika email berbeda → update
            elseif ($emailLama !== $emailBaru) {
                $entry = ["mail" => $emailBaru];
                if (ldap_modify($ldapconn, $dn, $entry)) {
                    $updated[] = [
                        "uid" => $uid,
                        "email_lama" => $emailLama,
                        "email_baru" => $emailBaru
                    ];
                } else {
                    $failed[] = [
                        "uid" => $uid,
                        "aksi" => "modify",
                        "error" => ldap_error($ldapconn)
                    ];
                }
            }
        }
    }

    ldap_unbind($ldapconn);

    return [
        "status" => "success",
        "added" => $added,
        "updated" => $updated,
        "failed" => $failed,
        "total_added" => count($added),
        "total_updated" => count($updated),
        "total_failed" => count($failed)
    ];
}



}
	
?>
