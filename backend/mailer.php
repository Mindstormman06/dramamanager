<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

function send_email($to, $subject, $body, $altBody = '') {
    $mail = new PHPMailer(true);

    try {
        // Load .env file
        $envFile = __DIR__ . '/../.env';
        if (!file_exists($envFile)) {
            throw new Exception('.env file not found');
        }
        
        $env = parse_ini_file($envFile);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host       = $env['SMTP_HOST'] ?? 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $env['SMTP_USERNAME'] ?? '';
        $mail->Password   = $env['SMTP_PASSWORD'] ?? '';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = (int)($env['SMTP_PORT'] ?? 587);

        // From/To
        $mail->setFrom($env['SMTP_FROM'] ?? 'noreply@example.com', $env['SMTP_FROM_NAME'] ?? 'Theatre Manager');
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = $altBody ?: strip_tags($body);

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
