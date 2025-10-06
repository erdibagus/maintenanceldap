<?php
class ApizimbrasController extends AppController{
	public $components = array('Function');

    function index() {
        $this->autoRender = false;

        if (!empty($_GET)) {
            http_response_code(400); // Bad Request
            $data = ([
                "status" => "error",
                "message" => "Query string tidak diperbolehkan. Kirim data melalui body request."
            ]);
            $this->sendJson($data);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405); 
            $data = ([
                "status" => "error",
                "message" => "Hanya POST yang diperbolehkan"
            ]);
            $this->sendJson($data);
            exit();
        }

        if (empty($_POST)) {
            http_response_code(400);
            $data = ([
                "status" => "error",
                "message" => "Body request kosong atau tidak valid."
            ]);
            $this->sendJson($data);
            exit;
        }

        date_default_timezone_set('Asia/Jakarta');

        $key = "B3rn04p1";

        $headers = getallheaders();
        $clientKey = isset($headers['Authorization']) ? $headers['Authorization'] : null;

        // Validasi API Key
        if ($clientKey !== $key) {
            http_response_code(401); 
            $data = ([
                "status"  => "error",
                "message" => "API Key salah"
            ]);
            $this->sendJson($data);
            exit;
        }

        $PREAUTH_KEY="5386629eecd3971d5770bd0b1424e6ef7a31f53fdaa7c90a7ab027d7cbca4496";
        $WEB_MAIL_PREAUTH_URL="https://mailtest.bernofarm.com/service/preauth";
        
        $user   = $_POST['user'];
        // $user   = "bagus";
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

        $data = ([
            "status" => "success",
            "email"  => $email,
            "url"    => $preauthURL
        ]);

        $this->sendJson($data);
    }

    private function sendJson($data) {
        header("Content-Type: application/json");
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit();
    }
}
	
?>
