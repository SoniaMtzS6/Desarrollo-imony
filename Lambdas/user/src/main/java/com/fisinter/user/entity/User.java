package com.fisinter.user.entity;

import com.fasterxml.jackson.annotation.JsonIgnore;
import com.fasterxml.jackson.annotation.JsonInclude;
import com.fisinter.user.enums.Status;
import com.fisinter.user.model.UserData;

import jakarta.persistence.Column;
import jakarta.persistence.Entity;
import jakarta.persistence.EnumType;
import jakarta.persistence.Enumerated;
import jakarta.persistence.GeneratedValue;
import jakarta.persistence.GenerationType;
import jakarta.persistence.Id;
import jakarta.persistence.Table;
import lombok.Getter;
import lombok.NoArgsConstructor;
import lombok.Setter;

@Entity
@Getter
@Setter
@Table(name = "user")
@JsonInclude(JsonInclude.Include.NON_NULL)
@NoArgsConstructor
public class User {
	
	@Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "id_")
    private Long idL;

    @Column(name = "id", unique = true, nullable = false, length = 50)
    private String id;

    @Column(name = "name", nullable = false, length = 100)
    private String name;

    @Column(name = "surname", nullable = false, length = 100)
    private String surname;

    @Column(name = "identification_type", length = 50)
    private String identificationType;

    @Column(name = "identification_value")
    private Long identificationValue;

    @Column(name = "birthdate", length = 20)
    private String birthdate;

    @Column(name = "gender", length = 20)
    private String gender;

    @Column(name = "email", unique = true, nullable = false, length = 255)
    private String email;

    @Column(name = "phone", length = 20)
    private String phone;

    @Column(name = "tax_identification_type", length = 50)
    private String taxIdentificationType;

    @Column(name = "tax_identification_value")
    private Long taxIdentificationValue;

    @Column(name = "nationality", length = 3)
    private String nationality;

    @Column(name = "tax_condition", length = 50)
    private String taxCondition;

    @Enumerated(EnumType.STRING)
    @Column(name = "status", nullable = false, length = 20)
    private Status status = Status.PENDING;

    @Column(name = "operation_country", length = 3)
    private String operationCountry;
    
    @Column(name = "id_empresa")
    private Long idEmpresa;
    
    @Column(nullable = false)
    @JsonIgnore
    private String password;

    @Column(name = "is_password_temporary", nullable = false, length = 255)
    @JsonIgnore
    private boolean isPasswordTemporary = false;
    
    @Column(name = "id_account", length = 50)
    private String idAccount;
    
    public User(UserData user) {
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
    	this.status = Status.valueOf(user.getStatus().toUpperCase());
    	this.operationCountry = user.getOperationCountry();
    }

}
