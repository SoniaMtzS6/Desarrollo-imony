package com.fisinter.user.entity;

import com.fasterxml.jackson.annotation.JsonInclude;

import jakarta.persistence.Column;
import jakarta.persistence.Entity;
import jakarta.persistence.GeneratedValue;
import jakarta.persistence.GenerationType;
import jakarta.persistence.Id;
import jakarta.persistence.Table;
import lombok.Getter;
import lombok.NoArgsConstructor;
import lombok.Setter;

@Getter
@Setter
@NoArgsConstructor
@JsonInclude(JsonInclude.Include.NON_NULL)
@Entity
@Table(name = "accounts")
public class Accounts {
	
	@Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "id_")
    private Long idL;
	
	@Column(name = "id", unique = true, nullable = false, length = 50)
    private String id;
	
	@Column(name = "country", nullable = false, length = 3)
    private String country;
	
	@Column(name = "license_owner", length = 8)
    private String licenseOwner;
    
	@Column(name = "currency", nullable = false, length = 3)
    private String currency;
    
	@Column(name = "datos_extras", length = 100)
    private String datosExtras;
    
    @Column(name = "status", nullable = false, length = 20)
    private String status;
    
    @Column(name = "owner_type", length = 8)
    private String ownerType;
    
    @Column(name = "client_id", length = 50)
    private String clientId;
	
    @Column(name = "user_id", nullable = false, length = 50)
    private String userId;
	
	@Column(name = "company_id")
    private Long idEmpresa;
    
	@Column(name = "created_at", nullable = false, length = 50)
    private String createdAt;

}
