package com.fisinter.user.entity;

import java.time.LocalDateTime;

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
@Table(name = "deleted_users")
@JsonInclude(JsonInclude.Include.NON_NULL)
public class DeletedUsers {
	
	@Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
	@Column(name = "ID")
    private Long id;
	
	@Column(name = "id_user")
	private String idUser;
	
	@Column(name = "deleted")
	private boolean deleted;
	
	@Column(name = "NAME")
	private String name;
	
	@Column(name = "create_time")
    private LocalDateTime createTime;

}
