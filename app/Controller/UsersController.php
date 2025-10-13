<?php
class UsersController extends AppController{
	public $components = array('Function');
    
    private $ldapHost = "ldap://192.168.0.101";
    private $ldapPort = 389;
    private $ldapAdminDn = "cn=admin,dc=bagus,dc=local";
    private $ldapAdminPass = "bagus";
    private $baseDn = "dc=bagus,dc=local";
	
	function index(){
		// $this->Function->cekSession($this);
	}

    public function getData($filter = "(objectClass=*)", $attributes = []) {
        $this->autoRender = false;

        $ou = $_POST['ou'];
        $nama = $_POST['nama'];
        $akses = $_POST['akses'];

        $filterOu = "";

        if($ou != "") $filterOu = "ou=$ou,";
        
        if ($akses != "" && $nama != "") {
            // Filter jika ada OU dan CN
            $filter = "(&" .
                        "(objectClass=*)" .
                        "(ou=$akses)" .
                        "(cn=*$nama*)" .
                      ")";
        } elseif ($akses != "") {
            // Hanya filter berdasarkan OU
            $filter = "(&" .
                        "(objectClass=*)" .
                        "(ou=$akses)" .
                      ")";
        } elseif ($nama != "") {
            // Hanya filter berdasarkan CN
            $filter = "(&" .
                        "(objectClass=*)" .
                        "(cn=*$nama*)" .
                      ")";
        }

        try {
            $conn = $this->Function->ldapConnect(true);

            $search = ldap_search($conn, $filterOu.$this->Function->ldapConfig['base_dn'], $filter, $attributes);
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
            $this->Function->ldapDisconnect($conn);
        }
    }

    public function hapus() {
        $this->autoRender = false;
        $dn = $_POST['dn'] ?? null;
        if (!$dn) return print "DN kosong";

        try {
            $conn = $this->Function->ldapConnect(true);
            if (ldap_delete($conn, $dn)) {
                echo "sukses";
            } else {
                echo "gagal: " . ldap_error($conn);
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        } finally {
            $this->Function->ldapDisconnect($conn);
        }
    }

    public function tambah() {
        $this->autoRender = false;

        // var_dump($_POST);exit();

        try {
            $conn = $this->Function->ldapConnect(true);
            $dn = "uid={$_POST['id']},ou={$_POST['ou']}," . $this->Function->ldapConfig['base_dn'];

            $entry = [
                "objectClass"      => ["inetOrgPerson", "bernofarmPerson"],
                "uid"              => $_POST['id'] ?? '',
                "sn"               => $_POST['sn'] ?? '-',
                "cn"               => $_POST['nama'] ?? '',
                "description"      => $_POST['ket'] ?? '',
                "employeeNumber"   => $_POST['nik'] ?? '',
                "departmentNumber" => $_POST['divisi'] ?? '',
                "employeeType"     => $_POST['status'] ?? '',
                "noKTP"            => $_POST['ktp'] ?? '',
                "firstNik"         => $_POST['nikawal'] ?? '',
                "lastNik"          => $_POST['nikakhir'] ?? '',
                "birthDate"        => $_POST['tgllahir'] ?? '',
                "mail"             => $_POST['email'] ?? '',
                "ou"               => $_POST['akses'] ?? '',
                "userPassword"     => $_POST['password'] ?? 'user123'
            ];
            if (@ldap_add($conn, $dn, $entry)) {
                echo "sukses";
            } else {
                echo "gagal: " . ldap_error($conn);
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        } finally {
            $this->Function->ldapDisconnect($conn);
        }
    }

    public function ubah() {
        $this->autoRender = false;

        // var_dump($_POST);exit();

        try {
            $conn = $this->Function->ldapConnect(true);
            $dn = $_POST['dn'];
            
            $entry = [
                "uid"              => $_POST['id'] ?? '',
                "sn"               => $_POST['sn'] ?? '-',
                "cn"               => $_POST['nama'] ?? '',
                "description"      => $_POST['ket'] ?? '',
                "employeeNumber"   => $_POST['nik'] ?? '',
                "departmentNumber" => $_POST['divisi'] ?? '',
                "employeeType"     => $_POST['status'] ?? '',
                "noKTP"            => $_POST['ktp'] ?? '',
                "firstNik"         => $_POST['nikawal'] ?? '',
                "lastNik"          => $_POST['nikakhir'] ?? '',
                "birthDate"        => $_POST['tgllahir'] ?? '',
                "ou"               => $_POST['akses'] ?? '',
                "mail"             => $_POST['email'] ?? '',
                
            ];

            if (@ldap_modify($conn, $dn, $entry)) {
                echo "sukses";
            } else {
                echo "gagal: " . ldap_error($conn);
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        } finally {
            $this->Function->ldapDisconnect($conn);
        }
    }

    public function ubahUid($uidLama, $uidBaru, $ouLama, $ouBaru) {
        $this->autoRender = false;

        try {
            $conn = $this->ldapConnect();

            $dn = "uid=$uidLama,ou=$ouLama,dc=bagus,dc=local";

            $newrdn = "uid=$uidBaru";

            $newparent = "ou=$ouBaru,dc=bagus,dc=local";

            if (ldap_rename($conn, $dn, $newrdn, $newparent, true)) {
                // echo "sukses";
            } else {
                echo "gagal";
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        } finally {
            $this->Function->ldapDisconnect($conn);
        }
    }

    public function mailtest(){
        $this->autoRender = false;
        date_default_timezone_set('Asia/Jakarta');

        $key = "B3rn04p1";

        $headers = getallheaders();
        $clientKey = isset($headers['Authorization']) ? $headers['Authorization'] : null;

        // Validasi API Key
        if ($clientKey !== $key) {
            http_response_code(401); // Unauthorized
            header('Content-Type: application/json');
            echo json_encode([
                "status"  => "error",
                "message" => "Invalid API Key"
            ]);
            exit;
        }

        $PREAUTH_KEY="5386629eecd3971d5770bd0b1424e6ef7a31f53fdaa7c90a7ab027d7cbca4496";
        $WEB_MAIL_PREAUTH_URL="https://mailtest.bernofarm.com/service/preauth";
        
        $user   = "bagus";
        $domain = "mailtest.bernofarm.com";
        $email  = "{$user}@{$domain}";

        $timestamp    = time() * 1000;
        $preauthToken = hash_hmac("sha1", $email."|name|0|".$timestamp, $PREAUTH_KEY);
        $preauthURL   = $WEB_MAIL_PREAUTH_URL
                    . "?account=".$email
                    . "&by=name"
                    . "&timestamp=".$timestamp
                    . "&expires=0"
                    . "&preauth=".$preauthToken;

        header('Content-Type: application/json');
        echo json_encode([
            "status" => "success",
            "email"  => $email,
            "url"    => $preauthURL
        ]);
    }

    public function ubahstatus(){
        $this->autoRender = false;

        $zimbraHost  = "https://mailtest.bernofarm.com:7071/service/admin/soap";
        $zimbraAdmin = "admin@mailtest.bernofarm.com";
        $zimbraPass  = "You4tourlah";
        $userEmail   = "bagus@mailtest.bernofarm.com";
        $newStatus   = "closed"; // "active", "closed", "locked", "maintenance"

        $loginXML = <<<EOT
        <soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope">
        <soap:Body>
            <AuthRequest xmlns="urn:zimbraAdmin">
            <name>{$zimbraAdmin}</name>
            <password>{$zimbraPass}</password>
            </AuthRequest>
        </soap:Body>
        </soap:Envelope>
        EOT;

        $response = $this->sendRequest($zimbraHost, $loginXML);
        $xml = simplexml_load_string($response);
        $xml->registerXPathNamespace('zimbra', 'urn:zimbraAdmin');
        $xml->registerXPathNamespace('soap', 'http://www.w3.org/2003/05/soap-envelope');
        $authToken = (string) $xml->xpath('//zimbra:authToken')[0] ?? null;

        if (empty($authToken)) {
            die("Gagal mendapatkan authToken.");
        }

        $getAccountXML = <<<EOT
        <soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope">
        <soap:Header>
            <context xmlns="urn:zimbra"><authToken>{$authToken}</authToken></context>
        </soap:Header>
        <soap:Body>
            <GetAccountRequest xmlns="urn:zimbraAdmin">
            <account by="name">{$userEmail}</account>
            </GetAccountRequest>
        </soap:Body>
        </soap:Envelope>
        EOT;

        $response = $this->sendRequest($zimbraHost, $getAccountXML);
        $xml = simplexml_load_string($response);
        $xml->registerXPathNamespace('zimbra', 'urn:zimbraAdmin');
        $accountId = (string) $xml->xpath('//zimbra:GetAccountResponse/zimbra:account/@id')[0] ?? null;

        if (empty($accountId)) {
            die("Gagal mendapatkan accountId.");
        }

        $modifyXML = <<<EOT
        <soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope">
        <soap:Header>
            <context xmlns="urn:zimbra"><authToken>{$authToken}</authToken></context>
        </soap:Header>
        <soap:Body>
            <ModifyAccountRequest xmlns="urn:zimbraAdmin" id="{$accountId}">
            <a n="zimbraAccountStatus">{$newStatus}</a>
            </ModifyAccountRequest>
        </soap:Body>
        </soap:Envelope>
        EOT;

        $response = $this->sendRequest($zimbraHost, $modifyXML);

        if (strpos($response, "ModifyAccountResponse") !== false) {
            echo "Status berhasil diubah\n";
        } else {
            echo "Gagal mengubah status.\n";
            echo "Response: \n$response\n";
        }
    }

    private function sendRequest($url, $data) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $data,
            CURLOPT_HTTPHEADER     => ["Content-Type: application/soap+xml"],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);
        $response = curl_exec($ch);
        $error    = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            die("cURL Error: " . $error);
        }
        return $response;
    }
}
	
?>



