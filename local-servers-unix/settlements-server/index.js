const express = require('express');
const cors = require('cors');
const axios = require('axios');

const app = express();
const PORT = process.env.PORT || 3003;

// Middleware
app.use(cors());
app.use(express.json());

// Configuración de Pomelo
const POMELO_CONFIG = {
    baseUrl: process.env.POMELO_BASE_URL || 'https://api.pomelo.la',
    clientId: process.env.POMELO_CLIENT_ID || 'your-client-id',
    clientSecret: process.env.POMELO_CLIENT_SECRET || 'your-client-secret',
    username: process.env.POMELO_USERNAME || 'your-username',
    password: process.env.POMELO_PASSWORD || 'your-password'
};

let accessToken = null;
let tokenExpiry = 0;

// Función para obtener token de acceso
async function getAccessToken() {
    const currentTime = Date.now();
    
    if (accessToken && currentTime < tokenExpiry) {
        return accessToken;
    }

    try {
        const response = await axios.post(`${POMELO_CONFIG.baseUrl}/oauth/token`, 
            `grant_type=password&client_id=${POMELO_CONFIG.clientId}&client_secret=${POMELO_CONFIG.clientSecret}&username=${POMELO_CONFIG.username}&password=${POMELO_CONFIG.password}`,
            {
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            }
        );

        accessToken = response.data.access_token;
        tokenExpiry = currentTime + (response.data.expires_in * 1000) - 60000; // 1 minuto antes de expirar
        
        console.log('Token de acceso obtenido exitosamente');
        return accessToken;
    } catch (error) {
        console.error('Error obteniendo token de acceso:', error.message);
        return null;
    }
}

// Función para obtener settlements
async function getSettlements(startDate, endDate, accountId) {
    try {
        const token = await getAccessToken();
        if (!token) {
            throw new Error('No se pudo obtener el token de acceso');
        }

        const params = new URLSearchParams();
        if (startDate) params.append('start_date', startDate);
        if (endDate) params.append('end_date', endDate);
        if (accountId) params.append('account_id', accountId);

        const response = await axios.get(`${POMELO_CONFIG.baseUrl}/core/settlements/v1?${params}`, {
            headers: {
                'Authorization': `Bearer ${token}`
            }
        });

        return response.data.data || [];
    } catch (error) {
        console.error('Error obteniendo settlements:', error.message);
        return [];
    }
}

// Función para obtener transacciones
async function getTransactions(startDate, endDate, accountId) {
    try {
        const token = await getAccessToken();
        if (!token) {
            throw new Error('No se pudo obtener el token de acceso');
        }

        const params = new URLSearchParams();
        if (startDate) params.append('start_date', startDate);
        if (endDate) params.append('end_date', endDate);
        if (accountId) params.append('account_id', accountId);

        const response = await axios.get(`${POMELO_CONFIG.baseUrl}/core/transactions/v1?${params}`, {
            headers: {
                'Authorization': `Bearer ${token}`
            }
        });

        return response.data.data || [];
    } catch (error) {
        console.error('Error obteniendo transacciones:', error.message);
        return [];
    }
}

// Función para calcular resumen
function calculateSummary(settlements, transactions) {
    const totalSettlements = settlements.reduce((sum, s) => sum + (s.amount || 0), 0);
    const totalTransactions = transactions.reduce((sum, t) => sum + (t.amount || 0), 0);
    const totalMovements = totalSettlements + totalTransactions;
    const difference = totalSettlements - totalTransactions;

    return {
        total_settlements: totalSettlements,
        total_transactions: totalTransactions,
        total_movements: totalMovements,
        settlements_count: settlements.length,
        transactions_count: transactions.length,
        movements_count: settlements.length + transactions.length,
        difference: difference
    };
}

// Función para aplicar paginación
function applyPagination(data, page, size) {
    const totalItems = data.length;
    const totalPages = Math.ceil(totalItems / size);
    const startIndex = (page - 1) * size;
    const endIndex = Math.min(startIndex + size, totalItems);
    
    return {
        data: data.slice(startIndex, endIndex),
        pagination: {
            page: page,
            size: size,
            total: totalItems,
            total_pages: totalPages
        }
    };
}

// Endpoints

// Health check
app.get('/api/settlements/health', (req, res) => {
    res.json({
        status: 'OK',
        service: 'Settlements API (Local)',
        timestamp: Date.now()
    });
});

// Reporte combinado
app.get('/api/settlements/report', async (req, res) => {
    try {
        const { start_date, end_date, account_id, page = 1, size = 10 } = req.query;
        
        console.log('Generando reporte de settlements - startDate:', start_date, 'endDate:', end_date, 'accountId:', account_id);
        
        const settlements = await getSettlements(start_date, end_date, account_id);
        const transactions = await getTransactions(start_date, end_date, account_id);
        
        // Combinar y ordenar datos
        const combinedData = [...settlements, ...transactions].sort((a, b) => {
            return new Date(b.created_at) - new Date(a.created_at);
        });
        
        // Aplicar paginación
        const { data, pagination } = applyPagination(combinedData, parseInt(page), parseInt(size));
        
        // Calcular resumen
        const summary = calculateSummary(settlements, transactions);
        
        res.json({
            success: true,
            message: 'Reporte generado exitosamente',
            data: data,
            summary: summary,
            pagination: pagination
        });
        
    } catch (error) {
        console.error('Error en endpoint /report:', error.message);
        res.status(500).json({
            success: false,
            message: 'Error interno del servidor: ' + error.message
        });
    }
});

// Settlements específicos
app.get('/api/settlements/settlements', async (req, res) => {
    try {
        const { start_date, end_date, account_id, page = 1, size = 10 } = req.query;
        
        console.log('Obteniendo settlements - startDate:', start_date, 'endDate:', end_date, 'accountId:', account_id);
        
        const settlements = await getSettlements(start_date, end_date, account_id);
        
        // Aplicar paginación
        const { data, pagination } = applyPagination(settlements, parseInt(page), parseInt(size));
        
        // Calcular resumen solo para settlements
        const summary = calculateSummary(settlements, []);
        
        res.json({
            success: true,
            message: 'Settlements obtenidos exitosamente',
            data: data,
            summary: summary,
            pagination: pagination
        });
        
    } catch (error) {
        console.error('Error en endpoint /settlements:', error.message);
        res.status(500).json({
            success: false,
            message: 'Error interno del servidor: ' + error.message
        });
    }
});

// Transacciones específicas
app.get('/api/settlements/transactions', async (req, res) => {
    try {
        const { start_date, end_date, account_id, page = 1, size = 10 } = req.query;
        
        console.log('Obteniendo transacciones - startDate:', start_date, 'endDate:', end_date, 'accountId:', account_id);
        
        const transactions = await getTransactions(start_date, end_date, account_id);
        
        // Aplicar paginación
        const { data, pagination } = applyPagination(transactions, parseInt(page), parseInt(size));
        
        // Calcular resumen solo para transacciones
        const summary = calculateSummary([], transactions);
        
        res.json({
            success: true,
            message: 'Transacciones obtenidas exitosamente',
            data: data,
            summary: summary,
            pagination: pagination
        });
        
    } catch (error) {
        console.error('Error en endpoint /transactions:', error.message);
        res.status(500).json({
            success: false,
            message: 'Error interno del servidor: ' + error.message
        });
    }
});

// Iniciar servidor
app.listen(PORT, () => {
    console.log(`🚀 Servidor de Settlements ejecutándose en puerto ${PORT}`);
    console.log(`📊 Endpoints disponibles:`);
    console.log(`   - GET /api/settlements/health`);
    console.log(`   - GET /api/settlements/report`);
    console.log(`   - GET /api/settlements/settlements`);
    console.log(`   - GET /api/settlements/transactions`);
    console.log(`🔧 Configuración Pomelo: ${POMELO_CONFIG.baseUrl}`);
});

module.exports = app; 