const express = require('express');
const cors = require('cors');
const bodyParser = require('body-parser');

const app = express();
const port = 3002;

// Middleware
app.use(cors());
app.use(bodyParser.json());

// Mock data
const cards = {
    'card_123': {
        id: 'card_123',
        pan: '**** **** **** 1234',
        cvv: '123',
        expiration_date: '12/25',
        pin: '1234',
        status: 'active'
    }
};

const accounts = {
    'acc_123': {
        id: 'acc_123',
        balance: 1000.00,
        currency: 'USD',
        status: 'active'
    }
};

// Pomelo routes
app.post('/pomelo/token', (req, res) => {
    res.json({
        access_token: 'mock_token_' + Date.now(),
        token_type: 'Bearer',
        expires_in: 3600
    });
});

// Endpoint GET adicional para pruebas fáciles desde el navegador
app.get('/pomelo/token', (req, res) => {
    res.json({
        access_token: 'mock_token_' + Date.now(),
        token_type: 'Bearer',
        expires_in: 3600,
        note: 'This is a test endpoint. In production, use POST method instead.'
    });
});

app.get('/cards/v1/:cardId', (req, res) => {
    const card = cards[req.params.cardId] || cards['card_123'];
    res.json(card);
});

app.get('/core/accounts/v1/:accountId', (req, res) => {
    const account = accounts[req.params.accountId] || accounts['acc_123'];
    res.json(account);
});

app.post('/cards/associations/v1', (req, res) => {
    res.json({
        success: true,
        card_id: 'card_' + Date.now(),
        status: 'associated'
    });
});

app.post('/core/transactions/v1', (req, res) => {
    res.json({
        transaction_id: 'tx_' + Date.now(),
        status: 'completed',
        amount: req.body.amount,
        currency: req.body.currency
    });
});

// Start server
app.listen(port, () => {
    console.log(`Pomelo mock server running at http://localhost:${port}`);
}); 