package com.fisinter.user.exception;

import lombok.AllArgsConstructor;
import lombok.Getter;
import lombok.Setter;

@AllArgsConstructor
@Getter
@Setter
public class UserError extends RuntimeException {
	
	private static final long serialVersionUID = 4373850969412581748L;

	private final int code;
	
	private final String msg;
	
	private final String description;

}
