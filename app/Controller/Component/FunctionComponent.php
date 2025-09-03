<?php
App::uses('Component', 'Controller');
class FunctionComponent extends Component{
	public $ldapConfig = [
        'host' => 'ldap://localhost',
        'port' => 389,
        'admin_dn' => 'cn=admin,dc=bernofarm,dc=com',
        'admin_pass' => 'bernofarm68',
        'base_dn' => 'dc=bernofarm,dc=com'
    ];

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

        var_dump("connect");

        return $conn;
    }

    public function ldapDisconnect($conn) {
        if ($conn && is_resource($conn)) {
            ldap_unbind($conn);
        }
    }

	public function cekOu($username) {
        $conn = $this->ldapConnect(true);
        $filter = "(uid=$username)";
        $result = ldap_search($conn, $this->ldapConfig['base_dn'], $filter, ["dn"]);
        $entries = ldap_get_entries($conn, $result);
        ldap_unbind($conn);

        if ($entries["count"] > 0) {
            if (preg_match('/ou=([^,]+)/i', $entries[0]["dn"], $matches)) {
                return $matches[1];
            }
        }
        return null;
    }
}



?>
