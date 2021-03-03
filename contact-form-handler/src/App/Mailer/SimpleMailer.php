<?php

namespace App\Mailer;

use Dotenv\Dotenv;
use Exception;
use Latte\Engine;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class SimpleMailer
{
  private const LOG_FILE_PATH = __DIR__ . '/../../../logs/mailer.log';

  private PHPMailer $phpMailer;
  private Engine $templateEngine;
  private Logger $logger;

  public function __construct(string $configDir)
  {
    // Load credentials from .env file
    $dotenv = Dotenv::createImmutable($configDir);
    $dotenv->load();

    $this->phpMailer = new PHPMailer(true);
    $this->phpMailer->SMTPDebug = SMTP::DEBUG_OFF;
    $this->phpMailer->isSMTP();
    $this->phpMailer->Host = env('SMTP_HOST');
    $this->phpMailer->Port = env('SMTP_PORT');
    $this->phpMailer->SMTPAuth = true;
    $this->phpMailer->Username = env('SMTP_USERNAME');
    $this->phpMailer->Password = env('SMTP_PASSWORD');
    $this->phpMailer->SMTPSecure = env('SMTP_ENCRYPTION_TYPE');

    // Create a logger
    $this->logger = new Logger('default');
    $this->logger->pushHandler(new StreamHandler(self::LOG_FILE_PATH, Logger::NOTICE));

    // Init Latte template engine
    $this->templateEngine = new Engine();
    $this->templateEngine->setTempDirectory('/tmp/latte-cached-templates/');
  }

  public function sendTemplatedEmail(string $templateName, array $params, string $subject): bool
  {
    $renderedTemplate = $this->templateEngine->renderToString($templateName, ['params' => $params]);



    try {
      // Recipients
      $this->phpMailer->setFrom(env('EMAIL_FROM_ADDRESS'));
      $this->phpMailer->addAddress(env('EMAIL_RECIPIENT_ADDRESS'));

      // Content
      $this->phpMailer->isHTML(true);
      $this->phpMailer->Subject = $subject;
      $this->phpMailer->Body = $renderedTemplate;
      $this->phpMailer->AltBody = json_encode($params, JSON_THROW_ON_ERROR);


      $this->phpMailer->send();
    } catch (Exception $e) {
      $this->logger->error(
        __METHOD__ . ' - Error while sending the email',
        [
          'mailerError' => $this->phpMailer->ErrorInfo ?? 'not-available',
          'exception' => $e,
          'postData' => $_POST,
        ]
      );

      return false;
    }

    return true;
  }

}
