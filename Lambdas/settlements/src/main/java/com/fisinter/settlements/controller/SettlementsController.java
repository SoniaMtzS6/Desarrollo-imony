package com.fisinter.settlements.controller;

import com.fisinter.settlements.model.SettlementsReportResponse;
import com.fisinter.settlements.model.SettlementData;
import com.fisinter.settlements.model.TransactionData;
import com.fisinter.settlements.service.SettlementsService;

import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;

import lombok.extern.slf4j.Slf4j;

import java.util.Map;
import java.util.HashMap;
import java.util.List;
import java.util.ArrayList;

@RestController
@RequestMapping("/api/settlements")
@Slf4j
@CrossOrigin(origins = "*")
public class SettlementsController {

    @Autowired
    private SettlementsService settlementsService;

    /**
     * Endpoint para generar reporte de settlements vs transacciones
     */
    @GetMapping("/report")
    public ResponseEntity<SettlementsReportResponse> generateReport(
            @RequestParam(value = "start_date", required = false) String startDate,
            @RequestParam(value = "end_date", required = false) String endDate,
            @RequestParam(value = "account_id", required = false) String accountId,
            @RequestParam(value = "page", defaultValue = "1") Integer page,
            @RequestParam(value = "size", defaultValue = "10") Integer size) {
        
        log.info("Generando reporte de settlements - startDate: {}, endDate: {}, accountId: {}, page: {}, size: {}", 
                startDate, endDate, accountId, page, size);
        
        try {
            SettlementsReportResponse response = settlementsService.generateReport(startDate, endDate, accountId, page, size);
            return ResponseEntity.ok(response);
        } catch (Exception e) {
            log.error("Error en endpoint /report: {}", e.getMessage());
            SettlementsReportResponse errorResponse = new SettlementsReportResponse();
            errorResponse.setSuccess(false);
            errorResponse.setMessage("Error interno del servidor: " + e.getMessage());
            return ResponseEntity.internalServerError().body(errorResponse);
        }
    }

    /**
     * Endpoint de salud para verificar que el servicio esté funcionando
     */
    @GetMapping("/health")
    public ResponseEntity<Map<String, Object>> health() {
        Map<String, Object> response = new HashMap<>();
        response.put("status", "OK");
        response.put("service", "Settlements API");
        response.put("timestamp", System.currentTimeMillis());
        return ResponseEntity.ok(response);
    }

    /**
     * Endpoint para obtener settlements específicos
     */
    @GetMapping("/settlements")
    public ResponseEntity<SettlementsReportResponse> getSettlements(
            @RequestParam(value = "start_date", required = false) String startDate,
            @RequestParam(value = "end_date", required = false) String endDate,
            @RequestParam(value = "account_id", required = false) String accountId,
            @RequestParam(value = "page", defaultValue = "1") Integer page,
            @RequestParam(value = "size", defaultValue = "10") Integer size) {
        
        log.info("Obteniendo settlements - startDate: {}, endDate: {}, accountId: {}, page: {}, size: {}", 
                startDate, endDate, accountId, page, size);
        
        try {
            // Para este endpoint solo retornamos settlements
            SettlementsReportResponse response = new SettlementsReportResponse();
            List<SettlementData> settlements = settlementsService.getSettlements(startDate, endDate, accountId);
            
            // Aplicar paginación
            int totalItems = settlements.size();
            int totalPages = (int) Math.ceil((double) totalItems / size);
            int startIndex = (page - 1) * size;
            int endIndex = Math.min(startIndex + size, totalItems);
            
            List<Object> paginatedData = new ArrayList<>(settlements.subList(startIndex, endIndex));
            
            // Configurar resumen solo para settlements
            SettlementsReportResponse.ReportSummary summary = new SettlementsReportResponse.ReportSummary();
            double totalSettlements = settlements.stream()
                .mapToDouble(s -> s.getAmount() != null ? s.getAmount() : 0.0)
                .sum();
            summary.setTotalSettlements(totalSettlements);
            summary.setSettlementsCount(settlements.size());
            
            // Configurar paginación
            SettlementsReportResponse.PaginationInfo pagination = new SettlementsReportResponse.PaginationInfo();
            pagination.setPage(page);
            pagination.setSize(size);
            pagination.setTotal(totalItems);
            pagination.setTotalPages(totalPages);
            
            response.setData(paginatedData);
            response.setSummary(summary);
            response.setPagination(pagination);
            response.setMessage("Settlements obtenidos exitosamente");
            
            return ResponseEntity.ok(response);
        } catch (Exception e) {
            log.error("Error en endpoint /settlements: {}", e.getMessage());
            SettlementsReportResponse errorResponse = new SettlementsReportResponse();
            errorResponse.setSuccess(false);
            errorResponse.setMessage("Error interno del servidor: " + e.getMessage());
            return ResponseEntity.internalServerError().body(errorResponse);
        }
    }

    /**
     * Endpoint para obtener transacciones específicas
     */
    @GetMapping("/transactions")
    public ResponseEntity<SettlementsReportResponse> getTransactions(
            @RequestParam(value = "start_date", required = false) String startDate,
            @RequestParam(value = "end_date", required = false) String endDate,
            @RequestParam(value = "account_id", required = false) String accountId,
            @RequestParam(value = "page", defaultValue = "1") Integer page,
            @RequestParam(value = "size", defaultValue = "10") Integer size) {
        
        log.info("Obteniendo transacciones - startDate: {}, endDate: {}, accountId: {}, page: {}, size: {}", 
                startDate, endDate, accountId, page, size);
        
        try {
            // Para este endpoint solo retornamos transacciones
            SettlementsReportResponse response = new SettlementsReportResponse();
            List<TransactionData> transactions = settlementsService.getTransactions(startDate, endDate, accountId);
            
            // Aplicar paginación
            int totalItems = transactions.size();
            int totalPages = (int) Math.ceil((double) totalItems / size);
            int startIndex = (page - 1) * size;
            int endIndex = Math.min(startIndex + size, totalItems);
            
            List<Object> paginatedData = new ArrayList<>(transactions.subList(startIndex, endIndex));
            
            // Configurar resumen solo para transacciones
            SettlementsReportResponse.ReportSummary summary = new SettlementsReportResponse.ReportSummary();
            double totalTransactions = transactions.stream()
                .mapToDouble(t -> t.getAmount() != null ? t.getAmount() : 0.0)
                .sum();
            summary.setTotalTransactions(totalTransactions);
            summary.setTransactionsCount(transactions.size());
            
            // Configurar paginación
            SettlementsReportResponse.PaginationInfo pagination = new SettlementsReportResponse.PaginationInfo();
            pagination.setPage(page);
            pagination.setSize(size);
            pagination.setTotal(totalItems);
            pagination.setTotalPages(totalPages);
            
            response.setData(paginatedData);
            response.setSummary(summary);
            response.setPagination(pagination);
            response.setMessage("Transacciones obtenidas exitosamente");
            
            return ResponseEntity.ok(response);
        } catch (Exception e) {
            log.error("Error en endpoint /transactions: {}", e.getMessage());
            SettlementsReportResponse errorResponse = new SettlementsReportResponse();
            errorResponse.setSuccess(false);
            errorResponse.setMessage("Error interno del servidor: " + e.getMessage());
            return ResponseEntity.internalServerError().body(errorResponse);
        }
    }
} 