<?php

use App\Mailer\SimpleMailer;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

require_once __DIR__ . '/vendor/autoload.php';

const SPAM_LOG_FILE_PATH = __DIR__ . '/logs/spam-messages.log';
/**
 * This field is used to help recognize spam messages using the honeypot technique.
 * For more info about the technique see:
 *   https://web.archive.org/web/20210308161605/https://www.amitmerchant.com/prevent-form-spamming-by-bots-using-this-honeypot-technique-in-php/
 */
const FIELD_NAME_HONEYPOT_SPAM = 'myExtraField';

/**
 * This script is mailing the contact form data to an email address.
 * Payload example:
 *
 *   name: Othmane
 *   Email: web@othmanemoustauda.io
 *   company: CoopSpace
 *   role: Energy commissioner junior
 *   newsletter: on
 *   SA: on
 *   BEF: on
 *   IP: on
 *   PV: on
 *   WPA: on
 *   message: Hello! I'm interested in LeafTech and would like to have a call with you.
 */


// Create a logger for the messages flagged as spam
$spamLogger = new Logger('default');
$spamLogger->pushHandler(new StreamHandler(SPAM_LOG_FILE_PATH, Logger::NOTICE));

// This field is invisible to the user, if it has been filled, we assume it's a bot.
$honeypotValue = $_POST[FIELD_NAME_HONEYPOT_SPAM] ?? null;
if (!empty($honeypotValue)) {
  // Potential spam detected
  $spamLogger->info(
    'Spam message detected',
    [
      'postData' => $_POST,
      'ip' => $_SERVER['REMOTE_ADDR']
    ]
  );

  print "The delivery of your message was not possible. Reason: it seems created aut0matica11y.";
  die(); // We logged the message and can quit the script.
}


$params = [];
foreach ($_POST as $key => $value) {
  $params[] = ['key' => $key, 'value' => $value];
}

$smoothMailer = new SimpleMailer(__DIR__);
$isSent = $smoothMailer->sendTemplatedEmail(
  __DIR__ . '/contact-form-email.template.latte',
  $params,
  'New email from the website contact form'
);

if ($isSent) {
  print '
    <html>
    <title>Message sent!</title>
    <body style="text-align: center">
      <img src="../assets/Logos/Leaftech%20logo/LEAFTECH-logo@3x.png">
      <h2>Thank you!</h2>
      <h3>Your message has been successfully sent. We will contact you very soon!</h3>
      <p>
        <a href="/">Return to the homepage</a>
      </p>
    </body>
    </html>
';
} else {
  print 'Error while sending the email. Instead, please write to: contact@leaftech.eu.
        Original message: ' . htmlentities($_POST['message']);
}

function env(string $varName): ?string
{
  $content = getenv($varName);
  if (empty($content)) {
    $content = $_ENV[$varName];
  }

  return $content;
}
