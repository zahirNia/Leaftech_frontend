<?php

use App\Mailer\SimpleMailer;

require_once __DIR__ . '/vendor/autoload.php';

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

$params = [];
foreach ($_POST as $key => $value) {
  $params[] = ['key' => $key, 'value' => $value];
}

$smoothMailer = new SimpleMailer(__DIR__);
$smoothMailer->sendTemplatedEmail(
  __DIR__ . '/contact-form-email.template.latte',
  $params,
  'New email from the website contact form'
);
