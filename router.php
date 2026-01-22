<?php
// router.php
if (preg_match('/\.(?:png|jpg|jpeg|gif|css|js|ico|pdf|svg|woff|woff2|ttf|eot)$/', $_SERVER["REQUEST_URI"])) {
    return false;    // serve the requested resource as-is.
} else {
    include 'index.php';
}
