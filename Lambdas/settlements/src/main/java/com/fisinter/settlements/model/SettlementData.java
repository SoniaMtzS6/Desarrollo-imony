package com.fisinter.settlements.model;

import com.fasterxml.jackson.annotation.JsonInclude;
import com.fasterxml.jackson.annotation.JsonProperty;
import com.fasterxml.jackson.annotation.JsonInclude.Include;

import jakarta.validation.constraints.NotBlank;
import lombok.Getter;
import lombok.NoArgsConstructor;
import lombok.Setter;

@Getter
@Setter
@NoArgsConstructor
@JsonInclude(Include.NON_NULL)
public class SettlementData {
	
	@JsonProperty("id")
    private String id;
	
	@JsonProperty("account_id")
    private String accountId;
	
	@JsonProperty("amount")
    private Double amount;
	
	@JsonProperty("currency")
    private String currency;
	
	@JsonProperty("status")
    private String status;
	
	@JsonProperty("created_at")
    private String createdAt;
	
	@JsonProperty("type")
    private String type = "settlement";
	
	@JsonProperty("description")
    private String description;
	
	@JsonProperty("settlement_id")
    private String settlementId;
	
	@JsonProperty("transaction_id")
    private String transactionId;
	
	@JsonProperty("merchant_name")
    private String merchantName;
	
	@JsonProperty("merchant_id")
    private String merchantId;
	
	@JsonProperty("card_last_four")
    private String cardLastFour;
	
	@JsonProperty("entry_type")
    private String entryType;
	
	@JsonProperty("process_type")
    private String processType;
	
	@JsonProperty("result")
    private String result;
	
	@JsonProperty("total_amount")
    private String totalAmount;
	
	@JsonProperty("local_amount")
    private Double localAmount;
	
	@JsonProperty("local_currency")
    private String localCurrency;
	
	@JsonProperty("settlement_amount")
    private Double settlementAmount;
	
	@JsonProperty("settlement_currency")
    private String settlementCurrency;
	
	@JsonProperty("transaction_amount")
    private Double transactionAmount;
	
	@JsonProperty("transaction_currency")
    private String transactionCurrency;
} 