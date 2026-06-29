<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Ashvalian OTP</title>
</head>
<body style="font-family: Arial, sans-serif; color:#111; line-height:1.5;">
    <h1>Ashvalian Registration OTP</h1>
    <p>Hello {{ $pendingRegistration->name }},</p>
    <p>Your verification code is:</p>
    <h2 style="letter-spacing:6px;">{{ $pendingRegistration->otp_code }}</h2>
    <p>This code expires at {{ $pendingRegistration->expires_at->format('h:i A') }}.</p>
    <p>If you did not request this account, you can ignore this email.</p>
</body>
</html>
