package com.fisinter.user.exception;

import lombok.AllArgsConstructor;
import lombok.Getter;
import lombok.Setter;

@AllArgsConstructor
@Getter
@Setter
public class TokenError extends RuntimeException {
	
	private static final long serialVersionUID = 3665938217159530605L;

	private final int code;
	
	private final String msg;
	
    private final String description;

}
