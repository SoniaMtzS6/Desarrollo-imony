package com.fisinter.card.association;

import org.springframework.boot.SpringApplication;
import org.springframework.boot.autoconfigure.SpringBootApplication;
import org.springframework.context.annotation.Import;

import com.fisinter.card.association.controller.AssociationController;

@SpringBootApplication
@Import({AssociationController.class})
public class AssociationApplication {

	public static void main(String[] args) {
		SpringApplication.run(AssociationApplication.class, args);
	}

}
