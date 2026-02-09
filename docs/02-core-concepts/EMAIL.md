# 📧 Email Guide

**Padi REST API Framework v1.0.0**

The Email component provides a simple wrapper around [PHPMailer](https://github.com/PHPMailer/PHPMailer) to send HTML and plain-text emails via SMTP.

---

## 📋 Table of Contents

- [Configuration](#configuration)
- [Basic Usage](#basic-usage)
- [Sending with Attachments](#sending-with-attachments)
- [Queued Emails (Recommended)](#queued-emails-recommended)
- [Troubleshooting](#troubleshooting)

---

## ⚙️ Configuration

Email settings are managed in your `.env` file and configured in `app/Config/mail.php`.

### .env Settings

```env
MAIL_DRIVER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="Padi REST API"
```

---

## 📍 Basic Usage

You can send emails directly using the `Core\Email` class. This is a synchronous operation, meaning the request will wait until the email is sent.

```php
use Core\Email;

$to = 'user@example.com';
$subject = 'Welcome to our App!';
$body = '<h1>Hello!</h1><p>Thank you for joining us.</p>';

$success = Email::send($to, $subject, $body);

if ($success) {
    // Email sent successfully
}
```

---

## 📎 Sending with Attachments

To send attachments, pass an array of file paths as the fourth parameter.

```php
use Core\Email;

$attachments = [
    '/path/to/invoice.pdf',
    '/path/to/report.xlsx'
];

Email::send('user@example.com', 'Your Invoice', '<p>Check attached.</p>', $attachments);
```

---

## 🚀 Queued Emails (Recommended)

Sending email via SMTP can be slow (1-5 seconds). For the best user experience, you should push email sending to the background queue.

### 1. Push to Queue

Instead of calling `Email::send()` directly in your controller, use `Core\Queue::push()`.

```php
use Core\Queue;
use App\Jobs\SendEmailJob;

Queue::push(SendEmailJob::class, [
    'email' => 'user@example.com',
    'subject' => 'Welcome!',
    'body' => '<h1>Welcome abroad!</h1>'
]);
```

### 2. Run the Worker

Ensure your queue worker is running to process the emails:

```bash
php scripts/queue-worker.php
```

---

## 🔍 Troubleshooting

### 1. Email not sending

- Check your SMTP credentials in `.env`.
- Ensure the `MAIL_PORT` and `MAIL_ENCRYPTION` match your provider's requirements (e.g., Gmail uses 587/tls or 465/ssl).
- Verify that your firewall allows outgoing connections on the SMTP port.

### 2. Slow Response Times

- If you are sending emails synchronously (`Email::send`), consider switching to [Queued Emails](#queued-emails-recommended).

### 3. Queue worker not processing

- Ensure `php scripts/queue-worker.php` is running.
- Check the log files in `storage/logs/` for any queue or email-related errors.

---

**Last Updated:** 2026-02-09  
**Version:** 1.0.0
