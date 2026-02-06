<?php
declare(strict_types=1);

namespace App\Core;

class Mailer
{
    public static function send(string $to, string $subject, string $body): bool
    {
        $headers = [];
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=utf-8';
        $from = MAIL_FROM_NAME . ' <' . MAIL_FROM_ADDRESS . '>';
        $headers[] = 'From: ' . $from;
        
        // Use Admin Address as Reply-To if defined, otherwise fallback to From
        $replyTo = defined('MAIL_ADMIN_ADDRESS') && MAIL_ADMIN_ADDRESS ? MAIL_ADMIN_ADDRESS : MAIL_FROM_ADDRESS;
        $headers[] = 'Reply-To: ' . $replyTo;

        $headersString = implode("\r\n", $headers);

        // Suppress warnings in local environment where mail server might not be configured
        return @mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, $headersString);
    }
}

