package com.fisinter.user.config;

import org.springframework.context.annotation.Bean;
import org.springframework.context.annotation.Configuration;
import org.springframework.web.reactive.function.client.WebClient;
import org.springframework.web.util.DefaultUriBuilderFactory;

import com.fisinter.user.repository.PropertiesRepository;

@Configuration
public class WebClientConfig {
	
	@Bean
    public WebClient webClient(PropertiesRepository propertiesRepository) {
		String baseUrl = propertiesRepository.findByName("base.url").getValue();
		DefaultUriBuilderFactory factory = new DefaultUriBuilderFactory(baseUrl);
		return WebClient.builder()
				.uriBuilderFactory(factory)
				.build();
    }

}
