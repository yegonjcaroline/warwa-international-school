<?php
require_once dirname(__DIR__) . '/config/app.php';
require_once __DIR__ . '/sms.php';
if (is_file(dirname(__DIR__) . '/vendor/autoload.php')) require_once dirname(__DIR__) . '/vendor/autoload.php';
function send_notice(string $to, string $subject, string $message): bool {
    $from = env_value('MAIL_FROM');
    if ($from === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) return false;
    if (env_value('SMTP_HOST') !== '' && is_file(dirname(__DIR__) . '/vendor/autoload.php')) {
        require_once dirname(__DIR__) . '/vendor/autoload.php';
        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP(); $mail->Host = env_value('SMTP_HOST'); $mail->Port = (int)env_value('SMTP_PORT', '587');
            $mail->SMTPAuth = env_value('SMTP_USERNAME') !== ''; $mail->Username = env_value('SMTP_USERNAME'); $mail->Password = env_value('SMTP_PASSWORD');
            if (env_value('SMTP_ENCRYPTION', 'tls') === 'ssl') $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            elseif (env_value('SMTP_ENCRYPTION', 'tls') === 'tls') $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->CharSet = 'UTF-8'; $mail->setFrom($from, 'Warwa International School'); $mail->addAddress($to); $mail->Subject = $subject; $mail->Body = $message;
            return $mail->send();
        } catch (Throwable $error) { error_log('School email delivery failed: ' . $error->getMessage()); return false; }
    }
    $headers = "From: Warwa International School <{$from}>\r\n" . "Reply-To: {$from}\r\n" . "Content-Type: text/plain; charset=UTF-8\r\n";
    return @mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $message, $headers);
}
function notify_parties(PDO $pdo, int $studentId, string $subject, string $message): void
{
    $stmt = $pdo->prepare("SELECT email, phone FROM users WHERE id=? AND role='student'");
    $stmt->execute([$studentId]);
    $student = $stmt->fetch();
    $adminEmail = env_value('SCHOOL_EMAIL');
    $adminPhone = env_value('SMS_ADMIN_PHONE');
    $smsMessage = $subject . ': ' . preg_replace('/\\s+/', ' ', $message);

    if ($student) {
        send_notice($student['email'], $subject, $message);
        if (!empty($student['phone'])) send_sms((string) $student['phone'], $smsMessage);
    }
    if ($adminEmail !== '' && (!$student || strtolower($adminEmail) !== strtolower($student['email']))) {
        send_notice($adminEmail, '[Warwa Portal] ' . $subject, $message);
    }
    $studentPhone = $student && !empty($student['phone'])
        ? normalize_ke_sms_number((string) $student['phone'])
        : '';
    if ($adminPhone !== '' && normalize_ke_sms_number($adminPhone) !== $studentPhone) {
        send_sms($adminPhone, '[Warwa Portal] ' . $smsMessage);
    }
}
