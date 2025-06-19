package com.fisinter.user.model;

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
public class AccountData {
	
	//Only Request fields
	
	@JsonProperty("user_id")
    private String userId;
	
	// Request & response fields
	
	@NotBlank
    @JsonProperty("owner_type")
    private String ownerType;
	
	@NotBlank
	@JsonProperty("country")
    private String country;
	
	@NotBlank
	@JsonProperty("currency")
	private String currency;
	
	//Only Response fields
	 
	@JsonProperty("id")
    private String id;
	
    @JsonProperty("data")
    private AccountSubDataResponse data;
    
    @JsonProperty("metadata")
    private Object metadata;
    
    @JsonProperty("status")
    private String status;
    
    @JsonProperty("owner_data")
    private AccountOwnerData ownerData;
    
    @JsonProperty("created_at")
    private String createdAt;

	public AccountData(String ownerType, String userId, String country, String currency) {
		super();
		this.ownerType = ownerType;
		this.userId = userId;
		this.country = country;
		this.currency = currency;
	}
 
}
