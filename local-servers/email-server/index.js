const express = require('express');
const cors = require('cors');
const bodyParser = require('body-parser');
const path = require('path');

const app = express();
const port = 3001;

// "Database" en memoria para los emails
let sentEmails = [];

// Middleware
app.use(cors());
app.use(bodyParser.json());
app.use(express.static(__dirname)); // Servir archivos estáticos desde el directorio actual

// Ruta para la página de prueba
app.get('/test', (req, res) => {
    res.sendFile(path.join(__dirname, 'test-email.html'));
});

// Ruta para obtener el historial de emails
app.get('/emails/history', (req, res) => {
    // Agregar headers para instruir al navegador que no guarde la respuesta en caché
    res.setHeader('Cache-Control', 'no-cache, no-store, must-revalidate');
    res.setHeader('Pragma', 'no-cache');
    res.setHeader('Expires', '0');
    
    res.json(sentEmails.slice().reverse()); // Enviar los más recientes primero
});

// Email routes
app.post('/email', (req, res) => {
    const { to, subject, body } = req.body;
    
    console.log('Email simulado enviado:');
    console.log('Para:', to);
    console.log('Asunto:', subject);
    console.log('Contenido:', body);

    // Guardar en el historial
    const emailData = { to, subject, body, receivedAt: new Date() };
    sentEmails.push(emailData);
    
    // Código JavaScript para mostrar alerta en el navegador
    const alertScript = `
        <script>
            alert('Email simulado enviado:\\n\\nPara: ${to}\\nAsunto: ${subject}\\nContenido: ${body}');
        </script>
    `;
    
    res.json({
        success: true,
        message: 'Email enviado (simulado)',
        messageId: `mock_${Date.now()}`,
        alertScript: alertScript
    });
});

app.post('/email/sendmailmasivo', (req, res) => {
    const { recipients, subject, content } = req.body;
    
    console.log('Email masivo simulado enviado:');
    console.log('Para:', recipients);
    console.log('Asunto:', subject);
    console.log('Contenido:', content);
    
    // Guardar en el historial
    recipients.forEach(recipient => {
        sentEmails.push({ to: recipient, subject, body: content, receivedAt: new Date() });
    });

    // Código JavaScript para mostrar alerta en el navegador
    const alertScript = `
        <script>
            alert('Emails masivos simulados enviados:\\n\\nPara: ${recipients.join(', ')}\\nAsunto: ${subject}\\nContenido: ${content}');
        </script>
    `;
    
    res.json({
        success: true,
        message: 'Emails masivos enviados (simulado)',
        messageIds: recipients.map(r => `mock_${Date.now()}_${r}`),
        alertScript: alertScript
    });
});

// Start server
app.listen(port, () => {
    console.log(`Email server running at http://localhost:${port}`);
    console.log(`Test page available at http://localhost:${port}/test`);
}); 