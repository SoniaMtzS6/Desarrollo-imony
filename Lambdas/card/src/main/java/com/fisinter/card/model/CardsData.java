package com.fisinter.card.model;

import java.util.List;

import com.fasterxml.jackson.annotation.JsonProperty;

import lombok.Getter;
import lombok.Setter;

@Getter
@Setter
public class CardsData {
	
	@JsonProperty("data")
	private List<CardData> data;
	
	@JsonProperty("meta")
	private Meta meta;
	
	@JsonProperty("error")
	private String error;

}
