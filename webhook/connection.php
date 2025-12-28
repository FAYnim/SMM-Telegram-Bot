<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Metode request tidak valid.', "method" => $_SERVER['REQUEST_METHOD']]);
    exit;
}

$action = isset($_POST['action']) ? $_POST['action'] : '';
$token = isset($_POST['api_token']) ? $_POST['api_token'] : '';

if (empty($token)) {
    echo json_encode(['status' => 'error', 'message' => 'API Token tidak boleh kosong.']);
    exit;
}

$response = [];

$url_req = "https://api.telegram.org/bot".$token;

if($action == "set") {
	$host = $_SERVER["HTTP_HOST"];
	$path = $_SERVER["PHP_SELF"];
	$self = dirname($_SERVER["PHP_SELF"]);
	
	$url_rep = str_replace("webhook", "index", $self);
	$webhook_url = "https://".$host.$url_rep;

	$url_req .= "/setWebhook";
	$data = [
		"url" => $webhook_url
	];

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url_req);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	$response = curl_exec($ch);
	curl_close($ch);
} else if($action == "check") {
	$response = file_get_contents($url_req."/getWebhookInfo");
} else if($action == "delete") {
	$url_req .= "/deleteWebhook";
	$data = [
	    'drop_pending_updates' => true
	];

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url_req);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	$response = curl_exec($ch);
	curl_close($ch);
}

// Kirim respon kembali ke JavaScript dalam format JSON
echo json_encode(json_decode($response, 1));
