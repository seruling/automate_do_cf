<?php
$cf_key = "CF_API_KEY"; //Cloudfalre API key
$cf_email = "email@email.com"; //Cloudflare email id
$domain_name = "example.com"; //domain name

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,"https://api.cloudflare.com/client/v4/zones?name=$domain_name");
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    "X-Auth-Email: $cf_email",
    "X-Auth-Key: $cf_key",
    'Content-Type: application/json'
));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$output_json = curl_exec ($ch);
curl_close ($ch);

$output_array = json_decode($output_json,true);
$zone_id = $output_array["result"][0]["id"];

$file = fopen("created_records.txt", "r") or exit("Unable to open file!");
$line_count = 0;

while(!feof($file))
{
	$line =  fgets($file);

	if (strlen($line) < 5) {
		continue;
	}
	else {
		$line = trim($line);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,"https://api.cloudflare.com/client/v4/zones/$zone_id/dns_records/$line");
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"X-Auth-Email: $cf_email",
			"X-Auth-Key: $cf_key",
			'Content-Type: application/json'
		));
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$output_json = curl_exec($ch);
		curl_close ($ch);
	}
	$line_count++;
}
fclose($file);


?>
