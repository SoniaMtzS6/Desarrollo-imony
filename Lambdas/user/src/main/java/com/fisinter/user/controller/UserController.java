package com.fisinter.user.controller;

import java.util.List;
import java.util.Map;

import org.springframework.http.HttpStatus;
import org.springframework.http.HttpStatusCode;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.DeleteMapping;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PatchMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.RestController;

import com.fisinter.user.entity.UserSimple;
import com.fisinter.user.model.UserData;
import com.fisinter.user.model.UserResponse;
import com.fisinter.user.model.UsersData;
import com.fisinter.user.service.DeletedUserService;
import com.fisinter.user.service.UserService;

import jakarta.validation.Valid;

@RestController
@RequestMapping("/user/api/v1")
public class UserController {

    private final UserService userService;
    
    private final DeletedUserService deletedUserService;
    
    public UserController(UserService userService, DeletedUserService deletedUserService) {
    	this.userService = userService;
    	this.deletedUserService = deletedUserService;
    }

    @PostMapping
    public ResponseEntity<UserResponse> createUser(@Valid @RequestBody UserData user) {
        UserResponse newUser = userService.createUser(user);
        HttpStatusCode status = null == newUser.getError() ? 
        		HttpStatus.CREATED : HttpStatus.BAD_REQUEST;
        return new ResponseEntity<>(newUser, status);
    }
    
    @PatchMapping("/{id}")
    public ResponseEntity<UserResponse> partialUpdateUserById(@PathVariable String id, @RequestBody UserData user) {
    	UserResponse updatedUser = userService.partialUpdateUserById(id, user);
    	HttpStatusCode status = null == updatedUser.getError() ? 
        		HttpStatus.OK : HttpStatus.BAD_REQUEST;
    	return new ResponseEntity<>(updatedUser, status);
    }
    
    @GetMapping("/{id}")
    public ResponseEntity<UserResponse> getUserById(@PathVariable String id) {
    	UserResponse user = userService.getUserByIdV2(id);
    	HttpStatusCode status = null == user.getError() ? 
        		HttpStatus.OK : HttpStatus.BAD_REQUEST;
        return new ResponseEntity<>(user, status);
    }
    
    @GetMapping
    public ResponseEntity<UsersData> getUsersByFiltersPaginationAndSorting(
    		@RequestParam Map<String, String> filter, @RequestParam(defaultValue = "0") int page,
    		@RequestParam(defaultValue = "5") int size, @RequestParam(required = false) String sort) {
    	filter.remove("sort");
    	filter.remove("page");
    	filter.remove("size");
    	UsersData users = userService.getUsersByFiltersPaginationAndSortingV2(filter, sort, page, size);
    	HttpStatusCode status = null == users.getError() ? 
        		HttpStatus.OK : HttpStatus.BAD_REQUEST;
    	return new ResponseEntity<>(users, status);
    }
    
    @PatchMapping("/block/{id}")
    public ResponseEntity<UserResponse> blockUserById(@PathVariable String id) {
    	UserResponse user = userService.blockUserById(id);
    	HttpStatusCode status = null == user.getError() ? 
        		HttpStatus.OK : HttpStatus.BAD_REQUEST;
    	return new ResponseEntity<>(user, status);
    }
    
    @DeleteMapping("/empresa/{idEmpresa}")
    public ResponseEntity<UserResponse> deleteUsersByIdEmpresa(@PathVariable Long idEmpresa) {
    	UserResponse response = deletedUserService.deleteUsersByIdEmpresa(idEmpresa);
    	HttpStatusCode status = null == response.getError() ? 
        		HttpStatus.OK : HttpStatus.BAD_REQUEST;
        return new ResponseEntity<>(response, status);
    }
    
    @DeleteMapping("/{id}")
    public ResponseEntity<UserResponse> deleteUserById(@PathVariable String id) {
    	UserResponse response = deletedUserService.deleteUserById(id);
    	HttpStatusCode status = null == response.getError() ? 
        		HttpStatus.OK : HttpStatus.BAD_REQUEST;
        return new ResponseEntity<>(response, status);
    }
    
    @GetMapping("/simple")
    public ResponseEntity<List<UserSimple>> getUsersSimple(){
    	List<UserSimple> response = userService.getUsersSimple();
        return new ResponseEntity<>(response, HttpStatus.OK);
    }

}
