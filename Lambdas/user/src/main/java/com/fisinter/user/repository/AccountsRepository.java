package com.fisinter.user.repository;

import java.util.List;
import java.util.Optional;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import org.springframework.stereotype.Repository;

import com.fisinter.user.entity.Accounts;

@Repository
public interface AccountsRepository extends JpaRepository<Accounts, Long>, JpaSpecificationExecutor<Accounts> {

	Optional<Accounts> findById(String id);
	
	Optional<List<Accounts>> findAllByIdEmpresa(Long idEmpresa);
}
