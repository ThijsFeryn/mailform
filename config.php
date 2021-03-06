<?php
/**
 * False: use standard PHP mail() function
 * True: use SMTP directly. Relay using $host & $port
 */
$smtp = false;
/**
 * Only required when SMTP is true
 */
$host = '';
/**
 * Only required when SMTP is true and when authentication is required
 */
$username = '';
$password = '';
/**
 * Only required when SMTP is true
 */
$port = '25';
/**
 * Do you want debugging?
 */
$debug = false;
/**
 * Some e-mail headers you can predefine
 */
$to = null;
$from = null;
$fromname = null;
$subject = null;
$redirect = null;