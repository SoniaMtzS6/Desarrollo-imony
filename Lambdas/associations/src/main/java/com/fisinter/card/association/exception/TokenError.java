package com.fisinter.card.association.exception;

import lombok.AllArgsConstructor;
import lombok.Getter;
import lombok.Setter;

@AllArgsConstructor
@Getter
@Setter
public class TokenError extends RuntimeException {
	
	private static final long serialVersionUID = -8948878498829453760L;

	private final int code;
	
	private final String msg;
	
    private final String description;

}
