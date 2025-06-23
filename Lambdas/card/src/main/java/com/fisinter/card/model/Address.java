package com.fisinter.card.model;

import com.fasterxml.jackson.annotation.JsonProperty;

import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotNull;
import lombok.Getter;
import lombok.Setter;

@Getter
@Setter
public class Address {
	
	@NotBlank
	@JsonProperty("street_name")
    private String streetName;

	@NotNull
    @JsonProperty("street_number")
    private int streetNumber;

	@NotNull
    @JsonProperty("floor")
    private int floor;

    @NotBlank
    @JsonProperty("apartment")
    private String apartment;
    
    @NotBlank
    @JsonProperty("city")
    private String city;
    
    @NotBlank
    @JsonProperty("region")
    private String region;
    
    @NotBlank
    @JsonProperty("country")
    private String country;
    
    @NotNull
    @JsonProperty("zip_code")
    private int zipCode;

    @NotBlank
    @JsonProperty("neighborhood")
    private String neighborhood;

    @NotBlank
    @JsonProperty("additional_info")
    private String additionalInfo;
    
}