package com.fisinter.user.service;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.http.HttpStatusCode;
import org.springframework.stereotype.Service;
import org.springframework.web.reactive.function.client.WebClient;

import com.fisinter.user.constants.Constants;
import com.fisinter.user.exception.TokenError;
import com.fisinter.user.model.TokenRequest;
import com.fisinter.user.model.TokenResponse;
import com.fisinter.user.repository.PropertiesRepository;

import reactor.core.publisher.Mono;

@Service
public class TokenService {

	private static final Logger log = LoggerFactory.getLogger(TokenService.class);

	private final WebClient webClient;

	private String solicitarToken;
	
	private String clientId; 
	
	private String clientSecret;
	
	private String audience;
	
	private String grantType;

	public TokenService(WebClient webClient, PropertiesRepository propertiesRepository) {
		this.webClient = webClient;
		solicitarToken = propertiesRepository.findByName("token.uri.solicitar").getValue();
		clientId = propertiesRepository.findByName("client.id").getValue();
		clientSecret = propertiesRepository.findByName("client.secret").getValue();
		audience = propertiesRepository.findByName("audience").getValue();
		grantType = propertiesRepository.findByName("grant.type").getValue();
	}

	public String solicitarToken() {
		TokenRequest tokenRequest = new TokenRequest(clientId, clientSecret, audience, grantType);
		TokenResponse response = webClient.post()
				.uri(solicitarToken)
				.bodyValue(tokenRequest)
				.retrieve()
				.onStatus(HttpStatusCode::is4xxClientError,
						responseLam -> responseLam.bodyToMono(String.class).flatMap(error -> {
							int code = responseLam.statusCode().value();
							TokenError tokenError = switch(code) {
								case 400 -> new TokenError(code, Constants.BAD_REQUEST, error);
								case 403 -> new TokenError(code, Constants.ACCESS_DENEID, error);
								case 401 -> new TokenError(code, Constants.UNAUTHORIZED, error);
								default -> new TokenError(code, Constants.UNEXPECTED_ERROR, error);};
								
							String errorMessage = String.format("Error %d - %s: %s", code, tokenError.getMsg(), error);
							log.error(errorMessage);
							return Mono.error(tokenError);
						}))
				.bodyToMono(TokenResponse.class).doOnSuccess(tokenResponse -> log.info("Token generado exitosamente"))
				.doOnError(error -> log.error("Error en la solicitud del token")).block();

		return response != null ? response.getAccessToken() : null;
	}

}
