// bot-server.js

const path = require('path');
const { Client, LocalAuth } = require('whatsapp-web.js');
const express = require('express');
const qrcode = require('qrcode-terminal');

const app = express();
app.use(express.json()); // Middleware untuk membaca body JSON dari request

console.log('Initializing WhatsApp client...');

const client = new Client({
    authStrategy: new LocalAuth({
        dataPath: path.join(__dirname, '.wwebjs_auth') 
    }),
    webVersionCache: {
        path: path.join(__dirname, '.wwebjs_cache')
    },
    puppeteer: {
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    }
});

client.on('qr', (qr) => {
    console.log('QR RECEIVED', qr);
    qrcode.generate(qr, { small: true }); 
});

client.on('ready', () => {
    console.log('✅ WhatsApp client is ready!');
});

client.on('disconnected', (reason) => {
    console.log('❌ Client was logged out', reason);
});

client.on('auth_failure', msg => {
    console.error('❌ AUTHENTICATION FAILURE', msg);
});

client.initialize().catch(err => {
    console.error('❌ Client initialization error:', err);
});

// Endpoint yang akan dipanggil oleh Laravel untuk mengirim pesan
app.post('/send-message', async (req, res) => {
    let { number, message } = req.body;

    if (!number || !message) {
        return res.status(400).json({ success: false, error: 'Nomor dan pesan wajib diisi.' });
    }

    try {
        // --- PERBAIKAN DAN PENAMBAHAN LOGIKA DI SINI ---

        // 1. Bersihkan nomor telepon dari karakter non-numerik
        number = number.replace(/\D/g, '');

        // 2. Format nomor WhatsApp menjadi ID chat
        const chatId = `${number}@c.us`;

        // 3. (KUNCI UTAMA) Periksa apakah nomor tersebut terdaftar di WhatsApp
        const isRegistered = await client.isRegisteredUser(chatId);

        if (!isRegistered) {
            // Jika nomor tidak terdaftar, jangan coba kirim pesan.
            const errorMessage = `❌ Nomor ${number} tidak terdaftar di WhatsApp. Pesan dibatalkan.`;
            console.error(errorMessage);
            return res.status(404).json({ success: false, error: errorMessage });
        }

        // 4. Jika terdaftar, baru kirim pesan
        await client.sendMessage(chatId, message);
        console.log(`🚀 Message sent to ${number}`);
        res.status(200).json({ success: true, message: 'Pesan berhasil dikirim.' });

    } catch (error) {
        // Tangkap error lain yang mungkin terjadi
        console.error(`❌ Failed to send message to ${number}:`, error);
        res.status(500).json({ success: false, error: 'Gagal mengirim pesan. Lihat log server untuk detail.' });
    }
});

const PORT = 3000;
app.listen(PORT, '0.0.0.0', () => {
    console.log(`🟢 WhatsApp API server is running. Listening on all interfaces at port ${PORT}`);
});