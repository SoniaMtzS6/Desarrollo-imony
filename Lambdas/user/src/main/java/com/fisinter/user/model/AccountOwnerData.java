package com.fisinter.user.model;

import com.fasterxml.jackson.annotation.JsonInclude;
import com.fasterxml.jackson.annotation.JsonProperty;
import com.fasterxml.jackson.annotation.JsonInclude.Include;

import lombok.Getter;
import lombok.NoArgsConstructor;
import lombok.Setter;

@Getter
@Setter
@NoArgsConstructor
@JsonInclude(Include.NON_NULL)
public class AccountOwnerData {
	
	@JsonProperty("client_id")
    private String clientId;
	
	@JsonProperty("user_id")
    private String userId;
	
	@JsonProperty("company_id")
    private String companyId;

}
