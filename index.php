<?php
require 'Api.php';

$results = Api::get('accounts/listtags');
print_r($results);
?>