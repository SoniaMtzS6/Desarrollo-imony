const express = require('express');
const cors = require('cors');
const bodyParser = require('body-parser');

const app = express();
const port = 3000;

// Middleware
app.use(cors());
app.use(bodyParser.json());

// Mock data
const users = [
    { id: 1, nombre: 'Usuario Test 1', email: 'test1@example.com', idEmpresa: 1 },
    { id: 2, nombre: 'Usuario Test 2', email: 'test2@example.com', idEmpresa: 1 }
];

const cards = [
    { id: 1, number: '**** **** **** 1234', user_id: 1, status: 'active' },
    { id: 2, number: '**** **** **** 5678', user_id: 2, status: 'active' }
];

// User routes
app.get('/api/user/v1', (req, res) => {
    const { size = 10, page = 1 } = req.query;
    const idEmpresa = req.query['filter[idEmpresa]'];
    
    let filteredUsers = users;
    if (idEmpresa) {
        filteredUsers = users.filter(user => user.idEmpresa === parseInt(idEmpresa));
    }

    res.json({
        data: filteredUsers,
        pagination: {
            total: filteredUsers.length,
            page: parseInt(page),
            size: parseInt(size)
        }
    });
});

// Card routes
app.get('/api/card/v1', (req, res) => {
    const userId = req.query['filter[user_id]'];
    const pageNumber = parseInt(req.query['page[number]'] || 1);
    const pageSize = parseInt(req.query['page[size]'] || 10);

    // Mock de tarjetas con campos esperados
    const allCards = [
        {
            id: 1,
            last_four: '1234',
            status: 'active',
            start_date: '2024-06-16',
            provider: 'Visa',
            affinity_group_name: 'Grupo A',
            user_id: 1
        },
        {
            id: 2,
            last_four: '5678',
            status: 'inactive',
            start_date: '2024-06-10',
            provider: 'Mastercard',
            affinity_group_name: 'Grupo B',
            user_id: 2
        }
    ];

    let filteredCards = allCards;
    if (userId) {
        filteredCards = allCards.filter(card => card.user_id == userId);
    }

    // Paginación
    const start = (pageNumber - 1) * pageSize;
    const end = start + pageSize;
    const pagedCards = filteredCards.slice(start, end);

    res.json({
        data: pagedCards,
        pagination: {
            total: filteredCards.length,
            page: pageNumber,
            size: pageSize
        }
    });
});

// Endpoint mock para actividades
app.post('/api/activities/findByAcc', (req, res) => {
    // Puedes usar req.body.acc si quieres filtrar por cuenta
    res.json({
        Data: [
            {
                totalAmount: 500,
                createdAt: "2024-06-17",
                origin: "Tienda",
                processType: "Compra",
                merchantName: "OXXO"
            },
            {
                totalAmount: 1200,
                createdAt: "2024-06-15",
                origin: "Supermercado",
                processType: "Compra",
                merchantName: "Walmart"
            }
        ]
    });
});

// Start server
app.listen(port, () => {
    console.log(`API server running at http://localhost:${port}`);
}); 