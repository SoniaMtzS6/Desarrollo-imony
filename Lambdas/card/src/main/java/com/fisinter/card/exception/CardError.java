package com.fisinter.card.exception;

import lombok.AllArgsConstructor;
import lombok.Getter;
import lombok.Setter;

@AllArgsConstructor
@Getter
@Setter
public class CardError extends RuntimeException {
	
	private static final long serialVersionUID = 2905607692851590712L;

	private final int code;
	
	private final String msg;
	
	private final String description;

}
