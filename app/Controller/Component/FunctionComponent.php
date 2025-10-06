<?php
App::uses('Component', 'Controller');
class FunctionComponent extends Component{
    public $ldapConfig;

    public function __construct() {
        $host = $_SERVER['HTTP_HOST'];

        if ($host === 'localhost' || $host === '127.0.0.1' || $host === '192.168.0.101' ) {
            //debug
            $this->ldapConfig = [
                'host' => 'ldap://103.123.63.108:7766',
                'port' => null,
                'admin_dn' => 'cn=admin,dc=bernofarm,dc=com',
                'admin_pass' => 'You4tourlah',
                'base_dn' => 'dc=bernofarm,dc=com',
                'hash' => 'plain'
            ];
        } else {
            //prod
            $this->ldapConfig = [
                'host' => 'ldap://localhost:7766',
                'port' => null,
                'admin_dn' => 'cn=admin,dc=bernofarm,dc=com',
                'admin_pass' => 'You4tourlah',
                'base_dn' => 'dc=bernofarm,dc=com',
                'hash' => 'plain'
            ];
        }
    }

    public function ldapConnect($bindAsAdmin = true) {
        $conn = ldap_connect($this->ldapConfig['host'], $this->ldapConfig['port']);
        if (!$conn) {
            throw new Exception("Tidak bisa konek ke LDAP");
        }

        ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($conn, LDAP_OPT_REFERRALS, 0);

        if ($bindAsAdmin) {
            if (!@ldap_bind($conn, $this->ldapConfig['admin_dn'], $this->ldapConfig['admin_pass'])) {
                throw new Exception("Gagal bind sebagai admin LDAP");
            }
        }

        return $conn;
    }

    public function ldapDisconnect($conn) {
        if ($conn && is_resource($conn)) {
            ldap_unbind($conn);
        }
    }

	public function cekOu($username) {
        $conn = null;
        try {
            $conn = $this->ldapConnect(true);
            $filter = "(uid=$username)";
            $result = ldap_search($conn, $this->ldapConfig['base_dn'], $filter, ["dn"]);

            if (!$result) {
                throw new Exception("LDAP search gagal untuk user: $username");
            }

            $entries = ldap_get_entries($conn, $result);

            if ($entries["count"] > 0) {
                if (preg_match('/ou=([^,]+)/i', $entries[0]["dn"], $matches)) {
                    return $matches[1];
                }
            }

            return null;
        } catch (Exception $e) {
            error_log("LDAP cekOu() error [user=$username]: " . $e->getMessage());
            return null;
        } finally {
            if ($conn) {
                $this->ldapDisconnect($conn);
            }
        }
    }

    public function cekUid($username) {
        $conn = null;
        try {
            $conn = $this->ldapConnect(true);
            $filter = "(mail=$username*@*)"; //ganti mail=$username*@*
            $result = ldap_search($conn, $this->ldapConfig['base_dn'], $filter, ["uid"]);

            if (!$result) {
                throw new Exception("LDAP search gagal untuk user: $username");
            }

            $entries = ldap_get_entries($conn, $result);

            if ($entries["count"] > 0) {
                return $entries[0]['uid'][0];
            }

            return null;
        } catch (Exception $e) {
            error_log("LDAP cekOu() error [user=$username]: " . $e->getMessage());
            return null;
        } finally {
            if ($conn) {
                $this->ldapDisconnect($conn);
            }
        }
    }

}



?>
