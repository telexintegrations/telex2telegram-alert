<?php 
require '../assets/header.php';

http_response_code(202);

use telex\telex;

// Process Request
if ($input) {
    telex::telexUpdate($input);
}
