package com.fisinter.settlements;

import org.springframework.boot.SpringApplication;
import org.springframework.boot.autoconfigure.SpringBootApplication;
import org.springframework.boot.autoconfigure.jdbc.DataSourceAutoConfiguration;
import org.springframework.context.annotation.Import;

import com.fisinter.settlements.controller.SettlementsController;

@SpringBootApplication(exclude = { DataSourceAutoConfiguration.class })
@Import({SettlementsController.class})
public class SettlementsApplication {

	public static void main(String[] args) {
		SpringApplication.run(SettlementsApplication.class, args);
	}

} 