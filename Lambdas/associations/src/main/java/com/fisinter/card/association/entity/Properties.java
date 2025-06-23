package com.fisinter.card.association.entity;

import com.fasterxml.jackson.annotation.JsonInclude;

import jakarta.persistence.Column;
import jakarta.persistence.Entity;
import jakarta.persistence.GeneratedValue;
import jakarta.persistence.GenerationType;
import jakarta.persistence.Id;
import jakarta.persistence.Table;
import lombok.Getter;
import lombok.Setter;

@Entity
@Getter
@Setter
@Table(name = "properties")
@JsonInclude(JsonInclude.Include.NON_NULL)
public class Properties {
	
	@Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
	@Column(name = "ID")
    private Long id;
	
	@Column(name = "NAME")
	private String name;
	
	@Column(name = "VALUE")
	private String value;

}
