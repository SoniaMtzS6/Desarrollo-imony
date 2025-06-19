package com.fisinter.user.repository;

import java.util.List;
import java.util.Optional;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import org.springframework.data.jpa.repository.Modifying;
import org.springframework.data.jpa.repository.Query;
import org.springframework.stereotype.Repository;
import org.springframework.transaction.annotation.Transactional;

import com.fisinter.user.entity.User;
import com.fisinter.user.enums.Status;

@Repository
public interface UserRepository extends JpaRepository<User, Long>, JpaSpecificationExecutor<User> {
	
	Optional<User> findById(String id);
	
	Optional<List<User>> findAllByIdEmpresa(Long idEmpresa);
	
	Optional<List<User>> findAllByIdEmpresaAndStatus(Long idEmpresa, Status status); 
	
	@Transactional
	@Modifying
	@Query("UPDATE User u SET u.idAccount = :idAccount WHERE u.id = :id")
	int updateidAccountById(String id, String idAccount);

}
