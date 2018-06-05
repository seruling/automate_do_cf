# automate_do_cf
automating process of creating droplets in digitalocean and A record in cloudflare utilising the API, using PHP.

sometime there is a need to deploy many droplets in DO. for example when conducting training. and it would be better if each droplet to be "remembered" not by its IP. thus, creating a subdomain for each of it would be helpful.

the script make use of DigitalOcean and Cloudflare.

usage:
php deploy.php

deleting droplets can be done at one go, using delete by tag. each droplet created with deploy.php can be assigned with tags. modify this in deploy.php
https://developers.digitalocean.com/documentation/v2/#deleting-droplets-by-tag

all created A records in cloudflare will be assigned with an identifier. the identifier will be write into a text file, created_records.txt. deleting the created records, where the identifier is in created_records.txt, can be done using delete_cf.php
