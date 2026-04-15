<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Registration OTP</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f8fafc; padding:30px;">
    <div style="max-width:600px; margin:0 auto; background:#ffffff; border-radius:12px; padding:30px; border:1px solid #e5e7eb;">
        <h2 style="margin-top:0; color:#111827;">MyVictory Billing Email Verification</h2>
        <p style="color:#374151; font-size:15px;">
            Aapka OTP ye hai:
        </p>

        <div style="font-size:32px; font-weight:700; letter-spacing:6px; color:#10b981; margin:20px 0;">
            {{ $otp }}
        </div>

        <p style="color:#6b7280; font-size:14px;">
            Ye OTP 10 minute ke liye valid hai.
        </p>

        <p style="color:#6b7280; font-size:14px;">
            Agar aapne request nahi ki thi, to is mail ko ignore kar dijiye.
        </p>
    </div>
</body>
</html>