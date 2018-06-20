<?php

$do_key = "DO_API_KEY"; //DigitalOcean API key
$cf_key = "CF_API_KEY"; //Cloudfalre API key
$cf_email = "email@email.com"; //Cloudflare email id
$domain_name = "example.com"; //domain name
$image_id = "34976253"; //image id. refer https://developers.digitalocean.com/documentation/v2/#list-all-images

$subdomain_start = 1;
$subdomain_end = 5;

$tags = array("api_testing"); //delete droplets at once - https://developers.digitalocean.com/documentation/v2/#deleting-droplets-by-tag

for ($x = $subdomain_start; $x <= $subdomain_end; $x++) {

	//create droplet
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,"https://api.digitalocean.com/v2/droplets");
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	    "Authorization: Bearer $do_key",
	    'Content-Type: application/json'
	));

	$data = array(
		"name" => "$x.$domain_name",
		"region" => "sgp1",
		"size" => "s-1vcpu-1gb",
		"image" => $image_id,
		"ssh_keys" => null,
		"backups" => false,
		"ipv6" => false,
		"monitoring" => true,
		"user_data" => null,
		"private_networking" => true,
		"volumes" => null,
		"tags" => $tags,
	);  
	$data_string = json_encode($data);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);                                                                  
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$output_json = curl_exec ($ch);
	curl_close ($ch);

	$output_array = json_decode($output_json,true);
	$droplet_id = $output_array["droplet"]["id"];


	//get droplet IPv4, based on droplet_id provided from create droplet response.
	while (1) { //keep trying until ipv4 is available
		sleep(1);
		$output_json = "";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,"https://api.digitalocean.com/v2/droplets/$droplet_id");
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		    "Authorization: Bearer $do_key",
		    'Content-Type: application/json'
		));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$output_json = curl_exec ($ch);
		curl_close ($ch);

		$output_array = json_decode($output_json,true);
		$droplet_name = $output_array["droplet"]["name"];

		if ($output_array["droplet"]["networks"]["v4"][0]["ip_address"] != "") {
			$ipv4 = $output_array["droplet"]["networks"]["v4"][0]["ip_address"];
			break;
		}
	}

	//get domain zone id
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


	//add A record
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,"https://api.cloudflare.com/client/v4/zones/$zone_id/dns_records");
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	    "X-Auth-Email: $cf_email",
	    "X-Auth-Key: $cf_key",
	    'Content-Type: application/json'
	));

	$data = array(
		"type" => "A",
		"name" => "$x",
		"content" => "$ipv4",
		"proxied" => false
	);  
	$data_string = json_encode($data);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);                                                                  
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$output_json = curl_exec ($ch);
	curl_close ($ch);

	$output_array = json_decode($output_json,true);
	$cf_record_id = $output_array["result"]["id"] . "\n";
	file_put_contents("created_records.txt", $cf_record_id, FILE_APPEND | LOCK_EX);

	echo "$droplet_name ($droplet_id) : $ipv4 : $cf_record_id";
}

?>
