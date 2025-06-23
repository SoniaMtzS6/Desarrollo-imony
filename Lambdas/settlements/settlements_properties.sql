-- Script para insertar propiedades necesarias para la Lambda de Settlements
-- Ejecutar solo si las propiedades no existen ya

-- Propiedad para la URL de settlements (si no existe)
INSERT IGNORE INTO properties (name, value) 
VALUES ('settlement.uri', '/core/settlements/v1');

-- Verificar que las propiedades existentes estén correctas
-- Las siguientes propiedades ya deberían existir según los datos proporcionados:
-- base.url = https://api.pomelo.la
-- client.id = cxq8Yq6wFgE53FxOzgHAGrCzRe5n4KgL
-- client.secret = hfGy54beNj08H6v7AnvTgo2g5zYGXZ9kyTdtpEHs5b_Vzgv1WDypnyFUz6273YO9
-- audience = https://auth-prod.pomelo.la
-- grant.type = client_credentials
-- token.uri.solicitar = /oauth/token
-- movement.uri = /core/transactions/v1

-- Consulta para verificar las propiedades existentes
SELECT name, value FROM properties WHERE name IN (
    'base.url',
    'client.id', 
    'client.secret',
    'audience',
    'grant.type',
    'token.uri.solicitar',
    'movement.uri',
    'settlement.uri'
) ORDER BY name; 