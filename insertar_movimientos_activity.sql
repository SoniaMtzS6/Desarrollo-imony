USE finister;

-- Insertar 10 movimientos de prueba en la tabla activity
INSERT INTO activity (account, country, currency, merchant_name, merchant_id, card_type, origin_tx_id, process_type, result, total_amount, activity_type, datetime, idempotency_key, type, version) VALUES
('ACC001', 'MEX', 'MXN', 'Walmart Supercenter', 'MER001', 'DEBIT', 'TX001', 'PURCHASE', 'APPROVED', '1250.50', 'TRANSACTION', '2024-01-15T14:30:00Z', 'IDEMP001', 'PURCHASE', '1.0'),
('ACC002', 'MEX', 'MXN', 'Starbucks Coffee', 'MER002', 'CREDIT', 'TX002', 'PURCHASE', 'APPROVED', '85.00', 'TRANSACTION', '2024-01-15T09:15:00Z', 'IDEMP002', 'PURCHASE', '1.0'),
('ACC003', 'MEX', 'MXN', 'Shell Gas Station', 'MER003', 'DEBIT', 'TX003', 'PURCHASE', 'APPROVED', '500.00', 'TRANSACTION', '2024-01-15T16:45:00Z', 'IDEMP003', 'PURCHASE', '1.0'),
('ACC001', 'MEX', 'MXN', 'ATM Banamex', 'MER004', 'DEBIT', 'TX004', 'WITHDRAWAL', 'APPROVED', '1000.00', 'WITHDRAWAL', '2024-01-15T11:20:00Z', 'IDEMP004', 'WITHDRAWAL', '1.0'),
('ACC002', 'MEX', 'MXN', 'Amazon Mexico', 'MER005', 'CREDIT', 'TX005', 'PURCHASE', 'APPROVED', '2500.75', 'TRANSACTION', '2024-01-15T13:10:00Z', 'IDEMP005', 'PURCHASE', '1.0'),
('ACC003', 'MEX', 'MXN', 'OXXO Convenience', 'MER006', 'DEBIT', 'TX006', 'PURCHASE', 'APPROVED', '150.25', 'TRANSACTION', '2024-01-15T20:30:00Z', 'IDEMP006', 'PURCHASE', '1.0'),
('ACC001', 'MEX', 'MXN', 'Netflix Subscription', 'MER007', 'CREDIT', 'TX007', 'RECURRING', 'APPROVED', '199.00', 'RECURRING', '2024-01-15T00:01:00Z', 'IDEMP007', 'RECURRING', '1.0'),
('ACC002', 'MEX', 'MXN', 'Uber Ride', 'MER008', 'DEBIT', 'TX008', 'PURCHASE', 'APPROVED', '120.00', 'TRANSACTION', '2024-01-15T18:45:00Z', 'IDEMP008', 'PURCHASE', '1.0'),
('ACC003', 'MEX', 'MXN', 'Restaurant El Pescador', 'MER009', 'CREDIT', 'TX009', 'PURCHASE', 'APPROVED', '450.00', 'TRANSACTION', '2024-01-15T19:30:00Z', 'IDEMP009', 'PURCHASE', '1.0'),
('ACC001', 'MEX', 'MXN', 'Deposit from Bank', 'MER010', 'DEBIT', 'TX010', 'DEPOSIT', 'APPROVED', '5000.00', 'DEPOSIT', '2024-01-15T08:00:00Z', 'IDEMP010', 'DEPOSIT', '1.0');

-- Verificar que se insertaron correctamente
SELECT COUNT(*) as total_movimientos FROM activity; 