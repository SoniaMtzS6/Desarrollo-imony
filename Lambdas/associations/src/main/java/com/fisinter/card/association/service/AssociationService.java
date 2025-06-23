package com.fisinter.card.association.service;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.http.HttpHeaders;
import org.springframework.http.HttpStatusCode;
import org.springframework.stereotype.Service;
import org.springframework.web.reactive.function.client.WebClient;

import com.fisinter.card.association.constants.Constants;
import com.fisinter.card.association.exception.AssociationError;
import com.fisinter.card.association.exception.TokenError;
import com.fisinter.card.association.model.CardData;
import com.fisinter.card.association.repository.PropertiesRepository;

import jakarta.validation.Valid;
import reactor.core.publisher.Mono;

@Service
public class AssociationService {
	
	private static final Logger log = LoggerFactory.getLogger(AssociationService.class);
	
	private String associationUri;
	
	private final WebClient webClient;
	
	private final TokenService tokenService;
	
	private static final String BEARER = "Bearer ";
	
	private static final String ERROR_MESSAGE = "Error [%d] - %s: %s";
	
	private static final String LOG_ERROR = "{} - {}";
	
	public AssociationService(WebClient webClient, TokenService tokenService,
			PropertiesRepository propertiesRepository) {
		this.tokenService = tokenService;
		this.webClient = webClient;
		associationUri = propertiesRepository.findByName("association.uri").getValue();
	}

	public CardData cardAssociation(@Valid CardData card) {
		CardData response = new CardData();
		try {
			String token = tokenService.solicitarToken();
			response = webClient.post()
					.uri(associationUri)
					.header(HttpHeaders.AUTHORIZATION, BEARER + token)
					.bodyValue(card)
					.retrieve()
					.onStatus(HttpStatusCode::is4xxClientError, responseLam -> 
					responseLam.bodyToMono(String.class).flatMap(error -> {
		    			int code = responseLam.statusCode().value();
		    			AssociationError createError = switch(code) {
		    				case 400 -> new AssociationError(code, Constants.BAD_REQUEST, error);
		    				case 403 -> new AssociationError(code, Constants.ACCESS_DENEID, error);
		    				case 401 -> new AssociationError(code, Constants.UNAUTHORIZED, error);
		    				default -> new AssociationError(code, Constants.UNEXPECTED_ERROR, error);};
		    			
		    			String errorMessage = String.format(ERROR_MESSAGE, code, createError.getMsg(), error);
		    			log.error(errorMessage);
		    			return Mono.error(createError);
		    		}))
					.bodyToMono(CardData.class).doOnSuccess(createResponse -> log.info(Constants.SUCCESSFUL_ASSOCIATION))
					.doOnError(error -> log.error(Constants.UNSUCCESSFUL_ASSOCIATION))
					.block();
		} catch(AssociationError e) {
			log.error(LOG_ERROR, e.getCode(), e.getMsg());
			response.setError(e.getMsg());
		} catch(TokenError e) {
			log.error(LOG_ERROR, e.getCode(), e.getMsg());
			response.setError(e.getMsg());
		}
		
		return response;
	}
	
}
