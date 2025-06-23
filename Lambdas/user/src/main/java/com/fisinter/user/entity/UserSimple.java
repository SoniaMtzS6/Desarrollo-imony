package com.fisinter.user.entity;

import com.fasterxml.jackson.annotation.JsonIgnore;
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

@Entity
@Getter
@Setter
@Table(name = "user")
@JsonInclude(JsonInclude.Include.NON_NULL)
@NoArgsConstructor
public class UserSimple {
	
	@Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "id_")
	@JsonIgnore
    private Long idL;

    @Column(name = "id", unique = true, nullable = false, length = 50)
    private String id;

    @Column(name = "name", nullable = false, length = 100)
    private String name;

}
