package com.fisinter.user.model;

import com.fasterxml.jackson.annotation.JsonInclude;
import com.fasterxml.jackson.annotation.JsonProperty;
import com.fasterxml.jackson.annotation.JsonInclude.Include;

import lombok.Getter;
import lombok.Setter;

@Getter
@Setter
@JsonInclude(Include.NON_NULL)
public class AccountResponse {
	
	@JsonProperty("data")
	private AccountData data;
	
	@JsonProperty("error")
	private String error;

}
