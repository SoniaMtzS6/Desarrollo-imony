package com.fisinter.card.model;

import com.fasterxml.jackson.annotation.JsonInclude;
import com.fasterxml.jackson.annotation.JsonProperty;

import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotNull;

import com.fasterxml.jackson.annotation.JsonInclude.Include;

import lombok.Getter;
import lombok.Setter;

@Getter
@Setter
@JsonInclude(Include.NON_NULL)
public class CardData {
	
	@NotBlank
	@JsonProperty("id")
	private String id;
	
	@NotBlank
	@JsonProperty("user_id")
	private String userId;
	
	@NotBlank
    @JsonProperty("affinity_group_id")
	private String affinityGroupId;
	
	@NotBlank
    @JsonProperty("card_type")
	private String cardType;
	
	@NotBlank
    @JsonProperty("product_type")
	private String productType;
	
	@NotBlank
    @JsonProperty("status")
	private String status;
	
	@NotBlank
    @JsonProperty("status_reason")
	private String statusReason;
	
	@NotBlank
    @JsonProperty("shipment_id")
	private String shipmentId;
	
	@NotBlank
    @JsonProperty("start_date")
	private String startDate;
	
	@NotBlank
    @JsonProperty("last_four")
	private String lastFour;
	
	@NotBlank
    @JsonProperty("provider")
	private String provider;
	
	@NotBlank
    @JsonProperty("affinity_group_name")
	private String affinityGroupName;
	
	@NotNull
    @JsonProperty("address")
	private Address address;
	
	@NotBlank
	@JsonProperty("company")
	private String company;
	
	@NotBlank
	@JsonProperty("previous_card_id")
	private String previousCardId;
	
	@NotNull
    @JsonProperty("pin")
    private String pin;
	
	@NotNull
    @JsonProperty("name_on_card")
    private String nameOnCard;

}
