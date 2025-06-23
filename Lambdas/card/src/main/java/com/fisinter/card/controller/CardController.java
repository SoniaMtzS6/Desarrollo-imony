package com.fisinter.card.controller;

import java.util.Map;

import org.springframework.http.HttpStatus;
import org.springframework.http.HttpStatusCode;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PatchMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.RestController;

import com.fisinter.card.model.CardData;
import com.fisinter.card.model.CardResponse;
import com.fisinter.card.model.CardsData;
import com.fisinter.card.service.CardService;

import jakarta.validation.Valid;

@RestController
@RequestMapping("/card/api/v1")
public class CardController {
	
	private final CardService cardService;
	
	public CardController(CardService cardService) {
		this.cardService = cardService;
	}
	
	@PostMapping //Nueva tarjeta
	public ResponseEntity<CardResponse> createCard(@Valid @RequestBody CardData card){
		CardResponse newCard = cardService.createCard(card);
		HttpStatusCode status = null == newCard.getError() ? 
        		HttpStatus.CREATED : HttpStatus.BAD_REQUEST;
		return new ResponseEntity<>(newCard, status);
	}
	
	@PatchMapping("/{id}") //Editar Tarjeta
	public ResponseEntity<CardResponse> partialUpdateUserById(@PathVariable String id, @RequestBody CardData card) {
    	CardResponse updatedCard = cardService.partialUpdateCardById(id, card);
    	HttpStatusCode status = null == updatedCard.getError() ? 
        		HttpStatus.OK : HttpStatus.BAD_REQUEST;
    	return new ResponseEntity<>(updatedCard, status);
    }
	
	@GetMapping //Listar tarjetas, listar tarjetas con paginacion
    public ResponseEntity<CardsData> getCardsByFiltersPaginationAndSorting(@RequestParam Map<String, String> params) {    	
    	CardsData cards = cardService.getCardsByFiltersPaginationAndSorting(params);
    	HttpStatusCode status = null == cards.getError() ? 
        		HttpStatus.OK : HttpStatus.BAD_REQUEST;
		return new ResponseEntity<>(cards, status);
    }
	
	@PatchMapping("/block/{id}") //Bloquear tarjeta
	public ResponseEntity<CardResponse> blockCardById(@PathVariable String id) {
		CardResponse card = cardService.blockCardById(id);
		HttpStatusCode status = null == card.getError() ? 
        		HttpStatus.OK : HttpStatus.BAD_REQUEST;
    	return new ResponseEntity<>(card, status);
    }
	
	@PatchMapping("/disable/{id}") //Desactivar tarjeta
	public ResponseEntity<CardResponse> disableCardById(@PathVariable String id) {
    	CardResponse card = cardService.disableCardById(id);
    	HttpStatusCode status = null == card.getError() ? 
        		HttpStatus.OK : HttpStatus.BAD_REQUEST;
    	return new ResponseEntity<>(card, status);
    }
	
	@PatchMapping("/disable/companie/{id}") // Desactivar todas las tarjetas de una empresa
	public ResponseEntity<CardResponse> disableCompaniCardsById(@PathVariable String id) {
		CardResponse cards = cardService.disableCompanieCardsById(id);
		HttpStatusCode status = null == cards.getError() ?
				HttpStatus.OK : HttpStatus.BAD_REQUEST;
		return new ResponseEntity<>(cards, status);
	}

}
