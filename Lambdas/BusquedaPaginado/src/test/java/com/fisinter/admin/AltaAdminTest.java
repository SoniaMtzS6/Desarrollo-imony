package com.fisinter.admin;

import com.amazonaws.services.lambda.runtime.Context;
import org.junit.jupiter.api.BeforeEach;
import org.mockito.Mock;
import org.mockito.MockitoAnnotations;


public class AltaAdminTest {

    private BusquedaAdmin altaAdmin;

    @Mock
    private AdministradorService mockAdministradorService;

    @Mock
    private Context mockContext;

    @BeforeEach
    public void setUp() {
        MockitoAnnotations.openMocks(this);
        altaAdmin = new BusquedaAdmin(mockAdministradorService);
    }

}