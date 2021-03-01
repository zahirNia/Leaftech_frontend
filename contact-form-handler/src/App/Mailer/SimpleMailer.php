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
  private const LOG_FILE_PATH = __DIR__ . 'logs/mailer.log';

  private PHPMailer $phpMailer;
  private Engine $templateEngine;
  private Logger $logger;

  public function __construct(string $configDir)
  {
    // Load credentials from .env file
    $dotenv = Dotenv::createImmutable($configDir);
    $dotenv->load();

    $this->phpMailer = new PHPMailer(true);
    $this->phpMailer->SMTPDebug = SMTP::DEBUG_SERVER; // Enable verbose output
    $this->phpMailer->isSMTP();
    $this->phpMailer->Host = getenv('SMTP_HOST');
    $this->phpMailer->Port = getenv('SMTP_PORT');
    $this->phpMailer->SMTPAuth = true;
    $this->phpMailer->Username = getenv('SMTP_USERNAME');
    $this->phpMailer->Password = getenv('SMTP_PASSWORD');
    $this->phpMailer->SMTPSecure = getenv('SMTP_ENCRYPTION_TYPE');

    // Create a logger
    $logger = new Logger('default');
    $logger->pushHandler(new StreamHandler(self::LOG_FILE_PATH, Logger::NOTICE));

    // Init Latte template engine
    $this->templateEngine = new Engine();
    $this->templateEngine->setTempDirectory('/tmp/latte-cached-templates/');
  }

  public function sendTemplatedEmail(string $templateName, array $params, string $subject): void
  {
    $renderedTemplate = $this->templateEngine->renderToString($templateName, ['params' => $params]);

    try {
      // Recipients
      $this->phpMailer->setFrom(getenv('EMAIL_FROM_ADDRESS'));
      $this->phpMailer->addAddress(getenv('EMAIL_RECIPIENT_ADDRESS'));

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
    }

  }

}
