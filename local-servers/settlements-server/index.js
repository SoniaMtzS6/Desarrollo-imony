const express = require('express');
const cors = require('cors');
const axios = require('axios');

const app = express();
const PORT = process.env.PORT || 3003;

// Middleware
app.use(cors());
app.use(express.json());

// --- Lógica para Datos de Prueba ---
function generateMockData(type, count, accountId) {
    const mockData = [];
    const descriptions = {
        settlement: ['Liquidación diaria', 'Ajuste de saldo', 'Liquidación de comisiones'],
        transaction: ['Compra en Amazon', 'Suscripción a Netflix', 'Café en Starbucks', 'Pago de servicios']
    };
    const statuses = ['completed', 'pending', 'failed'];

    for (let i = 0; i < count; i++) {
        mockData.push({
            id: `${type.slice(0, 4)}-${Math.random().toString(36).substr(2, 9)}`,
            account_id: accountId || `acc-mock-${Math.random().toString(36).substr(2, 6)}`,
            amount: parseFloat((Math.random() * 2000 - 500).toFixed(2)),
            currency: 'MXN',
            status: statuses[Math.floor(Math.random() * statuses.length)],
            created_at: new Date(Date.now() - Math.random() * 30 * 24 * 60 * 60 * 1000).toISOString(),
            type: type,
            description: descriptions[type][Math.floor(Math.random() * descriptions[type].length)]
        });
    }
    return mockData;
}

// --- Configuración y Funciones de API ---
const POMELO_CONFIG = {
    baseUrl: process.env.POMELO_BASE_URL || 'https://api.pomelo.la',
    clientId: process.env.POMELO_CLIENT_ID || 'your-client-id',
    clientSecret: process.env.POMELO_CLIENT_SECRET || 'your-client-secret',
    username: process.env.POMELO_USERNAME || 'your-username',
    password: process.env.POMELO_PASSWORD || 'your-password'
};

let accessToken = null;
let tokenExpiry = 0;

async function getAccessToken() {
    if (accessToken && Date.now() < tokenExpiry) return accessToken;
    if (POMELO_CONFIG.clientId === 'your-client-id') return null;

    try {
        const response = await axios.post(`${POMELO_CONFIG.baseUrl}/oauth/token`,
            new URLSearchParams({
                grant_type: 'password',
                client_id: POMELO_CONFIG.clientId,
                client_secret: POMELO_CONFIG.clientSecret,
                username: POMELO_CONFIG.username,
                password: POMELO_CONFIG.password
            }).toString(),
            { headers: { 'Content-Type': 'application/x-www-form-urlencoded' } }
        );
        accessToken = response.data.access_token;
        tokenExpiry = Date.now() + (response.data.expires_in * 1000) - 60000;
        console.log('Token de acceso real obtenido.');
        return accessToken;
    } catch (error) {
        console.error('Error obteniendo token de acceso real:', error.message);
        return null;
    }
}

async function getSettlements(startDate, endDate, accountId) {
    const token = await getAccessToken();
    if (!token) {
        console.log('Usando datos de prueba para settlements.');
        return generateMockData('settlement', 15, accountId);
    }
    try {
        const params = new URLSearchParams({ start_date: startDate, end_date: endDate, account_id: accountId });
        const response = await axios.get(`${POMELO_CONFIG.baseUrl}/core/settlements/v1?${params}`, {
            headers: { 'Authorization': `Bearer ${token}` }
        });
        return response.data.data || [];
    } catch (error) {
        console.error('Error en API real de settlements, usando datos de prueba.');
        return generateMockData('settlement', 15, accountId);
    }
}

async function getTransactions(startDate, endDate, accountId) {
    const token = await getAccessToken();
    if (!token) {
        console.log('Usando datos de prueba para transacciones.');
        return generateMockData('transaction', 25, accountId);
    }
    try {
        const params = new URLSearchParams({ start_date: startDate, end_date: endDate, account_id: accountId });
        const response = await axios.get(`${POMELO_CONFIG.baseUrl}/core/transactions/v1?${params}`, {
            headers: { 'Authorization': `Bearer ${token}` }
        });
        return response.data.data || [];
    } catch (error) {
        console.error('Error en API real de transacciones, usando datos de prueba.');
        return generateMockData('transaction', 25, accountId);
    }
}

// --- Funciones de Lógica de Reporte ---
function calculateSummary(settlements, transactions) {
    const totalSettlements = settlements.reduce((sum, s) => sum + (s.amount || 0), 0);
    const totalTransactions = transactions.reduce((sum, t) => sum + (t.amount || 0), 0);
    return {
        total_settlements: totalSettlements,
        total_transactions: totalTransactions,
        total_movements: totalSettlements + totalTransactions,
        settlements_count: settlements.length,
        transactions_count: transactions.length,
        movements_count: settlements.length + transactions.length,
        difference: totalSettlements - totalTransactions
    };
}

function applyPagination(data, page, size) {
    const totalItems = data.length;
    return {
        data: data.slice((page - 1) * size, page * size),
        pagination: {
            page: page,
            size: size,
            total: totalItems,
            total_pages: Math.ceil(totalItems / size)
        }
    };
}

// --- Endpoints ---
app.get('/api/settlements/health', (req, res) => res.json({ status: 'OK' }));

app.get('/api/settlements/report', async (req, res) => {
    try {
        const { start_date, end_date, account_id, page = 1, size = 10 } = req.query;
        const settlements = await getSettlements(start_date, end_date, account_id);
        const transactions = await getTransactions(start_date, end_date, account_id);
        
        const combinedData = [...settlements, ...transactions].sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
        const paginated = applyPagination(combinedData, parseInt(page), parseInt(size));
        const summary = calculateSummary(settlements, transactions);
        
        res.json({
            success: true,
            message: 'Reporte generado exitosamente',
            ...paginated,
            summary
        });
    } catch (error) {
        console.error('Error fatal en /report:', error.message);
        res.status(500).json({ success: false, message: 'Error interno del servidor.' });
    }
});

// --- Iniciar Servidor ---
app.listen(PORT, () => {
    console.log(`🚀 Servidor de Settlements (con datos de prueba) ejecutándose en puerto ${PORT}`);
});

module.exports = app; 