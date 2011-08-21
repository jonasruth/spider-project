<?php

$config = new stdClass;

// Initial website
$config->url = 'http://www.php.net/';

// Allow only this domain to be visited
$config->only_this_domain = true;

// Max of iterations allowed
$config->max_depth = 15;

$config->connection_proxy_enabled = false;
$config->connection_proxy = none;
$config->connection_proxy_port = none;
