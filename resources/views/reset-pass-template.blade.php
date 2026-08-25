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
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            width: 360px;
            border: 1px solid #f0f0f0;
            margin: auto;
            text-align: center;
        }

        .logo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-image: url('http://localhost:8000/img/logo.png');
            background-repeat: no-repeat;
            background-position: center;
            background-size: cover;
            margin: 0 auto 20px;
        }

        h2 {
            color: #222;
            font-size: 24px;
            margin-bottom: 15px;
        }

        p {
            margin: 20px 0;
            font-size: 14px;
            color: #555;
            line-height: 1.5;
        }

        a.reset-button {
            display: inline-block;
            padding: 12px 25px;
            background-color: #ff5a5f;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }

        a.reset-button:hover {
            background-color: #e14b4e;
        }

        .footer {
            font-size: 12px;
            color: #777;
            margin-top: 20px;
        }

        .divider {
            height: 5px;
            background: linear-gradient(to right, #ff5a5f, #222);
            margin-top: 30px;
        }
    </style>
</head>

<body>
    <table>
        <tr>
            <td>
                <div class="email-container">
                    <div class="logo"></div>

                    <h2>Permintaan Ganti Password</h2>
                    <p>Kami telah menerima permintaan untuk mengatur ulang kata sandi Anda. Klik tombol di bawah ini
                        untuk
                        melanjutkan proses.</p>
                    <a href="{{ $details['url'] }}" class="reset-button">Reset Password</a>
                    <div class="footer">Jika Anda tidak melakukan permintaan ini, abaikan email ini.</div>
                    <div class="divider"></div>
                </div>
            </td>
        </tr>
    </table>
</body>

</html>
