package com.fisinter.card.association.repository;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;

import com.fisinter.card.association.entity.Properties;

@Repository
public interface PropertiesRepository extends JpaRepository<Properties, Long> {
	
	Properties findByName(String name);
	
}
