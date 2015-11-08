<?php
// From: https://github.com/abraham/twitteroauth-demo/blob/master/clearsessions.php
/**
 * @file
 * Clears PHP sessions and redirects to the connect page.
 */
 
/* Load and clear sessions */
session_start();
session_destroy();
 
/* Redirect to page with the connect to Twitter option. */
header('Location: ./');
exit;