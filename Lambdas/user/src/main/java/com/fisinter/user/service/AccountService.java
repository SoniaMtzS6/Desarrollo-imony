package com.fisinter.user.service;

import java.util.Optional;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.http.HttpHeaders;
import org.springframework.http.HttpStatusCode;
import org.springframework.stereotype.Component;
import org.springframework.web.reactive.function.client.ClientResponse;
import org.springframework.web.reactive.function.client.WebClient;

import com.fisinter.user.constants.Constants;
import com.fisinter.user.entity.Accounts;
import com.fisinter.user.entity.User;
import com.fisinter.user.exception.TokenError;
import com.fisinter.user.exception.UserError;
import com.fisinter.user.model.AccountData;
import com.fisinter.user.model.AccountResponse;
import com.fisinter.user.repository.AccountsRepository;
import com.fisinter.user.repository.PropertiesRepository;
import com.fisinter.user.repository.UserRepository;

import reactor.core.publisher.Mono;

@Component
public class AccountService {
	
	private static final Logger log = LoggerFactory.getLogger(AccountService.class);
	
    private static final String ACCOUNT_URI = "account.uri";
    private static final String BEARER = "Bearer ";
    private static final String ERROR_MESSAGE = "Error [%d] - %s: %s";
    private static final String LOG_ERROR = "{} - {}";
	
	private String accountUri;
	private final WebClient webClient;
	private final TokenService tokenService;
	private final UserRepository userRepository;
	private final AccountsRepository accountsRepository;

    public AccountService(WebClient webClient, TokenService tokenService,
    		PropertiesRepository propertiesRepository, 
    		AccountsRepository accountsRepository, 
    		UserRepository userRepository) {
    	
    	this.webClient = webClient;
    	this.tokenService = tokenService;
    	this.accountsRepository = accountsRepository;
    	this.userRepository = userRepository;
    	
    	this.accountUri = Optional.ofNullable(propertiesRepository.findByName(ACCOUNT_URI))
                .map(property -> property.getValue())
                .orElseThrow(() -> new IllegalArgumentException("Account URI not found in properties"));
    }
    
    /**
     * Creates an account for a given account data.
     *
     * @param data the account data to create
     * @return the account response
     */
    public AccountResponse createAccount(String token, AccountData data) {
        User user = userRepository.findById(data.getUserId())
                .orElseThrow(() -> new UserError(400, "Usuario no encontrado", "El ID del usuario no existe."));
        if(token == null || token.isBlank()) {
        	token = tokenService.solicitarToken();
        }
        AccountResponse response = createAccountPomelo(token, data);
        if (response != null && response.getData() != null) {
            saveAndLinkAccount(response.getData(), user);
        }
        return response;
    }
    
    
	/**
     * Saves an account and links it to the specified user.
     *
     * @param accountData the account data
     * @param user        the user
     */
    private void saveAndLinkAccount(AccountData accountData, User user) {
        Accounts account = mapToAccounts(accountData);
        account.setIdL(0L); // Resetting local ID
        account.setIdEmpresa(user.getIdEmpresa());

        accountsRepository.save(account);

        int rows = userRepository.updateidAccountById(user.getId(), account.getId());
        if (rows > 0) {
            log.info(Constants.SUCCESSFUL_ACCOUNT_LINKED);
        }
    }
    
    private AccountResponse createAccountPomelo(String token, AccountData data) {
        try {
            return webClient.post()
                    .uri(accountUri)
                    .header(HttpHeaders.AUTHORIZATION, BEARER + token)
                    .bodyValue(data)
                    .retrieve()
                    .onStatus(HttpStatusCode::is4xxClientError, response -> handleClientError(response))
                    .bodyToMono(AccountResponse.class)
                    .doOnSuccess(wsResponse -> log.info(Constants.ACCOUNT_POMELO_SUCCESSFUL_CREATE))
                    .doOnError(error -> log.error(Constants.ACCOUNT_POMELO_UNSUCCESSFUL_CREATE))
                    .block();
        } catch (UserError e) {
            log.error(LOG_ERROR, e.getCode(), e.getMsg());
            return createErrorResponse(e.getMsg());
        } catch (TokenError e) {
            log.error(LOG_ERROR, e.getCode(), e.getMsg());
            return createErrorResponse(e.getMsg());
        }
    }

    private Mono<Throwable> handleClientError(ClientResponse response) {
        return response.bodyToMono(String.class).flatMap(error -> {
            int code = response.statusCode().value();
            UserError userError = switch (code) {
                case 400 -> new UserError(code, Constants.BAD_REQUEST, error);
                case 403 -> new UserError(code, Constants.ACCESS_DENEID, error);
                case 401 -> new UserError(code, Constants.UNAUTHORIZED, error);
                default -> new UserError(code, Constants.UNEXPECTED_ERROR, error);
            };
            log.error(String.format(ERROR_MESSAGE, code, userError.getMsg(), error));
            return Mono.error(userError);
        });
    }
	
	private AccountResponse createErrorResponse(String errorMsg) {
        AccountResponse response = new AccountResponse();
        response.setError(errorMsg);
        return response;
    }

	 private Accounts mapToAccounts(AccountData data) {
	        Accounts account = new Accounts();
	        account.setId(data.getId());
	        account.setCountry(data.getCountry());
	        Optional.ofNullable(data.getData()).ifPresent(subData -> account.setLicenseOwner(subData.getLicenseOwner()));
	        account.setCurrency(data.getCurrency());
	        Optional.ofNullable(data.getMetadata()).ifPresent(metadata -> account.setDatosExtras((String) metadata));
	        account.setStatus(data.getStatus());
	        account.setOwnerType(data.getOwnerType());
	        Optional.ofNullable(data.getOwnerData()).ifPresent(ownerData -> {
	            account.setClientId(ownerData.getClientId());
	            account.setUserId(ownerData.getUserId());
	        });
	        account.setCreatedAt(data.getCreatedAt());
	        return account;
	    }

}