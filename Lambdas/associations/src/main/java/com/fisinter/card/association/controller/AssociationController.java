package com.fisinter.card.association.controller;

import org.springframework.http.HttpStatus;
import org.springframework.http.HttpStatusCode;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

import com.fisinter.card.association.model.CardData;
import com.fisinter.card.association.service.AssociationService;

import jakarta.validation.Valid;

@RestController
@RequestMapping("/card/association/api/v1")
public class AssociationController {
	
	private final AssociationService associationService;
	
	public AssociationController(AssociationService associationService) {
		this.associationService = associationService;
	}
	
	@PostMapping //Asociar tarjeta
	public ResponseEntity<CardData> cardAssociation(@Valid @RequestBody CardData card) {
		CardData newCard = associationService.cardAssociation(card);
		HttpStatusCode status = null == newCard.getError() ? 
        		HttpStatus.CREATED : HttpStatus.BAD_REQUEST;
		return new ResponseEntity<>(newCard, status);
	}
	
}
