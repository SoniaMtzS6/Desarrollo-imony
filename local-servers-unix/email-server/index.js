const express = require('express');
const cors = require('cors');
const bodyParser = require('body-parser');

const app = express();
const port = 3001;

// Middleware
app.use(cors());
app.use(bodyParser.json());

// Email routes
app.post('/email', (req, res) => {
    const { to, subject, body } = req.body;
    
    console.log('Email simulado enviado:');
    console.log('Para:', to);
    console.log('Asunto:', subject);
    console.log('Contenido:', body);
    
    res.json({
        success: true,
        message: 'Email enviado (simulado)',
        messageId: `mock_${Date.now()}`
    });
});

app.post('/email/sendmailmasivo', (req, res) => {
    const { recipients, subject, content } = req.body;
    
    console.log('Email masivo simulado enviado:');
    console.log('Para:', recipients);
    console.log('Asunto:', subject);
    console.log('Contenido:', content);
    
    res.json({
        success: true,
        message: 'Emails masivos enviados (simulado)',
        messageIds: recipients.map(r => `mock_${Date.now()}_${r}`)
    });
});

// Start server
app.listen(port, () => {
    console.log(`Email server running at http://localhost:${port}`);
}); 