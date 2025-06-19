package com.fisinter.user.entity;

import com.fasterxml.jackson.annotation.JsonInclude;

import jakarta.persistence.Column;
import jakarta.persistence.Entity;
import jakarta.persistence.FetchType;
import jakarta.persistence.Id;
import jakarta.persistence.JoinColumn;
import jakarta.persistence.MapsId;
import jakarta.persistence.OneToOne;
import jakarta.persistence.Table;
import lombok.Getter;
import lombok.NoArgsConstructor;
import lombok.Setter;

@Entity
@Getter
@Setter
@Table(name = "user_addres")
@JsonInclude(JsonInclude.Include.NON_NULL)
@NoArgsConstructor
public class UserAddress {
	
    @Id
    @Column(name = "user_id")
    private Long userId;

    @Column(name = "street_name", length = 255)
    private String streetName;

    @Column(name = "street_number")
    private Integer streetNumber;

    @Column(name = "floor")
    private Integer floor;

    @Column(name = "apartment", length = 10)
    private String apartment;

    @Column(name = "zip_code")
    private Integer zipCode;

    @Column(name = "neighborhood", length = 100)
    private String neighborhood;

    @Column(name = "city", length = 100)
    private String city;

    @Column(name = "region", length = 100)
    private String region;

    @Column(name = "additional_info", length = 255)
    private String additionalInfo;

    @Column(name = "country", length = 3)
    private String country;

    @OneToOne(fetch = FetchType.LAZY)
    @MapsId
    @JoinColumn(name = "user_id", referencedColumnName = "id_", unique = true)
    private User user;

}
