package com.fisinter.settlements.service;

import com.fisinter.settlements.model.SettlementData;
import com.fisinter.settlements.model.TransactionData;
import com.fisinter.settlements.model.SettlementsReportResponse;
import com.fisinter.settlements.model.SettlementsReportResponse.ReportSummary;
import com.fisinter.settlements.model.SettlementsReportResponse.PaginationInfo;

import org.springframework.beans.factory.annotation.Value;
import org.springframework.http.HttpEntity;
import org.springframework.http.HttpHeaders;
import org.springframework.http.HttpMethod;
import org.springframework.http.ResponseEntity;
import org.springframework.stereotype.Service;
import org.springframework.web.client.RestTemplate;
import org.springframework.web.util.UriComponentsBuilder;

import lombok.extern.slf4j.Slf4j;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

@Service
@Slf4j
public class SettlementsService {

    @Value("${pomelo.api.base-url:https://api.pomelo.la}")
    private String pomeloBaseUrl;
    
    @Value("${pomelo.api.client-id}")
    private String clientId;
    
    @Value("${pomelo.api.client-secret}")
    private String clientSecret;
    
    @Value("${pomelo.api.username}")
    private String username;
    
    @Value("${pomelo.api.password}")
    private String password;

    private final RestTemplate restTemplate = new RestTemplate();
    private String accessToken = null;
    private long tokenExpiry = 0;

    /**
     * Obtiene el token de acceso OAuth2 de Pomelo
     */
    private String getAccessToken() {
        long currentTime = System.currentTimeMillis();
        
        // Si el token aún es válido, lo retornamos
        if (accessToken != null && currentTime < tokenExpiry) {
            return accessToken;
        }

        try {
            String tokenUrl = pomeloBaseUrl + "/oauth/token";
            
            HttpHeaders headers = new HttpHeaders();
            headers.set("Content-Type", "application/x-www-form-urlencoded");
            
            String body = String.format(
                "grant_type=password&client_id=%s&client_secret=%s&username=%s&password=%s",
                clientId, clientSecret, username, password
            );
            
            HttpEntity<String> request = new HttpEntity<>(body, headers);
            
            ResponseEntity<Map> response = restTemplate.postForEntity(tokenUrl, request, Map.class);
            
            if (response.getStatusCode().is2xxSuccessful() && response.getBody() != null) {
                accessToken = (String) response.getBody().get("access_token");
                Integer expiresIn = (Integer) response.getBody().get("expires_in");
                tokenExpiry = currentTime + (expiresIn * 1000L) - 60000; // 1 minuto antes de expirar
                
                log.info("Token de acceso obtenido exitosamente");
                return accessToken;
            }
        } catch (Exception e) {
            log.error("Error obteniendo token de acceso: {}", e.getMessage());
        }
        
        return null;
    }

    /**
     * Obtiene settlements desde la API de Pomelo
     */
    public List<SettlementData> getSettlements(String startDate, String endDate, String accountId) {
        List<SettlementData> settlements = new ArrayList<>();
        
        try {
            String token = getAccessToken();
            if (token == null) {
                log.error("No se pudo obtener el token de acceso");
                return settlements;
            }

            String settlementsUrl = pomeloBaseUrl + "/core/settlements/v1";
            
            UriComponentsBuilder builder = UriComponentsBuilder.fromHttpUrl(settlementsUrl);
            if (startDate != null && !startDate.isEmpty()) {
                builder.queryParam("start_date", startDate);
            }
            if (endDate != null && !endDate.isEmpty()) {
                builder.queryParam("end_date", endDate);
            }
            if (accountId != null && !accountId.isEmpty()) {
                builder.queryParam("account_id", accountId);
            }

            HttpHeaders headers = new HttpHeaders();
            headers.setBearerAuth(token);
            
            HttpEntity<String> request = new HttpEntity<>(headers);
            
            ResponseEntity<Map> response = restTemplate.exchange(
                builder.toUriString(), 
                HttpMethod.GET, 
                request, 
                Map.class
            );
            
            if (response.getStatusCode().is2xxSuccessful() && response.getBody() != null) {
                List<Map<String, Object>> data = (List<Map<String, Object>>) response.getBody().get("data");
                
                if (data != null) {
                    for (Map<String, Object> item : data) {
                        SettlementData settlement = new SettlementData();
                        settlement.setId((String) item.get("id"));
                        settlement.setAccountId((String) item.get("account_id"));
                        settlement.setAmount(parseDouble(item.get("amount")));
                        settlement.setCurrency((String) item.get("currency"));
                        settlement.setStatus((String) item.get("status"));
                        settlement.setCreatedAt((String) item.get("created_at"));
                        settlement.setDescription((String) item.get("description"));
                        settlement.setSettlementId((String) item.get("settlement_id"));
                        settlement.setMerchantName((String) item.get("merchant_name"));
                        settlement.setMerchantId((String) item.get("merchant_id"));
                        settlement.setCardLastFour((String) item.get("card_last_four"));
                        settlement.setEntryType((String) item.get("entry_type"));
                        settlement.setProcessType((String) item.get("process_type"));
                        settlement.setResult((String) item.get("result"));
                        settlement.setTotalAmount((String) item.get("total_amount"));
                        settlement.setLocalAmount(parseDouble(item.get("local_amount")));
                        settlement.setLocalCurrency((String) item.get("local_currency"));
                        settlement.setSettlementAmount(parseDouble(item.get("settlement_amount")));
                        settlement.setSettlementCurrency((String) item.get("settlement_currency"));
                        settlement.setTransactionAmount(parseDouble(item.get("transaction_amount")));
                        settlement.setTransactionCurrency((String) item.get("transaction_currency"));
                        
                        settlements.add(settlement);
                    }
                }
            }
            
            log.info("Settlements obtenidos: {}", settlements.size());
            
        } catch (Exception e) {
            log.error("Error obteniendo settlements: {}", e.getMessage());
        }
        
        return settlements;
    }

    /**
     * Obtiene transacciones desde la API de Pomelo
     */
    public List<TransactionData> getTransactions(String startDate, String endDate, String accountId) {
        List<TransactionData> transactions = new ArrayList<>();
        
        try {
            String token = getAccessToken();
            if (token == null) {
                log.error("No se pudo obtener el token de acceso");
                return transactions;
            }

            String transactionsUrl = pomeloBaseUrl + "/core/transactions/v1";
            
            UriComponentsBuilder builder = UriComponentsBuilder.fromHttpUrl(transactionsUrl);
            if (startDate != null && !startDate.isEmpty()) {
                builder.queryParam("start_date", startDate);
            }
            if (endDate != null && !endDate.isEmpty()) {
                builder.queryParam("end_date", endDate);
            }
            if (accountId != null && !accountId.isEmpty()) {
                builder.queryParam("account_id", accountId);
            }

            HttpHeaders headers = new HttpHeaders();
            headers.setBearerAuth(token);
            
            HttpEntity<String> request = new HttpEntity<>(headers);
            
            ResponseEntity<Map> response = restTemplate.exchange(
                builder.toUriString(), 
                HttpMethod.GET, 
                request, 
                Map.class
            );
            
            if (response.getStatusCode().is2xxSuccessful() && response.getBody() != null) {
                List<Map<String, Object>> data = (List<Map<String, Object>>) response.getBody().get("data");
                
                if (data != null) {
                    for (Map<String, Object> item : data) {
                        TransactionData transaction = new TransactionData();
                        transaction.setId((String) item.get("id"));
                        transaction.setAccountId((String) item.get("account_id"));
                        transaction.setAmount(parseDouble(item.get("amount")));
                        transaction.setCurrency((String) item.get("currency"));
                        transaction.setStatus((String) item.get("status"));
                        transaction.setCreatedAt((String) item.get("created_at"));
                        transaction.setDescription((String) item.get("description"));
                        transaction.setTransactionId((String) item.get("transaction_id"));
                        transaction.setMerchantName((String) item.get("merchant_name"));
                        transaction.setMerchantId((String) item.get("merchant_id"));
                        transaction.setCardLastFour((String) item.get("card_last_four"));
                        transaction.setEntryType((String) item.get("entry_type"));
                        transaction.setProcessType((String) item.get("process_type"));
                        transaction.setResult((String) item.get("result"));
                        transaction.setTotalAmount((String) item.get("total_amount"));
                        transaction.setLocalAmount(parseDouble(item.get("local_amount")));
                        transaction.setLocalCurrency((String) item.get("local_currency"));
                        transaction.setSettlementAmount(parseDouble(item.get("settlement_amount")));
                        transaction.setSettlementCurrency((String) item.get("settlement_currency"));
                        transaction.setTransactionAmount(parseDouble(item.get("transaction_amount")));
                        transaction.setTransactionCurrency((String) item.get("transaction_currency"));
                        
                        transactions.add(transaction);
                    }
                }
            }
            
            log.info("Transacciones obtenidas: {}", transactions.size());
            
        } catch (Exception e) {
            log.error("Error obteniendo transacciones: {}", e.getMessage());
        }
        
        return transactions;
    }

    /**
     * Genera el reporte combinado de settlements y transacciones
     */
    public SettlementsReportResponse generateReport(String startDate, String endDate, String accountId, 
                                                   Integer page, Integer size) {
        SettlementsReportResponse response = new SettlementsReportResponse();
        
        try {
            // Obtener datos
            List<SettlementData> settlements = getSettlements(startDate, endDate, accountId);
            List<TransactionData> transactions = getTransactions(startDate, endDate, accountId);
            
            // Combinar datos
            List<Object> combinedData = new ArrayList<>();
            combinedData.addAll(settlements);
            combinedData.addAll(transactions);
            
            // Ordenar por fecha de creación
            combinedData.sort((a, b) -> {
                String dateA = getCreatedAt(a);
                String dateB = getCreatedAt(b);
                return dateB.compareTo(dateA); // Orden descendente
            });
            
            // Calcular resumen
            ReportSummary summary = calculateSummary(settlements, transactions);
            
            // Aplicar paginación
            int totalItems = combinedData.size();
            int totalPages = (int) Math.ceil((double) totalItems / size);
            int startIndex = (page - 1) * size;
            int endIndex = Math.min(startIndex + size, totalItems);
            
            List<Object> paginatedData = combinedData.subList(startIndex, endIndex);
            
            // Configurar paginación
            PaginationInfo pagination = new PaginationInfo();
            pagination.setPage(page);
            pagination.setSize(size);
            pagination.setTotal(totalItems);
            pagination.setTotalPages(totalPages);
            
            response.setData(paginatedData);
            response.setSummary(summary);
            response.setPagination(pagination);
            response.setMessage("Reporte generado exitosamente");
            
            log.info("Reporte generado con {} settlements y {} transacciones", 
                    settlements.size(), transactions.size());
            
        } catch (Exception e) {
            log.error("Error generando reporte: {}", e.getMessage());
            response.setSuccess(false);
            response.setMessage("Error generando reporte: " + e.getMessage());
        }
        
        return response;
    }

    /**
     * Calcula el resumen del reporte
     */
    private ReportSummary calculateSummary(List<SettlementData> settlements, List<TransactionData> transactions) {
        ReportSummary summary = new ReportSummary();
        
        // Calcular totales de settlements
        double totalSettlements = settlements.stream()
            .mapToDouble(s -> s.getAmount() != null ? s.getAmount() : 0.0)
            .sum();
        
        // Calcular totales de transacciones
        double totalTransactions = transactions.stream()
            .mapToDouble(t -> t.getAmount() != null ? t.getAmount() : 0.0)
            .sum();
        
        // Calcular total de movimientos
        double totalMovements = totalSettlements + totalTransactions;
        
        // Calcular diferencia
        double difference = totalSettlements - totalTransactions;
        
        summary.setTotalSettlements(totalSettlements);
        summary.setTotalTransactions(totalTransactions);
        summary.setTotalMovements(totalMovements);
        summary.setSettlementsCount(settlements.size());
        summary.setTransactionsCount(transactions.size());
        summary.setMovementsCount(settlements.size() + transactions.size());
        summary.setDifference(difference);
        
        return summary;
    }

    /**
     * Obtiene la fecha de creación de un objeto
     */
    private String getCreatedAt(Object obj) {
        if (obj instanceof SettlementData) {
            return ((SettlementData) obj).getCreatedAt();
        } else if (obj instanceof TransactionData) {
            return ((TransactionData) obj).getCreatedAt();
        }
        return "";
    }

    /**
     * Parsea un valor a Double de forma segura
     */
    private Double parseDouble(Object value) {
        if (value == null) {
            return null;
        }
        if (value instanceof Number) {
            return ((Number) value).doubleValue();
        }
        try {
            return Double.parseDouble(value.toString());
        } catch (NumberFormatException e) {
            return null;
        }
    }
} 