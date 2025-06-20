package com.fisinter.settlements.model;

import com.fasterxml.jackson.annotation.JsonInclude;
import com.fasterxml.jackson.annotation.JsonProperty;
import com.fasterxml.jackson.annotation.JsonInclude.Include;

import lombok.Getter;
import lombok.NoArgsConstructor;
import lombok.Setter;

import java.util.List;

@Getter
@Setter
@NoArgsConstructor
@JsonInclude(Include.NON_NULL)
public class SettlementsReportResponse {
	
	@JsonProperty("success")
    private Boolean success = true;
	
	@JsonProperty("message")
    private String message;
	
	@JsonProperty("data")
    private List<Object> data;
	
	@JsonProperty("summary")
    private ReportSummary summary;
	
	@JsonProperty("pagination")
    private PaginationInfo pagination;
	
	@Getter
	@Setter
	@NoArgsConstructor
	@JsonInclude(Include.NON_NULL)
	public static class ReportSummary {
		@JsonProperty("total_settlements")
		private Double totalSettlements = 0.0;
		
		@JsonProperty("total_transactions")
		private Double totalTransactions = 0.0;
		
		@JsonProperty("total_movements")
		private Double totalMovements = 0.0;
		
		@JsonProperty("settlements_count")
		private Integer settlementsCount = 0;
		
		@JsonProperty("transactions_count")
		private Integer transactionsCount = 0;
		
		@JsonProperty("movements_count")
		private Integer movementsCount = 0;
		
		@JsonProperty("difference")
		private Double difference = 0.0;
	}
	
	@Getter
	@Setter
	@NoArgsConstructor
	@JsonInclude(Include.NON_NULL)
	public static class PaginationInfo {
		@JsonProperty("page")
		private Integer page = 1;
		
		@JsonProperty("size")
		private Integer size = 10;
		
		@JsonProperty("total")
		private Integer total = 0;
		
		@JsonProperty("total_pages")
		private Integer totalPages = 0;
	}
} 