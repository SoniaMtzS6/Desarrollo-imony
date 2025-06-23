package com.fisinter.transactions;

import lombok.Getter;
import lombok.Setter;

@Getter
@Setter
public class Transaction {

  private String idTransaction;
  private String eventId;
  private String typeTransaction;
  private String countryCode;
  private String originTransaction;
  private String sourceTransaction;
  private String network;
  private String originalTransactionId;
  private String localDateTime;
  private Integer idMerchant;
  private Integer merchantMcc;
  private String merchantName;
  private String idCard;
  private String productType;
  private String providerCard;
  private Integer lastFour;
  private String idUser;
  private double totalLocalAmount;
  private String currencyLocalAmount;
  private double totalTransactionAmount;
  private String currencyTransactionAmount;
  private double totalSettlementAmount;
  private String currencySettlementAmount;
  private String typeDetailsAmount;
  private String currencyDetailsAmount;
  private double amountDetailsAmount;
  private String nameDetailsAmount;
  private String status;
  private String statusDetail;
  private String extraDetail;
}
