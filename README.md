# Warwa International School Portal

A standalone PHP and MySQL school portal. Students create accounts, browse courses and unlock enrollment after a verified KSh 5 payment. Administrators manage students, courses and payment records.

## Project structure

```text
Warwa-International-School/
├── admin/                 Administrator pages
├── assets/css, assets/js  Theme and password visibility controls
├── callbacks/             Paystack return verification and M-Pesa callback
├── config/                Environment and database setup
├── includes/              Shared layout, notices and payment logic
├── student/               Student dashboards and learning history
├── database.sql           MySQL schema and starter courses
├── .env.example           Safe configuration template
└── .gitignore
```

## Local setup with XAMPP

1. Place this folder at `C:\xampp\htdocs\Warwa-International-School`.
2. Start Apache and MySQL in the XAMPP Control Panel.
3. Import `database.sql` using phpMyAdmin or MySQL. The script creates the `warwa_school` database.
4. Copy `.env.example` to `.env`. Set the database values and `APP_URL=http://localhost/Warwa-International-School`.
5. Install the optional SMTP mail package from this folder with `composer install`. Configure `SMTP_HOST`, `SMTP_PORT`, `SMTP_USERNAME`, `SMTP_PASSWORD`, `SMTP_ENCRYPTION`, `MAIL_FROM`, and `SCHOOL_EMAIL` in `.env`. Without SMTP, PHP's configured `mail()` transport is used.
6. Open `http://localhost/Warwa-International-School/setup-admin.php` and create the first administrator. This page locks as soon as an administrator exists.
7. Register a student at `/register.php`, then sign in at `/login.php`.

## Payments

The fixed school enrollment fee is KSh 5. M-Pesa Daraja receives integer amount `5`. Paystack expects minor units, so the server sends `500` with currency `KES`. The KSh 5 fee satisfies Paystack's published KES minimum. Both payment methods are available to students. The backend creates a pending payment first and only creates course enrollment after provider verification. Repeated callback processing is idempotent.

### Paystack sandbox

Add a Paystack **test** secret key to `PAYSTACK_SECRET_KEY`. The browser is redirected to Paystack Checkout; after return, PHP calls Paystack's transaction verification endpoint and checks the reference, success status, KES currency and exact amount before granting enrollment. Never use a secret key in browser JavaScript.

### M-Pesa Daraja sandbox and ngrok

Set the Daraja sandbox consumer key, consumer secret, shortcode and passkey in `.env`. Start ngrok for your local Apache port (commonly `ngrok http 80`), then copy its HTTPS URL into `MPESA_CALLBACK_URL`, followed by `/Warwa-International-School/callbacks/mpesa.php`. Keep that URL current while ngrok is running. The student enters a Kenyan mobile number and approves the STK prompt. Only a successful callback with the expected amount and phone activates enrollment. Sandbox availability and test numbers are controlled by Safaricom Developer Portal.

Switch `MPESA_ENV=production` only after configuring production Daraja credentials and a stable HTTPS callback URL. Keep both providers in sandbox while developing.

## Email, notices and OTP

Payment/enrollment results are emailed to the student and `SCHOOL_EMAIL`. Password reset codes are sent only to the account email, expire after ten minutes, and are stored hashed. This keeps account recovery codes private. SMTP delivery needs a working mail account and valid sender domain. M-Pesa sends its STK payment prompt to the student's phone; SMS delivery beyond the provider prompt needs a separate SMS service.

## Security notes

- Passwords are hashed with `password_hash()` and checked with `password_verify()`.
- Sessions use HTTP-only and same-site cookies; forms use CSRF tokens and role checks.
- Database operations use PDO prepared statements.
- Payment credentials stay in `.env`, which is ignored by Git and denied by the included Apache rules.
- Never commit `.env`, test or live credentials, real student details, or payment callback secrets.
- Configure HTTPS, database backups, outbound SMTP and production access controls before using real student information or live payment credentials.
- Keep `database.sql` and `.env.example` in the project source; Apache rules prevent serving them as web pages.
