package com.fisinter.user.repository;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;

import com.fisinter.user.entity.Properties;

@Repository
public interface PropertiesRepository extends JpaRepository<Properties, Long> {
	
	Properties findByName(String name);
	
}
