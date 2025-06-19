package com.fisinter.card.association.model;

import com.fasterxml.jackson.annotation.JsonInclude;
import com.fasterxml.jackson.annotation.JsonProperty;
import com.fasterxml.jackson.annotation.JsonInclude.Include;

import jakarta.validation.constraints.NotBlank;
import lombok.Getter;
import lombok.Setter;

@Getter
@Setter
@JsonInclude(Include.NON_NULL)
public class CardData {
	
	@NotBlank
    @JsonProperty("card_id")
    private String cardId;
	
	@NotBlank
    @JsonProperty("account_id")
	private String accountId;
	
	@NotBlank
    @JsonProperty("associated")
	private boolean associated;
	
	@NotBlank
    @JsonProperty("created_at")
	private String createdAt;
	
	@NotBlank
    @JsonProperty("updated_at")
	private String updatedAt;
	
	@JsonProperty("error")
	private String error;

}
