package com.fisinter.user.repository;

import org.springframework.data.jpa.repository.JpaRepository;

import com.fisinter.user.entity.UserSimple;

public interface UserSimpleRepository extends JpaRepository<UserSimple, Long> {

}
