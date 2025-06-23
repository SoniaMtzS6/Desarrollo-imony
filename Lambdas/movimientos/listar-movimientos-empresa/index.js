const AWS = require('aws-sdk');
const dynamoDB = new AWS.DynamoDB.DocumentClient();

exports.handler = async (event) => {
    try {
        const { idEmpresa } = event.pathParameters || {};
        const { page = 1, size = 10 } = event.queryStringParameters || {};
        const limit = parseInt(size);
        const offset = (parseInt(page) - 1) * limit;

        if (!idEmpresa) {
            return {
                statusCode: 400,
                headers: {
                    'Access-Control-Allow-Origin': '*',
                    'Access-Control-Allow-Credentials': true,
                },
                body: JSON.stringify({
                    error: 'ID de empresa requerido'
                })
            };
        }

        // Obtener movimientos de la empresa con paginación
        const params = {
            TableName: 'movimientos_saldo',
            IndexName: 'idEmpresa-index',
            KeyConditionExpression: 'idEmpresa = :idEmpresa',
            ExpressionAttributeValues: {
                ':idEmpresa': idEmpresa
            },
            Limit: limit,
            ScanIndexForward: false, // Orden descendente
            ProjectionExpression: 'id, idEmpresa, monto, tipo, idUsuario, comentario, fecha'
        };

        if (offset > 0) {
            params.ExclusiveStartKey = { id: offset.toString() };
        }

        const result = await dynamoDB.query(params).promise();

        // Obtener total de registros para esta empresa
        const countParams = {
            TableName: 'movimientos_saldo',
            IndexName: 'idEmpresa-index',
            KeyConditionExpression: 'idEmpresa = :idEmpresa',
            ExpressionAttributeValues: {
                ':idEmpresa': idEmpresa
            },
            Select: 'COUNT'
        };
        const countResult = await dynamoDB.query(countParams).promise();
        const total = countResult.Count;

        // Obtener información de la empresa
        const empresaParams = {
            TableName: 'empresas',
            Key: { idEmpresa }
        };
        const empresaResult = await dynamoDB.get(empresaParams).promise();

        if (!empresaResult.Item) {
            return {
                statusCode: 404,
                headers: {
                    'Access-Control-Allow-Origin': '*',
                    'Access-Control-Allow-Credentials': true,
                },
                body: JSON.stringify({
                    error: 'Empresa no encontrada'
                })
            };
        }

        return {
            statusCode: 200,
            headers: {
                'Access-Control-Allow-Origin': '*',
                'Access-Control-Allow-Credentials': true,
            },
            body: JSON.stringify({
                data: result.Items,
                empresa: {
                    idEmpresa: empresaResult.Item.idEmpresa,
                    nombre: empresaResult.Item.nombre,
                    saldo: empresaResult.Item.saldo || 0
                },
                pagination: {
                    total,
                    page: parseInt(page),
                    size: limit,
                    totalPages: Math.ceil(total / limit)
                }
            })
        };

    } catch (error) {
        console.error('Error:', error);
        return {
            statusCode: 500,
            headers: {
                'Access-Control-Allow-Origin': '*',
                'Access-Control-Allow-Credentials': true,
            },
            body: JSON.stringify({
                error: 'Error interno del servidor'
            })
        };
    }
}; 