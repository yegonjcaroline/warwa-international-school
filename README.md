# Warwa International School Portal

A standalone PHP and MySQL school management portal. Students can register, browse courses, pay a KSh 5 enrollment fee through Paystack or M-Pesa, and access courses after server-side payment verification. Administrators manage students, courses, enrollments, and payments.

Public repository: https://github.com/yegonjcaroline/warwa-international-school

## Features

- Student registration, login, password reset, and role-based sessions
- Course catalog, paid enrollment, and student learning area
- Administrator dashboard for students, courses, payments, and enrollments
- Paystack Checkout and M-Pesa Daraja STK Push with backend verification
- Gmail SMTP notifications and password-reset OTP by email
- Africa's Talking SMS integration (Sandbox or Live configuration)
- Responsive interface using HTML, Tailwind CSS CDN, and custom CSS

## Requirements

- PHP 8.1 or newer
- MySQL 8+ or MariaDB 10.4+
- XAMPP or another Apache/PHP/MySQL environment
- Composer
- PHP extensions: PDO MySQL, cURL, OpenSSL, and mbstring
- Internet access for Composer and payment/email/SMS provider APIs

## Project structure

```text
Warwa-International-School/
|-- admin/                 Administrator pages
|-- assets/
|   |-- css/style.css      Theme and responsive layout
|   |-- images/            School imagery
|   `-- js/app.js          Password visibility controls
|-- callbacks/             Paystack and M-Pesa payment callbacks
|-- config/                Environment and database setup
|-- includes/              Layout, email, SMS, and payment helpers
|-- student/               Student dashboard, learning, and payments
|-- storage/               Runtime storage; contents are ignored by Git
|-- .env.example           Safe configuration template
|-- composer.json          PHP dependencies
`-- database.sql           MySQL schema and starter courses
```

## Local setup with XAMPP

1. Copy or clone this folder to `C:\xampp\htdocs\Warwa-International-School`.
2. Start **Apache** and **MySQL** in the XAMPP Control Panel.
3. Open Command Prompt in the project folder and install PHP dependencies:

   ```cmd
   cd /d C:\xampp\htdocs\Warwa-International-School
   composer install
   ```

4. Create the local configuration file:

   ```cmd
   copy .env.example .env
   ```

5. Import `database.sql` in phpMyAdmin. It creates the `warwa_school` database and inserts the starter courses. If your MySQL root account has a password, enter it in `.env`.
6. Edit `.env` and set at least:
   - `APP_URL=http://localhost/Warwa-International-School`
   - `DB_HOST=127.0.0.1`, `DB_PORT=3306`, `DB_NAME=warwa_school`
   - `DB_USER` and `DB_PASS` for your local MySQL account
7. Open [http://localhost/Warwa-International-School](http://localhost/Warwa-International-School).

### Create the first administrator

The web-based first-admin setup page is intentionally not included in the public repository. For a fresh database, first register an account at `/register.php`, then promote that account using phpMyAdmin's SQL tab. Replace the email with the administrator account you just registered:

```sql
UPDATE users
SET role = 'admin'
WHERE email = 'admin@example.com' AND role = 'student';
```

Sign out and sign in again. The account will open the administrator dashboard. Do this only on a database you control.

## Payment setup

The enrollment fee is KSh 5. A successful, verified payment is required before the enrollment is activated.

### Paystack Sandbox

1. Use Paystack **test** keys in `.env`:
   - `PAYSTACK_SECRET_KEY`
   - `PAYSTACK_PUBLIC_KEY`
2. The backend initializes Checkout for KES 500 minor units (KSh 5), then verifies the returned transaction reference, status, currency, and amount with Paystack before activating enrollment.
3. Use Paystack test checkout details while in test mode. Never place the secret key in browser JavaScript or commit it to Git.

### M-Pesa Daraja Sandbox

1. Set `MPESA_ENV=sandbox` and enter the Daraja **sandbox** consumer key and secret.
2. Keep the sandbox shortcode, passkey, and transaction type from the Daraja sandbox configuration.
3. For a private local PHP server, open a Command Prompt in the project folder and run:

   ```cmd
   C:\xampp\php\php.exe -S 127.0.0.1:8001
   ```

4. In a second Command Prompt, run `ngrok http 8001`. Keep both windows open.
5. Set `APP_URL` to the current HTTPS ngrok address. Set `MPESA_CALLBACK_URL` to that address plus `/callbacks/mpesa.php`. For example:

   ```env
   APP_URL=https://your-current-ngrok-host.ngrok-free.dev
   MPESA_CALLBACK_URL=https://your-current-ngrok-host.ngrok-free.dev/callbacks/mpesa.php
   ```

6. Restart the PHP server after changing `.env`. Use Safaricom's sandbox test number. The callback must be reachable over HTTPS. Update the callback whenever the temporary ngrok address changes.

Do not use live credentials or a real customer payment while testing.

## Email and password reset OTP

The project uses PHPMailer and Gmail SMTP. For Gmail, enable 2-Step Verification and create an App Password. Configure:

- `SMTP_HOST=smtp.gmail.com`
- `SMTP_PORT=587`
- `SMTP_ENCRYPTION=tls`
- `SMTP_USERNAME` and `MAIL_FROM` as the sending Gmail address
- `SMTP_PASSWORD` as the App Password, entered without spaces
- `SCHOOL_EMAIL` as the administrator notification inbox

Account and payment notices go to the student and administrator when both addresses are configured. Password-reset OTPs go only to the student's registered email and phone; the administrator receives a reset-request notice without the code. OTPs expire after ten minutes and are stored hashed.

## SMS setup

The project sends SMS through the Africa's Talking API.

### Sandbox

- Set `AT_ENV=sandbox`
- Set `AT_USERNAME=sandbox`
- Put the API key generated from the Africa's Talking **Sandbox App** in `AT_API_KEY`
- Set `SMS_ADMIN_PHONE` to the administrator's Kenyan mobile number in international format, for example `+2547XXXXXXXX`
- Ensure each student's account has a phone number

Sandbox SMS is simulated. Messages appear in Africa's Talking's simulator and are not delivered to real handsets. The simulator must be reachable for delivery simulation.

### Live SMS

Live SMS requires separate Live credentials, a configured sender ID or shortcode, account activation, and any required airtime/balance. Live messages may incur charges. Change to Live only after obtaining the provider's approval and replacing all Sandbox credentials.

## Security and deployment notes

- `.env` is ignored by Git. Keep credentials only in that file or a secret manager.
- `.env.example` contains placeholders, not working credentials.
- Passwords use `password_hash()` and `password_verify()`.
- Database access uses PDO prepared statements; forms use CSRF tokens and role checks.
- Payment callbacks are verified server-side and duplicate payment processing is guarded.
- Configure HTTPS, secure database credentials, backups, sender authentication, and access controls before using real student data or live payment/SMS services.
