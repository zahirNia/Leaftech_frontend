<?php

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

require_once __DIR__ . '/vendor/autoload.php';

/**
 * This script is mailing the contact form data to an email address.
 * It uses PHPMailer.
 */

// create a log channel
$logFileName = 'contact-form-mailer.log';
$logger = new Logger('default');
$logger->pushHandler(new StreamHandler(__DIR__ . 'logs/' . $logFileName, Logger::NOTICE));

$postData = $_POST;

/**
 * Payload example:
 *
 * name: Othmane
 * Email: web@othmanemoustauda.io
 * company: CoopSpace
 * role: Energy commissioner
 * newsletter: on
 * SA: on
 * BEF: on
 * IP: on
 * PV: on
 * WPA: on
 * message: Trying with Chrome
 *
 */

print_r($postData);


//Instantiation and passing `true` enables exceptions
$mail = new PHPMailer(true);

try {
  //Server settings
  $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
  $mail->isSMTP();                                            //Send using SMTP
  $mail->Host       = 'smtp.example.com';                     //Set the SMTP server to send through
  $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
  $mail->Username   = 'user@example.com';                     //SMTP username
  $mail->Password   = 'secret';                               //SMTP password
  $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         //Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
  $mail->Port       = 587;                                    //TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

  //Recipients
  $mail->setFrom('from@example.com', 'Contact form mailer');
  $mail->addAddress('joe@example.net');

  //Content
  $mail->isHTML(true);                                  //Set email format to HTML
  $mail->Subject = 'Here is the subject';
  $mail->Body    = 'This is the HTML message body <b>in bold!</b>';
  $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

  $mail->send();
} catch (Exception $e) {
  $logger->error(
    'Error while sending the email',
    [
      'mailerError' => $mail->ErrorInfo,
      'exception' => $e,
      'postData' => $_POST,
    ]
  );
}
