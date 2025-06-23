package com.fisinter.card.service;

import java.util.Map;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.http.HttpHeaders;
import org.springframework.http.HttpStatusCode;
import org.springframework.stereotype.Service;
import org.springframework.web.reactive.function.client.WebClient;

import com.fisinter.card.constants.Constants;
import com.fisinter.card.exception.CardError;
import com.fisinter.card.exception.TokenError;
import com.fisinter.card.model.CardData;
import com.fisinter.card.model.CardResponse;
import com.fisinter.card.model.CardsData;
import com.fisinter.card.repository.PropertiesRepository;

import jakarta.validation.Valid;
import reactor.core.publisher.Mono;

@Service
public class CardService {
	
	private static final Logger log = LoggerFactory.getLogger(CardService.class);
	
	private String cardUri;
	
	private final WebClient webClient;
	
	private final TokenService tokenService;
	
	private static final String BEARER = "Bearer ";
	
	private static final String ERROR_MESSAGE = "Error [%d] - %s: %s";
	
	private static final String LOG_ERROR = "{} - {}";
	
	public CardService(WebClient webClient, TokenService tokenService,
			PropertiesRepository propertiesRepository) {
		this.tokenService = tokenService;
		this.webClient = webClient;
		cardUri = propertiesRepository.findByName("card.uri").getValue();
	}
	
	public CardResponse createCard(@Valid CardData card) {
		CardResponse response = new CardResponse();
		try {
			String token = tokenService.solicitarToken();
			response = webClient.post()
					.uri(cardUri)
					.header(HttpHeaders.AUTHORIZATION, BEARER + token)
					.bodyValue(card)
					.retrieve()
					.onStatus(HttpStatusCode::is4xxClientError, responseLam -> 
					responseLam.bodyToMono(String.class).flatMap(error -> {
		    			int code = responseLam.statusCode().value();
		    			CardError createError = switch(code) {
		    				case 400 -> new CardError(code, Constants.BAD_REQUEST, error);
		    				case 403 -> new CardError(code, Constants.ACCESS_DENEID, error);
		    				case 401 -> new CardError(code, Constants.UNAUTHORIZED, error);
		    				default -> new CardError(code, Constants.UNEXPECTED_ERROR, error);};
		    			
		    			String errorMessage = String.format(ERROR_MESSAGE, code, createError.getMsg(), error);
		    			log.error(errorMessage);
		    			return Mono.error(createError);
		    		}))
					.bodyToMono(CardResponse.class).doOnSuccess(createResponse -> log.info(Constants.SUCCESSFUL_CREATE))
					.doOnError(error -> log.error(Constants.UNSUCCESSFUL_CREATE))
					.block();
		} catch(CardError e) {
			log.error(LOG_ERROR, e.getCode(), e.getMsg());
			response.setError(e.getMsg());
		} catch(TokenError e) {
			log.error(LOG_ERROR, e.getCode(), e.getMsg());
			response.setError(e.getMsg());
		}
		return response;
	}
	
	public CardResponse partialUpdateCardById(String id, CardData card) {
		CardResponse response = new CardResponse();
    	try {
    		String token = tokenService.solicitarToken();
	    	response = webClient.patch()
	    	.uri(cardUri + "{id}", id)
	    	.header(HttpHeaders.AUTHORIZATION, BEARER + token)
	    	.bodyValue(card)
	    	.retrieve()
	    	.onStatus(HttpStatusCode::is4xxClientError, responseLam -> 
	    		responseLam.bodyToMono(String.class).flatMap(error -> {
	    			int code = responseLam.statusCode().value();
	    			CardError createError = switch(code) {
	    				case 400 -> new CardError(code, Constants.BAD_REQUEST, error);
	    				case 403 -> new CardError(code, Constants.ACCESS_DENEID, error);
	    				case 401 -> new CardError(code, Constants.UNAUTHORIZED, error);
	    				default -> new CardError(code, Constants.UNEXPECTED_ERROR, error);};
	    			
	    			String errorMessage = String.format(ERROR_MESSAGE, code, createError.getMsg(), error);
	    			log.error(errorMessage);
	    			return Mono.error(createError);
	    		}))
			.bodyToMono(CardResponse.class).doOnSuccess(createResponse -> log.info(Constants.SUCCESSFUL_UPDATE))
			.doOnError(error -> log.error(Constants.UNSUCCESSFUL_UPDATE))
			.block();
		} catch (CardError e) {
			log.error(LOG_ERROR, e.getCode(), e.getMsg());
			response.setError(e.getMsg());
		} catch (TokenError e) {
			log.error(LOG_ERROR, e.getCode(), e.getMsg());
			response.setError(e.getMsg());
		}
    	return response;
	}
	
	public CardsData getCardsByFiltersPaginationAndSorting(Map<String, String> params) {
		CardsData response = new CardsData();
		String uri = buildUri(params);
		try {
			String token = tokenService.solicitarToken();
			response = webClient.get()
			    	.uri(uri)
			    	.header(HttpHeaders.AUTHORIZATION, BEARER + token)
			    	.retrieve()
			    	.onStatus(HttpStatusCode::is4xxClientError, responseLam -> 
			    		responseLam.bodyToMono(String.class).flatMap(error -> {
			    			int code = responseLam.statusCode().value();
			    			CardError cardError = switch(code) {
			    				case 400 -> new CardError(code, "Bad Request.", error);
			    				case 403 -> new CardError(code, "Access Denied.", error);
			    				case 401 -> new CardError(code, "Unauthorized.", error);
			    				default -> new CardError(code, "Unexpected error.", error);};
			    			
			    			String errorMessage = String.format("Error [%d] - %s: %s", code, cardError.getMsg(), error);
			    			log.error(errorMessage);
			    			return Mono.error(cardError);
			    		}))
					.bodyToMono(CardsData.class).doOnSuccess(createResponse -> log.info("Usuario encontrado."))
					.doOnError(error -> log.error("Error en busqueda de usuario."))
					.block();
		}catch(CardError e) {
			log.error(LOG_ERROR, e.getCode(), e.getMsg());
			response.setError(e.getMsg());
		}catch (TokenError e) {
			log.error(LOG_ERROR, e.getCode(), e.getMsg());
			response.setError(e.getMsg());
		}
		return response;		
	}
	
	private String buildUri(Map<String, String> params) {
		StringBuilder uri = new StringBuilder(cardUri + "?");
		String sort = params.getOrDefault("sort", null);
		String filter = params.entrySet()
		        .stream()
		        .filter(entry -> entry.getKey().contains("filter"))
		        .map(entry -> entry.getKey() + "=" + entry.getValue())
		        .findFirst()
		        .orElse(null);
		
		if (filter != null) {
            uri.append(filter).append("&");
        }
		
		if (sort != null) {
            uri.append("sort=").append(sort).append("&");
        }
		uri.append("page[number]=").append(params.getOrDefault("page[number]", "0")).append("&");
		uri.append("page[size]=").append(params.getOrDefault("page[size]", "5"));
		
		return uri.toString();
	}
	
	public CardResponse blockCardById(String id) {
		CardResponse response = new CardResponse();
		CardData card = new CardData();
		card.setStatus(Constants.STATUS_BLOCKED);
		card.setStatusReason(Constants.STATUS_BLOCKED_DISABLED_REASON_USER);
    	try {
    		String token = tokenService.solicitarToken();
			response = webClient.patch()
	    	.uri(cardUri + "{id}", id)
	    	.header(HttpHeaders.AUTHORIZATION, BEARER + token)
	    	.bodyValue(card)
	    	.retrieve()
	    	.onStatus(HttpStatusCode::is4xxClientError, responseLam -> 
	    		responseLam.bodyToMono(String.class).flatMap(error -> {
	    			int code = responseLam.statusCode().value();
	    			CardError createError = switch(code) {
	    				case 400 -> new CardError(code, Constants.BAD_REQUEST, error);
	    				case 403 -> new CardError(code, Constants.ACCESS_DENEID, error);
	    				case 401 -> new CardError(code, Constants.UNAUTHORIZED, error);
	    				default -> new CardError(code, Constants.UNEXPECTED_ERROR, error);};
	    			
	    			String errorMessage = String.format(ERROR_MESSAGE, code, createError.getMsg(), error);
	    			log.error(errorMessage);
	    			return Mono.error(createError);
	    		}))
			.bodyToMono(CardResponse.class).doOnSuccess(createResponse -> log.info(Constants.SUCCESSFUL_BLOCK))
			.doOnError(error -> log.error("Error en bloqueo de usuario."))
			.block();
		} catch (CardError e) {
			log.error(LOG_ERROR, e.getCode(), e.getMsg());
			response.setError(e.getMsg());
		} catch (TokenError e) {
			log.error(LOG_ERROR, e.getCode(), e.getMsg());
			response.setError(e.getMsg());
		}
    	return response;
	}

	public CardResponse disableCardById(String id) {
		CardResponse response = new CardResponse();
		CardData card = new CardData();
		card.setStatus(Constants.STATUS_DISABLED);
		card.setStatusReason(Constants.STATUS_BLOCKED_DISABLED_REASON_USER);
    	try {
    		String token = tokenService.solicitarToken();
			response = webClient.patch()
	    	.uri(cardUri + "{id}", id)
	    	.header(HttpHeaders.AUTHORIZATION, BEARER + token)
	    	.bodyValue(card)
	    	.retrieve()
	    	.onStatus(HttpStatusCode::is4xxClientError, responseLam -> 
	    		responseLam.bodyToMono(String.class).flatMap(error -> {
	    			int code = responseLam.statusCode().value();
	    			CardError createError = switch(code) {
	    				case 400 -> new CardError(code, Constants.BAD_REQUEST, error);
	    				case 403 -> new CardError(code, Constants.ACCESS_DENEID, error);
	    				case 401 -> new CardError(code, Constants.UNAUTHORIZED, error);
	    				default -> new CardError(code, Constants.UNEXPECTED_ERROR, error);};
	    			
	    			String errorMessage = String.format(ERROR_MESSAGE, code, createError.getMsg(), error);
	    			log.error(errorMessage);
	    			return Mono.error(createError);
	    		}))
			.bodyToMono(CardResponse.class).doOnSuccess(createResponse -> log.info(Constants.SUCCESSFUL_BLOCK))
			.doOnError(error -> log.error("Error en bloqueo de usuario."))
			.block();
		} catch (CardError e) {
			log.error(LOG_ERROR, e.getCode(), e.getMsg());
			response.setError(e.getMsg());
		} catch (TokenError e) {
			log.error(LOG_ERROR, e.getCode(), e.getMsg());
			response.setError(e.getMsg());
		}
    	return response;
	}

	public CardResponse disableCompanieCardsById(String id) {
		CardResponse response = new CardResponse();
		
		//Recuperar todas las card id de la bd de la aplicación
		
		//bloquear todas las tarjetas en pomelo API(realizarlo de manera asincrona)
		return null;
	}

}
