package com.fisinter.user.model;

import com.fasterxml.jackson.annotation.JsonInclude;
import com.fasterxml.jackson.annotation.JsonInclude.Include;
import com.fasterxml.jackson.annotation.JsonProperty;
import com.fisinter.user.entity.User;

import jakarta.validation.constraints.Email;
import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotNull;
import lombok.Getter;
import lombok.NoArgsConstructor;
import lombok.Setter;

@Getter
@Setter
@NoArgsConstructor
@JsonInclude(Include.NON_NULL)
public class UserData {
	
	@JsonProperty("id")
    private String id;
	
	@JsonProperty("id_empresa")
    private Long idEmpresa;
	
    @NotBlank
    @JsonProperty("name")
    private String name;
    
    @NotBlank
    @JsonProperty("surname")
    private String surname;
    
    @NotBlank
    @JsonProperty("identification_type")
    private String identificationType;
    
    @NotNull
    @JsonProperty("identification_value")
    private Long identificationValue;
    
    @NotBlank
    @JsonProperty("birthdate")
    private String birthdate;
    
    @NotBlank
    @JsonProperty("gender")
    private String gender;
    
    @Email
    @JsonProperty("email")
    private String email;
    
    @NotBlank
    @JsonProperty("phone")
    private String phone;
    
    @JsonProperty("tax_identification_type")
    private String taxIdentificationType;
    
    @JsonProperty("tax_identification_value")
    private Long taxIdentificationValue;
    
    @NotBlank
    @JsonProperty("nationality")
    private String nationality;
    
    @NotBlank
    @JsonProperty("tax_condition")
    private String taxCondition;
    
    @NotNull
    @JsonProperty("status")
    private String status;
    
    @NotNull
    @JsonProperty("status_reason")
    private String statusReason;
    
    @NotNull
    @JsonProperty("operation_country")
    private String operationCountry;
    
    @NotNull
    @JsonProperty("legal_address")
    private LegalAddress legalAddress;
    
    @JsonProperty("id_account")
    private String idAccount;
    
    public UserData(User user) {
    	this.id = user.getId();
    	this.name = user.getName();
    	this.surname = user.getSurname();
    	this.identificationType = user.getIdentificationType();
    	this.identificationValue = user.getIdentificationValue();
    	this.birthdate = user.getBirthdate();
    	this.gender = user.getGender();
    	this.email = user.getEmail();
    	this.phone = user.getPhone();
    	this.taxIdentificationType = user.getIdentificationType();
    	this.taxIdentificationValue = user.getTaxIdentificationValue();
    	this.nationality = user.getNationality();
    	this.taxCondition = user.getTaxCondition();
    	this.status = user.getStatus().toString();
    	this.operationCountry = user.getOperationCountry();
    }
    
}
