<?php
class ApizimbrasController extends AppController{
	public $components = array('Function');

    function index() {
    $this->autoRender = false;

    // ❌ Tolak jika ada query string
    if (!empty($_GET)) {
        http_response_code(400);
        $data = [
            "status" => "error",
            "message" => "Query string tidak diperbolehkan. Kirim data melalui body request."
        ];
        $this->sendJson($data);
        exit;
    }

    // ❌ Hanya izinkan metode POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        $data = [
            "status" => "error",
            "message" => "Hanya POST yang diperbolehkan"
        ];
        $this->sendJson($data);
        exit;
    }

    date_default_timezone_set('Asia/Jakarta');

    // 🔐 Basic Auth
    if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
        header('WWW-Authenticate: Basic realm="Restricted Area"');
        http_response_code(401);
        $data = [
            "status" => "error",
            "message" => "Autentikasi diperlukan (Basic Auth)."
        ];
        $this->sendJson($data);
        exit;
    }

    $email = $_SERVER['PHP_AUTH_USER'];
    $password = $_SERVER['PHP_AUTH_PW'];

    // Validasi password API (bisa diganti sesuai kebutuhan)
    $API_PASSWORD = "B3rn04p1";

    if ($password !== $API_PASSWORD) {
        http_response_code(401);
        $data = [
            "status" => "error",
            "message" => "Kredensial salah."
        ];
        $this->sendJson($data);
        exit;
    }

    // 🧮 Generate Preauth URL
    $PREAUTH_KEY = "345051456776cfdadefcfb5291d2ac12f008148b5267258bb2e187abe2fb8678";
    $WEB_MAIL_PREAUTH_URL = "https://mail.bernofarm.com/service/preauth";

    $timestamp = time() * 1000;
    $preauthToken = hash_hmac("sha1", $email . "|name|0|" . $timestamp, $PREAUTH_KEY);
    $preauthURL = $WEB_MAIL_PREAUTH_URL
        . "?account=" . $email
        . "&by=name"
        . "&timestamp=" . $timestamp
        . "&expires=0"
        . "&preauth=" . $preauthToken;

    $data = [
        "status" => "success",
        "email"  => $email,
        "url"    => $preauthURL
    ];

    $this->sendJson($data);
}


    private function sendJson($data) {
        header("Content-Type: application/json");
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit();
    }

    public function getAkun() {
        $this->autoRender = false;
        $endpoint    = "https://mail.bernofarm.com:7071/service/admin/soap";
        $adminUser   = "admin@bernofarm.com";
        $adminPass   = "You4tourlah";
        $ssl_verify  = true; // di production sebaiknya true

        function soap_post($endpoint, $xml, $ssl_verify = true) {
            $ch = curl_init($endpoint);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: text/xml; charset=utf-8"]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            if (!$ssl_verify) {
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            }
            $resp = curl_exec($ch);
            if ($resp === false) {
                $err = curl_error($ch);
                curl_close($ch);
                throw new Exception("cURL error: " . $err);
            }
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($http_code >= 400) {
                throw new Exception("HTTP error: " . $http_code . " Response: " . $resp);
            }
            return $resp;
        }

        try {
            // 1) Auth admin
            $authXml = <<<XML
    <soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope">
    <soap:Body>
        <AuthRequest xmlns="urn:zimbraAdmin">
        <account by="name">{$adminUser}</account>
        <password>{$adminPass}</password>
        </AuthRequest>
    </soap:Body>
    </soap:Envelope>
    XML;

            $authResp = soap_post($endpoint, $authXml, $ssl_verify);
            $sx = new SimpleXMLElement($authResp);
            $sx->registerXPathNamespace('adm', 'urn:zimbraAdmin');
            $tokenNodes = $sx->xpath('//adm:authToken | //authToken');
            if (!$tokenNodes || count($tokenNodes) == 0) {
                throw new Exception("Gagal auth: tidak mendapatkan authToken. Response: " . $authResp);
            }
            $authToken = (string)$tokenNodes[0];

            // 2) Ambil semua akun
            $limit = 250;
            $offset = 0;
            $allAccounts = [];

            // daftar akun sistem yang ingin disembunyikan
            // $excludePatterns = ['spam', 'ham', 'virus', 'galsync'];

            while (true) {
                $getXml = <<<XML
    <soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope">
    <soap:Header>
        <context xmlns="urn:zimbra">
        <authToken>{$authToken}</authToken>
        </context>
    </soap:Header>
    <soap:Body>
        <GetAllAccountsRequest xmlns="urn:zimbraAdmin" limit="{$limit}" offset="{$offset}" />
    </soap:Body>
    </soap:Envelope>
    XML;

                $resp = soap_post($endpoint, $getXml, $ssl_verify);

                $xml = new SimpleXMLElement($resp);
                $xml->registerXPathNamespace('a', 'urn:zimbraAdmin');
                $accounts = $xml->xpath('//a:account | //account');

                if (!$accounts || count($accounts) == 0) {
                    break;
                }

                $excludePrefixes = ['spam.','ham.','virus-quarantine.','galsync.'];

                foreach ($accounts as $acc) {
                    $attrs = $acc->attributes();
                    $name = isset($attrs['name']) ? (string)$attrs['name'] : null;
                    // $id   = isset($attrs['id'])   ? (string)$attrs['id']   : null;

                    // ambil localpart sebelum @
                    $local = explode('@', $name)[0];

                    // skip akun sistem berdasarkan prefix
                    $skip = false;
                    foreach ($excludePrefixes as $prefix) {
                        if (stripos($local, $prefix) === 0) { // hanya jika diawali prefix tsb
                            $skip = true;
                            break;
                        }
                    }
                    if ($skip) continue;

                    // ambil atribut tambahan
                    // $attrsList = [];
                    // foreach ($acc->xpath('./a') ?: [] as $a) {
                    //     $an = (string)$a['n'];
                    //     $av = (string)$a;
                    //     $attrsList[$an] = $av;
                    // }

                    $allAccounts[] = [
                        'name' => $name,
                        // 'id'   => $id,
                        // 'attrs'=> $attrsList
                    ];
                }

                if (count($accounts) < $limit) break;
                $offset += $limit;
                if ($offset > 1000000) break;
            }

            header('Content-Type: application/json; charset=utf-8');
            $this->sendJson(['status' => 'ok', 'count' => count($allAccounts), 'accounts' => $allAccounts], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        } catch (Exception $e) {
            header('Content-Type: application/json; charset=utf-8', true, 500);
            $this->sendJson(['status'=>'error','message'=>$e->getMessage(),'trace'=>$e->getTraceAsString()]);
        }
    }

}
	
?>
