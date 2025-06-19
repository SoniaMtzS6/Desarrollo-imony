package com.fisinter.user.service;

import java.security.SecureRandom;
import java.util.List;
import java.util.Map;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.PageRequest;
import org.springframework.data.domain.Sort;
import org.springframework.data.jpa.domain.Specification;
import org.springframework.http.HttpHeaders;
import org.springframework.http.HttpStatusCode;
import org.springframework.security.crypto.bcrypt.BCryptPasswordEncoder;
import org.springframework.stereotype.Service;
import org.springframework.web.reactive.function.client.WebClient;

import com.fisinter.user.component.UserSpecifications;
import com.fisinter.user.constants.Constants;
import com.fisinter.user.entity.Empresa;
import com.fisinter.user.entity.User;
import com.fisinter.user.entity.UserSimple;
import com.fisinter.user.enums.CurrencyEnum;
import com.fisinter.user.exception.TokenError;
import com.fisinter.user.exception.UserError;
import com.fisinter.user.model.AccountData;
import com.fisinter.user.model.AccountResponse;
import com.fisinter.user.model.UserData;
import com.fisinter.user.model.UserResponse;
import com.fisinter.user.model.UsersData;
import com.fisinter.user.repository.EmpresaRepository;
import com.fisinter.user.repository.PropertiesRepository;
import com.fisinter.user.repository.UserRepository;
import com.fisinter.user.repository.UserSimpleRepository;

import reactor.core.publisher.Mono;

@Service
public class UserService {
	
	private static final Logger log = LoggerFactory.getLogger(UserService.class);
	
	private String userUri;
	
	private final WebClient webClient;
    
    private final TokenService tokenService;
    
    private final EmpresaRepository empresaRepository;
    
    private final UserRepository userRepository;
    
    private final UserSimpleRepository userSimpleRepository;
    
    private final AccountService accountService;
    
    private static final String USER_URI = "user.uri";
    
    private static final String BEARER = "Bearer ";
    
    private static final String ERROR_MESSAGE = "Error [%d] - %s: %s";
    
    private static final String LOG_ERROR = "{} - {}";
    
    private final BCryptPasswordEncoder passwordEncoder = new BCryptPasswordEncoder();
    
    private static final String OWNER_TYPE = "USER";
    
    public UserService(WebClient webClient, TokenService tokenService,
    		PropertiesRepository propertiesRepository, UserRepository userRepository,
    		EmpresaRepository empresaRepository, UserSimpleRepository userSimpleRepository,
    		AccountService accountService) {
    	this.tokenService = tokenService;
    	this.webClient = webClient;
    	this.userRepository = userRepository;
    	this.empresaRepository = empresaRepository;
    	this.userSimpleRepository = userSimpleRepository;
    	this.accountService = accountService;
    	userUri = propertiesRepository.findByName(USER_URI).getValue();
    }
    
    public UserResponse createUser(UserData user) {
    	UserResponse response = new UserResponse();
    	if(null == user.getIdEmpresa()) {
    		log.error("El ID de la empresa no puede ser null o estar vacío.");
    		response.setError("El ID de la empresa no puede ser null o estar vacío. Por favor, proporcione un valor válido.");
    		return response;
    	}
    	Long idEmpresa = user.getIdEmpresa();
    	user.setIdEmpresa(null);
    	try {
    		Empresa empresa = empresaRepository.findByIdEmpresa(idEmpresa)
    		.orElseThrow(() -> new UserError(400, "Empresa no encontrada", "El ID de la empresa proporcionado no existe en el sistema."));
    		String token = tokenService.solicitarToken();
	    	response = webClient.post()
	    	.uri(userUri)
	    	.header(HttpHeaders.AUTHORIZATION, BEARER + token)
	    	.bodyValue(user)
	    	.retrieve()
	    	.onStatus(HttpStatusCode::is4xxClientError, responseLam -> 
	    		responseLam.bodyToMono(String.class).flatMap(error -> {
	    			int code = responseLam.statusCode().value();
	    			UserError createError = switch(code) {
	    				case 400 -> new UserError(code, Constants.BAD_REQUEST, error);
	    				case 403 -> new UserError(code, Constants.ACCESS_DENEID, error);
	    				case 401 -> new UserError(code, Constants.UNAUTHORIZED, error);
	    				default -> new UserError(code, Constants.UNEXPECTED_ERROR, error);};
	    			
	    			String errorMessage = String.format(ERROR_MESSAGE, code, createError.getMsg(), error);
	    			log.error(errorMessage);
	    			return Mono.error(createError);
	    		}))
			.bodyToMono(UserResponse.class).doOnSuccess(createResponse -> {
				User tmp = new User(createResponse.getData());
				tmp.setIdL(0L);
				tmp.setIdEmpresa(empresa.getIdEmpresa());
				
				String tempPassword = pacreateTemporalPass();
//				String encryptedPassword = passwordEncoder.encode(tempPassword);
				tmp.setPassword(tempPassword);
				tmp.setPasswordTemporary(true);
				
				userRepository.save(tmp);
				log.info(Constants.SUCCESSFUL_CREATE);
			})
			.doOnError(error -> log.error(Constants.UNSUCCESSFUL_CREATE))
			.block();
	    	
	    	createAccountForUser(token, response);
		} catch (UserError e) {
			log.error(LOG_ERROR, e.getCode(), e.getMsg());
			response.setError(e.getMsg());
		} catch (TokenError e) {
			log.error(LOG_ERROR, e.getCode(), e.getMsg());
			response.setError(e.getMsg());
		}
    	return response;
    }

	private void createAccountForUser(String token, UserResponse userResponse) {
		if(userResponse == null || userResponse.getData() == null ) {
			log.info(Constants.ACCOUNT_UNSUCCESSFUL_CREATE);
		}
		AccountResponse account = accountService.createAccount(token, new AccountData(OWNER_TYPE, userResponse.getData().getId(),
				userResponse.getData().getNationality(), CurrencyEnum.MXN.name()));
		if(account!= null && account.getData() != null) {
			userResponse.getData().setIdAccount(account.getData().getId());
			log.info(Constants.ACCOUNT_SUCCESSFUL_CREATE);
		}
	}

	public UserResponse partialUpdateUserById(String id, UserData user) {
		UserResponse response = new UserResponse();
    	try {
    		String token = tokenService.solicitarToken();
	    	response = webClient.patch()
	    	.uri(userUri + "{id}", id)
	    	.header(HttpHeaders.AUTHORIZATION, BEARER + token)
	    	.bodyValue(user)
	    	.retrieve()
	    	.onStatus(HttpStatusCode::is4xxClientError, responseLam -> 
	    		responseLam.bodyToMono(String.class).flatMap(error -> {
	    			int code = responseLam.statusCode().value();
	    			UserError createError = switch(code) {
	    				case 400 -> new UserError(code, Constants.BAD_REQUEST, error);
	    				case 403 -> new UserError(code, Constants.ACCESS_DENEID, error);
	    				case 401 -> new UserError(code, Constants.UNAUTHORIZED, error);
	    				default -> new UserError(code, Constants.UNEXPECTED_ERROR, error);};
	    			
	    			String errorMessage = String.format(ERROR_MESSAGE, code, createError.getMsg(), error);
	    			log.error(errorMessage);
	    			return Mono.error(createError);
	    		}))
			.bodyToMono(UserResponse.class).doOnSuccess(createResponse -> {
				User tmp = new User(createResponse.getData());
				User aux = userRepository.findById(id)
				.orElseThrow(() -> new UserError(409, Constants.CONFLICT, Constants.UNSUCCESSFUL_UPDATE_LOCAL));
				tmp.setIdL(aux.getIdL());
				tmp.setIdEmpresa(aux.getIdEmpresa());
				userRepository.saveAndFlush(tmp);
				log.info(Constants.SUCCESSFUL_UPDATE);
			})
			.doOnError(error -> log.error(Constants.UNSUCCESSFUL_UPDATE))
			.block();
		} catch (UserError e) {
			log.error(LOG_ERROR, e.getCode(), e.getMsg());
			response.setError(e.getMsg());
		} catch (TokenError e) {
			log.error(LOG_ERROR, e.getCode(), e.getMsg());
			response.setError(e.getMsg());
		}
    	return response;
	}
	
	public UserResponse getUserById(String id) {
		UserResponse response = new UserResponse();
    	try {
    		String token = tokenService.solicitarToken();
	    	response = webClient.get()
	    	.uri(userUri + "{id}", id)
	    	.header(HttpHeaders.AUTHORIZATION, BEARER + token)
	    	.retrieve()
	    	.onStatus(HttpStatusCode::is4xxClientError, responseLam -> 
	    		responseLam.bodyToMono(String.class).flatMap(error -> {
	    			int code = responseLam.statusCode().value();
	    			UserError createError = switch(code) {
	    				case 400 -> new UserError(code, Constants.BAD_REQUEST, error);
	    				case 403 -> new UserError(code, Constants.ACCESS_DENEID, error);
	    				case 401 -> new UserError(code, Constants.UNAUTHORIZED, error);
	    				default -> new UserError(code, Constants.UNEXPECTED_ERROR, error);};
	    			
	    			String errorMessage = String.format(ERROR_MESSAGE, code, createError.getMsg(), error);
	    			log.error(errorMessage);
	    			return Mono.error(createError);
	    		}))
			.bodyToMono(UserResponse.class).doOnSuccess(createResponse -> log.info(Constants.SUCCESSFUL_SEARCH))
			.doOnError(error -> log.error("Error en busqueda de usuario."))
			.block();
		} catch (UserError e) {
			log.error(LOG_ERROR, e.getCode(), e.getMsg());
			response.setError(e.getMsg());
		} catch (TokenError e) {
			log.error(LOG_ERROR, e.getCode(), e.getMsg());
			response.setError(e.getMsg());
		}
    	return response;
	}
	
	public UserResponse blockUserById(String id) {
		UserResponse response = new UserResponse();
		UserData user = new UserData();
		user.setStatus(Constants.STATUS_BLOCKED);
		user.setStatusReason(Constants.STATUS_REASON);
    	try {
    		String token = tokenService.solicitarToken();
			response = webClient.patch()
	    	.uri(userUri + "{id}", id)
	    	.header(HttpHeaders.AUTHORIZATION, BEARER + token)
	    	.bodyValue(user)
	    	.retrieve()
	    	.onStatus(HttpStatusCode::is4xxClientError, responseLam -> 
	    		responseLam.bodyToMono(String.class).flatMap(error -> {
	    			int code = responseLam.statusCode().value();
	    			UserError createError = switch(code) {
	    				case 400 -> new UserError(code, Constants.BAD_REQUEST, error);
	    				case 403 -> new UserError(code, Constants.ACCESS_DENEID, error);
	    				case 401 -> new UserError(code, Constants.UNAUTHORIZED, error);
	    				default -> new UserError(code, Constants.UNEXPECTED_ERROR, error);};
	    			
	    			String errorMessage = String.format(ERROR_MESSAGE, code, createError.getMsg(), error);
	    			log.error(errorMessage);
	    			return Mono.error(createError);
	    		}))
			.bodyToMono(UserResponse.class).doOnSuccess(createResponse -> {
				User tmp = new User(createResponse.getData());
				User aux = userRepository.findById(id)
				.orElseThrow(() -> new UserError(409, Constants.CONFLICT, Constants.UNSUCCESSFUL_BLOCK_LOCAL));
				tmp.setIdL(aux.getIdL());
				tmp.setIdEmpresa(aux.getIdEmpresa());
				userRepository.save(tmp);
				log.info(Constants.SUCCESSFUL_BLOCK);
			})
			.doOnError(error -> log.error("Error en bloqueo de usuario."))
			.block();
		} catch (UserError e) {
			log.error(LOG_ERROR, e.getCode(), e.getMsg());
			response.setError(e.getMsg());
		} catch (TokenError e) {
			log.error(LOG_ERROR, e.getCode(), e.getMsg());
			response.setError(e.getMsg());
		}
    	return response;
	}
    
	public UserResponse getUserByIdV2(String id) {
		UserResponse response = new UserResponse();
		try {
			User user = userRepository.findById(id)
					.orElseThrow(() -> new UserError(400, Constants.BAD_REQUEST, Constants.UNSUCCESSFUL_SEARCH));
			response.setData(new UserData(user));
		} catch (UserError e) {
			log.error(LOG_ERROR, e.getCode(), e.getMsg());
			response.setError(e.getMsg());
		}
		return response;
	}
	
	public UsersData getUsersByFiltersPaginationAndSortingV2(Map<String, String> filter, String sort, int page, int size) {
		UsersData users = new UsersData();
		Specification<User> spec = Specification.where(null);
		for (Map.Entry<String, String> entry : filter.entrySet()) {
		    String key = entry.getKey();
		    String[] aux = entry.getValue().split(",");
            switch(extractContentWithinBrackets(key)) {
            	case "id":
            		spec = spec.and(UserSpecifications.idIn(aux));
            		break;
                case "name":
                    spec = spec.and(UserSpecifications.nameIn(aux));
                    break;
                case "surname":
                	spec = spec.and(UserSpecifications.surnameIn(aux));
                    break;
                case "identificationType":
                	spec = spec.and(UserSpecifications.identificationTypeIn(aux));
                    break;
                case "identificationValue":
                	spec = spec.and(UserSpecifications.identificationValueIn(parseLongArray(aux)));
                    break;
                case "birthdate":
                	spec = spec.and(UserSpecifications.birthdateIn(aux));
                    break;
                case "gender":
                	spec = spec.and(UserSpecifications.genderIn(aux));
                    break;
                case "email":
                	spec = spec.and(UserSpecifications.emailIn(aux));
                    break;
                case "phone":
                	spec = spec.and(UserSpecifications.phoneIn(aux));
                    break;
                case "taxIdentificationType":
                	spec = spec.and(UserSpecifications.taxIdentificationTypeIn(aux));
                    break;
                case "taxIdentificationValue":
                	spec = spec.and(UserSpecifications.taxIdentificationValueIn(parseLongArray(aux)));
                    break;
                case "nationality":
                	spec = spec.and(UserSpecifications.nationalityIn(aux));
                    break;
                case "taxCondition":
                	spec = spec.and(UserSpecifications.taxConditionIn(aux));
                    break;
                case "status":
                	spec = spec.and(UserSpecifications.statusIn(aux));
                    break;
                case "operationCountry":
                	spec = spec.and(UserSpecifications.operationCountryIn(aux));
                    break;
                case "idEmpresa":
                	spec = spec.and(UserSpecifications.idEmpresaIn(parseLongArray(aux)));
                    break;
                default:
                    break;
            }
        }
        Sort sortOrder = parseSort(sort);
        PageRequest pageRequest = PageRequest.of(page, size, sortOrder);
        Page<User> tmp = userRepository.findAll(spec, pageRequest);
        users.setData(tmp);
        return users;
	}
	
    private Long[] parseLongArray(String[] values) {
        Long[] longValues = new Long[values.length];
        for (int i = 0; i < values.length; i++) {
            longValues[i] = Long.parseLong(values[i]);
        }
        return longValues;
    }
    
    private Sort parseSort(String sort) {
        Sort sortOrder = Sort.unsorted();
        if (sort != null && !sort.isEmpty()) {
            String[] sortParams = sort.split(",");
            for (String sortParam : sortParams) {
                String field = sortParam.startsWith("-") ? sortParam.substring(1) : sortParam;
                Sort.Order order = sortParam.startsWith("-") ? Sort.Order.desc(field) : Sort.Order.asc(field);
                sortOrder = sortOrder.and(Sort.by(order));
            }
        }
        return sortOrder;
    }
    
    public static String extractContentWithinBrackets(String input) {
        return input.replaceAll(".*\\[(.*)\\].*", "$1");
    }

	public List<UserSimple> getUsersSimple() {
//		return userSimpleRepository.findAll();
		return userSimpleRepository.findAll(Sort.by(Sort.Order.asc("idL")));
	}
	
	private String pacreateTemporalPass() {
		int length = 10;
		String charPool = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()-_+=";
        SecureRandom random = new SecureRandom();
        StringBuilder password = new StringBuilder(length);
        for (int i = 0; i < length; i++) {
            password.append(charPool.charAt(random.nextInt(charPool.length())));
        }
        return password.toString();
	}
    
}
