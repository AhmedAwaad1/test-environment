<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Email Verification Code</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f9f9f9; padding: 20px; color: #333;">
    <div style="max-width: 600px; margin: auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.05);">
        <h2 style="color: #2c3e50;">Thanks for signing up with <strong>NAJD</strong>!</h2>

        <p>To verify your email address, please enter the code below on the website:</p>

        <h1 style="color: #3490dc; font-size: 32px; text-align: center; letter-spacing: 4px;">
            🔐 {{ $verificationCode }}
        </h1>

        <p>This code will expire in <strong>10 minutes</strong>, so be sure to use it soon.</p>

        <p>If you didn’t request this, you can safely ignore this email.</p>

        <p style="margin-top: 40px;">Thanks for joining us!<br>
        — <strong>The NAJD Team</strong></p>
    </div>
</body>
</html>
