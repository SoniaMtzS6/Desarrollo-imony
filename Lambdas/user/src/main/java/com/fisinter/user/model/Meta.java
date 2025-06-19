package com.fisinter.user.model;

import java.util.List;

import com.fasterxml.jackson.annotation.JsonInclude;
import com.fasterxml.jackson.annotation.JsonProperty;
import com.fasterxml.jackson.annotation.JsonInclude.Include;

import lombok.Getter;
import lombok.Setter;

@Getter
@Setter
@JsonInclude(Include.NON_NULL)
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
