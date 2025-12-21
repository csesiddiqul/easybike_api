<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>
</head>

<body>
    <div style="padding: 20px; background: #f3eeee; border-radius: 10px">
        <p>Hello,</p>

        <p>You are receiving this email because we received a password reset request for your account.</p>

        <p>Copy the OTP below to reset your password:</p>

        <div>
            <span style="padding: 5px 15px; border: 1px solid #3d3d3d; border-radius: 5px;">
                {{ $token }}
            </span>
        </div>

        <p>If you did not request a password reset, no further action is required.</p>

        <p>Thanks</p>
    </div>
</body>

</html>
