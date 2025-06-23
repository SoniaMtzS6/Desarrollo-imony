package com.fisinter.user.model;

import org.springframework.data.domain.Page;

import com.fasterxml.jackson.annotation.JsonInclude;
import com.fasterxml.jackson.annotation.JsonProperty;
import com.fasterxml.jackson.annotation.JsonInclude.Include;
import com.fisinter.user.entity.User;

import lombok.Getter;
import lombok.Setter;

@Getter
@Setter
@JsonInclude(Include.NON_NULL)
public class UsersData {
	
	@JsonProperty("data")
	private Page<User> data;
	
	@JsonProperty("meta")
	private Meta meta;
	
	@JsonProperty("error")
	private String error;

}
