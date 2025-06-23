package com.fisinter.admin;

import com.amazonaws.services.lambda.runtime.Context;
import org.junit.jupiter.api.BeforeEach;
import org.junit.jupiter.api.Test;
import org.mockito.Mock;
import org.mockito.MockitoAnnotations;

import java.sql.SQLException;
import java.util.HashMap;
import java.util.Map;

import static org.junit.jupiter.api.Assertions.assertEquals;
import static org.mockito.ArgumentMatchers.any;
import static org.mockito.ArgumentMatchers.anyInt;
import static org.mockito.Mockito.doNothing;
import static org.mockito.Mockito.verify;

public class AltaAdminTest {

    private EliminarAdmin altaAdmin;

    @Mock
    private AdministradorService mockAdministradorService;

    @Mock
    private Context mockContext;

    @BeforeEach
    public void setUp() {
        MockitoAnnotations.openMocks(this);
        altaAdmin = new EliminarAdmin(mockAdministradorService);
    }

    @Test
    public void testHandleRequest() throws Exception {
        // Crear datos de entrada simulados
        Map<String, Object> input = new HashMap<>();
        input.put("id", 1);
        input.put("nombre", "Empresa Test");
        input.put("correo", "test@admin.com");
        input.put("telefono", "123456789");

        doNothing().when(mockAdministradorService).eliminar(anyInt());

        String result = altaAdmin.handleRequest(input,mockContext);

        assertEquals("Administrador eliminado correctamente", result);

        verify(mockAdministradorService).eliminar(anyInt());
    }

    @Test
    public void testHandleRequestMissingCorreo() {
        Map<String, Object> input = new HashMap<>();
        input.put("nombre", "Juan");
        input.put("telefono", "123456789");

        String result = altaAdmin.handleRequest(input, mockContext);

        assertEquals("Error: ID es necesario", result);
    }

}