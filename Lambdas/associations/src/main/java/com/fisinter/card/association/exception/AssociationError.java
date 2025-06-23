package com.fisinter.card.association.exception;

import lombok.AllArgsConstructor;
import lombok.Getter;
import lombok.Setter;

@AllArgsConstructor
@Getter
@Setter
public class AssociationError extends RuntimeException {
	
	private static final long serialVersionUID = -5884319682781411504L;

	private final int code;
	
	private final String msg;
	
    private final String description;

}
