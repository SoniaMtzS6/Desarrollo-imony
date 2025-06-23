package com.fisinter.card.model;

import java.util.List;

import com.fasterxml.jackson.annotation.JsonProperty;

import lombok.Getter;
import lombok.Setter;

@Getter
@Setter
public class Meta {
	
	@JsonProperty("pagination")
    private Pagination pagination;
    
	@JsonProperty("filters")
    private List<Object> filters;
	
}

@Getter
@Setter
class Pagination {
	
	@JsonProperty("total_pages")
    private int totalPages;
    
	@JsonProperty("current_page")
    private int currentPage;
    
}
