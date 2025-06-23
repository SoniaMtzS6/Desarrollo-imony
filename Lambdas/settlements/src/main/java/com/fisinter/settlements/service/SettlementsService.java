package com.fisinter.settlements.service;

import com.fisinter.settlements.model.SettlementData;
import com.fisinter.settlements.model.TransactionData;
import com.fisinter.settlements.model.SettlementsReportResponse;
import com.fisinter.settlements.model.SettlementsReportResponse.ReportSummary;
import com.fisinter.settlements.model.SettlementsReportResponse.PaginationInfo;

import org.springframework.http.HttpEntity;
import org.springframework.http.HttpHeaders;
import org.springframework.http.HttpMethod;
import org.springframework.http.ResponseEntity;
import org.springframework.stereotype.Service;
import org.springframework.web.client.RestTemplate;
import org.springframework.web.util.UriComponentsBuilder;

import lombok.extern.slf4j.Slf4j;

import java.util.ArrayList;
import java.util.Comparator;
import java.util.List;
import java.util.Map;

@Service
@Slf4j
public class SettlementsService {

    private final RestTemplate restTemplate = new RestTemplate();

    /**
     * Obtiene settlements desde la API de Pomelo
     */
    public List<SettlementData> getSettlements(String startDate, String endDate, String accountId, String pomeloToken, String pomeloBaseUrl) {
        List<SettlementData> settlements = new ArrayList<>();
        
        try {
            if (pomeloToken == null || pomeloBaseUrl == null) {
                log.error("El token de acceso o la URL base de Pomelo no fueron proporcionados");
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
            headers.setBearerAuth(pomeloToken);
            
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
            log.error("Error obteniendo settlements: {}", e.getMessage(), e);
        }
        
        return settlements;
    }

    /**
     * Obtiene transacciones desde la API de Pomelo
     */
    public List<TransactionData> getTransactions(String startDate, String endDate, String accountId, String pomeloToken, String pomeloBaseUrl) {
        List<TransactionData> transactions = new ArrayList<>();
        
        try {
            if (pomeloToken == null || pomeloBaseUrl == null) {
                log.error("El token de acceso o la URL base de Pomelo no fueron proporcionados");
                return transactions;
            }

            // Usamos un path fijo ya que no podemos leerlo de la BD
            String movementUri = "/core/transactions/v1";
            String transactionsUrl = pomeloBaseUrl + movementUri;
            
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
            headers.setBearerAuth(pomeloToken);
            
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
                        transaction.setDescription((String) item.get("description"));
                        transaction.setCreatedAt((String) item.get("created_at"));
                        transaction.setStatus((String) item.get("status"));
                        transaction.setType((String) item.get("type"));
                        // Campos que podrían no estar en el objeto de respuesta de la API.
                        // transaction.setAuthorizationCode((String) item.get("authorization_code"));
                        // transaction.setCardId((String) item.get("card_id"));
                        transaction.setMerchantName((String) item.get("merchant_name"));
                        
                        transactions.add(transaction);
                    }
                }
            }
            
            log.info("Transacciones obtenidas: {}", transactions.size());

        } catch (Exception e) {
            log.error("Error obteniendo transacciones: {}", e.getMessage(), e);
        }
        
        return transactions;
    }

    /**
     * Genera un reporte combinado, paginado, y con resumen
     */
    public SettlementsReportResponse generateReport(String startDate, String endDate, String accountId, 
                                                   Integer page, Integer size, String pomeloToken, String pomeloBaseUrl) {
        
        // Obtenemos todos los datos usando los métodos refactorizados
        List<SettlementData> allSettlements = getSettlements(startDate, endDate, accountId, pomeloToken, pomeloBaseUrl);
        List<TransactionData> allTransactions = getTransactions(startDate, endDate, accountId, pomeloToken, pomeloBaseUrl);
        
        List<Object> combinedList = new ArrayList<>();
        combinedList.addAll(allSettlements);
        combinedList.addAll(allTransactions);
        
        // Ordenar por fecha de creación
        combinedList.sort(Comparator.comparing(this::getCreatedAt).reversed());
        
        // Calcular resumen
        ReportSummary summary = calculateSummary(allSettlements, allTransactions);
        
        // Aplicar paginación
        int totalItems = combinedList.size();
        int totalPages = (int) Math.ceil((double) totalItems / size);
        int startIndex = (page - 1) * size;
        int endIndex = Math.min(startIndex + size, totalItems);
        
        List<Object> paginatedData = new ArrayList<>();
        if(startIndex <= endIndex) {
            paginatedData = combinedList.subList(startIndex, endIndex);
        }
        
        // Configurar paginación
        PaginationInfo pagination = new PaginationInfo();
        pagination.setPage(page);
        pagination.setSize(size);
        pagination.setTotal(totalItems);
        pagination.setTotalPages(totalPages);
        
        SettlementsReportResponse response = new SettlementsReportResponse();
        response.setData(paginatedData);
        response.setSummary(summary);
        response.setPagination(pagination);
        response.setMessage("Reporte generado exitosamente");
        
        log.info("Reporte generado con {} settlements y {} transacciones", 
                allSettlements.size(), allTransactions.size());
        
        return response;
    }

    private ReportSummary calculateSummary(List<SettlementData> settlements, List<TransactionData> transactions) {
        ReportSummary summary = new ReportSummary();
        
        double totalSettlements = settlements.stream()
            .mapToDouble(s -> s.getAmount() != null ? s.getAmount() : 0.0)
            .sum();
        
        double totalTransactions = transactions.stream()
            .mapToDouble(t -> t.getAmount() != null ? t.getAmount() : 0.0)
            .sum();
            
        summary.setTotalSettlements(totalSettlements);
        summary.setTotalTransactions(totalTransactions);
        summary.setTotalMovements(totalSettlements + totalTransactions);
        summary.setSettlementsCount(settlements.size());
        summary.setTransactionsCount(transactions.size());
        summary.setMovementsCount(settlements.size() + transactions.size());
        summary.setDifference(totalSettlements - totalTransactions);
        
        return summary;
    }

    private String getCreatedAt(Object obj) {
        if (obj instanceof SettlementData) {
            return ((SettlementData) obj).getCreatedAt();
        } else if (obj instanceof TransactionData) {
            return ((TransactionData) obj).getCreatedAt();
        }
        return "";
    }

    private Double parseDouble(Object value) {
        if (value instanceof Number) {
            return ((Number) value).doubleValue();
        }
        if (value instanceof String) {
            try {
                return Double.parseDouble((String) value);
            } catch (NumberFormatException e) {
                return 0.0;
            }
        }
        return 0.0;
    }
}