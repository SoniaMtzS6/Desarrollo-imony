package com.fisinter.user.service;

import java.util.List;
import java.util.Optional;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;

import com.fisinter.user.entity.Empresa;
import com.fisinter.user.entity.User;
import com.fisinter.user.enums.Status;
import com.fisinter.user.exception.UserError;
import com.fisinter.user.model.UserData;
import com.fisinter.user.model.UserResponse;
import com.fisinter.user.repository.DeletedUserRepository;
import com.fisinter.user.repository.EmpresaRepository;
import com.fisinter.user.repository.UserRepository;

@Service
public class DeletedUserService {
	
	private static final Logger log = LoggerFactory.getLogger(DeletedUserService.class);
	
	private final UserService userService;
	
	private final EmpresaRepository empresaRepository;

    private final DeletedUserRepository deletedUserRepository;
    
    private final UserRepository userRepository;
    
    private static final String LOG_ERROR = "{} - {}";

    @Autowired
    public DeletedUserService(UserService userService, DeletedUserRepository deletedUserRepository
    		, UserRepository userRepository, EmpresaRepository empresaRepository) {
    	this.userService = userService;
    	this.deletedUserRepository = deletedUserRepository;
    	this.userRepository = userRepository;
    	this.empresaRepository = empresaRepository;
    }

    public Boolean isUserDeleted(String idUser) {
        Optional<Boolean> deletedStatus = deletedUserRepository.findDeletedByIdUser(idUser);
        return deletedStatus.orElse(false);
    }

	public UserResponse deleteUsersByIdEmpresa(Long idEmpresa) {
		UserResponse response = new UserResponse();
		UserData data = new UserData();
		try {
			Empresa empresa = empresaRepository.findByIdEmpresa(idEmpresa)
					.orElseThrow(() -> new UserError(400, "Empresa no encontrada.", "El ID de la empresa proporcionado no existe en el sistema."));
			List<User> users = userRepository.findAllByIdEmpresaAndStatus(empresa.getIdEmpresa(), Status.ACTIVE)
					.orElseThrow(() -> new UserError(400, "Usuarios no encontrados.", "La empresa no cuenta con usuarios existentes en el sistema."));
			
			long count = users.stream()
	                  .filter(user -> userService.blockUserById(user.getId()).getError() != null)
	                  .count();
			if(users.isEmpty()) {
				data.setStatus("No hay usuarios activos registrados en la empresa especificada.");
			} else if(count == 0) {
				data.setStatus("Eliminación exitosa");
			} else {
				data.setStatus("Algunos usuarios fueron eliminados con éxito, pero hubo problemas con otros. Intente revisar los registros y proceder con las correcciones necesarias.");
			}
			response.setData(data);
		} catch(Exception e) {
			log.error(LOG_ERROR, "Delete user error.", e);
			response.setError("Error al eliminar usuarios. No se puede realizar la operación.");
		}
		return response;
	}
	
	public UserResponse deleteUserById(String id) {
		UserResponse response = new UserResponse();
		try {
			User user = userRepository.findById(id)
			.orElseThrow(() -> new UserError(400, "Usuario no encontrado.", "El usuario no cuenta existente en el sistema."));
			if(user.getStatus() != Status.ACTIVE && user.getStatus() == Status.BLOCKED) {
				response.setError("El usuario ya ha sido eliminado previamente.");
			} else {
				response = userService.blockUserById(id);
			}
		} catch(Exception e) {
			log.error(LOG_ERROR, "Delete user error.", e);
			response.setError("Error al eliminar usuario. No se puede realizar la operación.");
		}
		return response;
	}
	
}
