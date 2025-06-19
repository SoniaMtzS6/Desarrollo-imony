package com.fisinter.user.model;

import com.fasterxml.jackson.annotation.JsonInclude;
import com.fasterxml.jackson.annotation.JsonProperty;
import com.fasterxml.jackson.annotation.JsonInclude.Include;

import lombok.AllArgsConstructor;
import lombok.Getter;
import lombok.NoArgsConstructor;
import lombok.Setter;

@AllArgsConstructor
@NoArgsConstructor
@Getter
@Setter
@JsonInclude(Include.NON_NULL)
public class TokenResponse {
	
	@JsonProperty("access_token")
	private String accessToken;
	
	@JsonProperty("scope")
	private String scope;
	
	@JsonProperty("expires_in")
	private long expiresIn;
	
	@JsonProperty("token_type")
	private String tokenType;
	
}
