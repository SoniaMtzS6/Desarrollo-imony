package com.fisinter.user.repository;

import java.util.Optional;

import org.springframework.data.jpa.repository.JpaRepository;

import com.fisinter.user.entity.DeletedUsers;

public interface DeletedUserRepository extends JpaRepository<DeletedUsers, Long> {
	
	Optional<Boolean> findDeletedByIdUser(String idUser);

}
