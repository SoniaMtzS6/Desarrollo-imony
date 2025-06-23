package com.fisinter.admin;

import org.junit.jupiter.api.BeforeEach;
import org.junit.jupiter.api.Test;
import org.mockito.InjectMocks;
import org.mockito.Mock;
import org.mockito.MockitoAnnotations;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.HashMap;
import java.util.Map;

import static org.mockito.ArgumentMatchers.anyString;
import static org.mockito.Mockito.*;

class AdministradorServiceTest {

    @Mock
    private Connection mockConnection;

    @Mock
    private PreparedStatement mockPreparedStatement;

    @Mock
    private ResultSet mockResultSet;

    @InjectMocks
    private AdministradorService administradorService;

    @BeforeEach
    void setUp() throws SQLException {
        MockitoAnnotations.openMocks(this);
        // Inyectar la conexión simulada
        //administradorService = new AdministradorService(mockConnection);
    }
/*
    @Test
    public void testActualizarSuccess() throws SQLException {
        // Mock del método buscarPorId (simulamos que encontró el administrador)
        when(mockConnection.prepareStatement(anyString())).thenReturn(mockPreparedStatement);
        when(mockPreparedStatement.executeQuery()).thenReturn(mockResultSet);
        when(mockResultSet.next()).thenReturn(true); // Administrador encontrado
        when(mockResultSet.getInt("id")).thenReturn(1);
        when(mockResultSet.getString("nombre")).thenReturn("John Doe");
        when(mockResultSet.getString("correo")).thenReturn("john@example.com");
        when(mockResultSet.getString("telefono")).thenReturn("123456789");

        // Simular la actualización
        Map<String, Object> input = new HashMap<>();
        input.put("id", 1);
        input.put("nombre", "Jane Doe");
        input.put("correo", "jane@example.com");

        // Simular el PreparedStatement de la actualización
        String updateQuery = "UPDATE administradores SET nombre = ?, correo = ? WHERE id = ?";
        when(mockConnection.prepareStatement(updateQuery)).thenReturn(mockPreparedStatement);

        // Llamamos al método actualizar
        administradorService.actualizar(input);

        // Verificar que se ejecutó la query de actualización
        verify(mockPreparedStatement, times(1)).executeUpdate();

        // Verificar que los parámetros fueron asignados correctamente
        verify(mockPreparedStatement, times(1)).setObject(1, "Jane Doe");
        verify(mockPreparedStatement, times(1)).setObject(2, "jane@example.com");
        verify(mockPreparedStatement, times(1)).setInt(3, 1); // El ID como último parámetro
    }*/
}