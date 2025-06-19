package com.fisinter.user.model;

import com.fasterxml.jackson.annotation.JsonInclude;
import com.fasterxml.jackson.annotation.JsonProperty;
import com.fasterxml.jackson.annotation.JsonInclude.Include;

import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotNull;
import lombok.Getter;
import lombok.Setter;

@Getter
@Setter
@JsonInclude(Include.NON_NULL)
public class LegalAddress {
	
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

    @NotNull
    @JsonProperty("zip_code")
    private int zipCode;

    @NotBlank
    @JsonProperty("neighborhood")
    private String neighborhood;

    @NotBlank
    @JsonProperty("city")
    private String city;

    @NotBlank
    @JsonProperty("region")
    private String region;

    @NotBlank
    @JsonProperty("additional_info")
    private String additionalInfo;

    @NotBlank
    @JsonProperty("country")
    private String country;

}
