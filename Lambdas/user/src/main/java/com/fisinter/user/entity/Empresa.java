package com.fisinter.user.entity;

import com.fasterxml.jackson.annotation.JsonInclude;

import jakarta.persistence.Column;
import jakarta.persistence.Entity;
import jakarta.persistence.Id;
import jakarta.persistence.Table;
import lombok.Getter;
import lombok.Setter;

@Entity
@Getter
@Setter
@Table(name = "empresas")
@JsonInclude(JsonInclude.Include.NON_NULL)
public class Empresa {
	
	@Id
	@Column(name = "id_empresa")
    private Long idEmpresa;

}
