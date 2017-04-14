<?php
require 'Api.php';

$results = Api::request('GET', 'accounts/listtags');
print_r($results);
?>