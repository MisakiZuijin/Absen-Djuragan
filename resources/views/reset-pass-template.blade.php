<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');

        body {
            font-family: 'Poppins', Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
        }

        table {
            width: 100%;
            height: auto;
            text-align: center;
            border-collapse: collapse;
            padding: 0;
            margin: 0;
        }

        .email-container {
            background-color: #ffffff;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            width: 100%;
            max-width: 440px;
            border: 1px solid #eaeaea;
            margin: auto;
            text-align: center;
        }

        h2 {
            color: #1f2937;
            font-size: 22px;
            margin-bottom: 12px;
        }

        p {
            margin: 16px 0;
            font-size: 14px;
            color: #4b5563;
            line-height: 1.6;
        }

        a.reset-button {
            display: inline-block;
            padding: 12px 28px;
            background-color: #ef4444;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
        }

        a.reset-button:hover {
            background-color: #dc2626;
        }

        .footer {
            font-size: 12px;
            color: #9ca3af;
            margin-top: 20px;
            line-height: 1.4;
        }

        .divider {
            height: 3px;
            background: linear-gradient(to right, #ef4444, #1f2937);
            margin-top: 24px;
            border-radius: 2px;
        }
    </style>
</head>

<body>
    <table role="presentation" width="100%">
        <tr>
            <td align="center" style="padding: 40px 10px;">
                <div class="email-container">
                    <h2>Permintaan Reset Password</h2>
                    <p>Halo, kami telah menerima permintaan untuk mengatur ulang kata sandi akun Absensi Anda. Silakan klik tombol di bawah ini untuk membuat password baru:</p>
                    <div style="margin: 25px 0;">
                        <a href="{{ $details['url'] }}" class="reset-button">Reset Password Saya</a>
                    </div>
                    <p style="font-size: 12px; color: #6b7280;">Link ini bersifat rahasia dan hanya berlaku selama <strong>1 jam</strong>.</p>
                    <div class="footer">Jika Anda tidak pernah meminta perubahan kata sandi, abaikan email ini dan akun Anda tetap aman.</div>
                    <div class="divider"></div>
                </div>
            </td>
        </tr>
    </table>
</body>

</html>
