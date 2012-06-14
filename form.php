<?php
try{
    /**
     * Config
     */
    require('config.php');
    /**
     * Include
     */
    require_once 'lib/swift_required.php';
    /**
     * Validate
     */
    if(empty($_REQUEST['to'])){
        throw new Exception('To address not set');
    }
    /**
     * Init
     */
    $message = Swift_Message::newInstance();
    /**
     * to
     */
    $message->setTo($_REQUEST['to']);
    /**
     * subject
     */
    if(isset($_REQUEST['subject'])){
        $message->setSubject($_REQUEST['subject']);
    } elseif(isset($_SERVER['HTTP_REFERER'])){
        $message->setSubject("A message has been sent from {$_SERVER['HTTP_REFERER']} at ".date('Y-m-d H:i:s'));
    } else {
        $message->setSubject("A message has been sent at ".date('Y-m-d H:i:s'));
    }
    /**
     * from
     */
    if(isset($_REQUEST['from'])){
        if(isset($_REQUEST['fromname'])){
            $message->setFrom($_REQUEST['from'],$_REQUEST['fromname']);
        } else {
            $message->setFrom($_REQUEST['from']);
        }
    } elseif(filter_var($_SERVER['SERVER_ADMIN'],FILTER_VALIDATE_EMAIL) !== false) {
        $message->setFrom($_SERVER['SERVER_ADMIN'],'Contact form');
    } else {
        $message->setFrom('noreply@'.$_SERVER['HTTP_HOST'],'Contact form');
    }
    /**
     * body
     */
    $body = '';
    foreach($_REQUEST as $field=>$value){
        $body .= "{$field}: {$value}".PHP_EOL;
    }
    $message->setBody($body);
    /**
     * transport
     */
    if($smtp){
        $transport = Swift_SmtpTransport::newInstance($host,$port);
        if(strlen($username) > 0 && strlen($password) > 0){
            $transport->setUsername($username)->setPassword($password);
        }
    } else {
        $transport = Swift_MailTransport::newInstance();
    }
    /**
     * mailer
     */
    $mailer = Swift_Mailer::newInstance($transport);
    /**
     * Logger
     */
    if($smtp && $debug){
        $logger = new Swift_Plugins_Loggers_ArrayLogger();
        $mailer->registerPlugin(new Swift_Plugins_LoggerPlugin($logger));
    }
    if(!$mailer->send($message)){
        throw new Exception('Sending e-mail failed');
    }
    /**
     * redirect
     */
     if(isset($_REQUEST['redirect']) && !$debug){
        header("Location:{$_REQUEST['redirect']}");
     } else {
         echo "<h1>Success</h1>".PHP_EOL;
         echo "Sending e-mail succeeded";
     }
} catch(Exception $e){
        echo "<h1>An error occured: ".get_class($e)."</h1>".PHP_EOL;
        echo "<h2>Message</h2>".PHP_EOL;
        echo "<pre>{$e->getMessage()}</pre>".PHP_EOL;
        echo "<h2>Backtrace</h2>".PHP_EOL;
        echo "<pre>{$e->getTraceAsString()}</pre>";
}
/**
 * Debug
 */
if($debug){
    echo "<h2>Debug</h2>".PHP_EOL;
    echo "<ul>".PHP_EOL;
    echo "<li>To: ".var_export($message->getTo(),true)."</li>".PHP_EOL;
    echo "<li>Subject: {$message->getSubject()}</li>".PHP_EOL;
    echo "<li>From: ".var_export($message->getFrom(),true)."</li>".PHP_EOL;
    if($smtp){
        echo "<li>SMTP handler: yes</li>".PHP_EOL;
        echo "<li>SMTP host: {$transport->getHost()}</li>".PHP_EOL;
        echo "<li>SMTP port: {$transport->getPort()}</li>".PHP_EOL;
        if(strlen($username) > 0 && strlen($password) > 0){
            echo "<li>SMTP authentication: yes</li>".PHP_EOL;
            echo "<li>SMTP username: {$transport->getUsername()}</li>".PHP_EOL;
            echo "<li>SMTP password: {$transport->getPassword()}</li>".PHP_EOL;
        } else {
            echo "<li>SMTP authentication: no</li>".PHP_EOL;
        }
    } else {
        echo "<li>SMTP handler: no</li>".PHP_EOL;
    }
    echo "</ul>".PHP_EOL;
    echo "<h2>Message body</h2>".PHP_EOL;
    echo "<pre>{$message->getbody()}</pre>".PHP_EOL;
    if($smtp && $debug){
        echo "<h2>Logger</h2>".PHP_EOL;
        echo "<pre>{$logger->dump()}</pre>".PHP_EOL;
    }
}