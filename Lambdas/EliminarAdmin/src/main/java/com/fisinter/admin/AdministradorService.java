package com.fisinter.admin;

import org.json.JSONObject;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.List;
import java.util.NoSuchElementException;

public class AdministradorService {

    private Connection connection;

    public AdministradorService () throws SQLException {
        this.connection = DBConnection.getConnection();
    }

    /**
     * Metodo para eliminar un registro de la tabla administradores
     * @param id
     * @throws SQLException
     */
    public void eliminar(Integer id) throws SQLException {
        if (id == null || id <= 0) {
            throw new IllegalArgumentException("El ID debe ser un valor positivo");
        }

        if (buscarPorId(id)) {
            String query = "DELETE FROM administradores WHERE id = ?";
            try (PreparedStatement preparedStatement = connection.prepareStatement(query)) {
                preparedStatement.setInt(1, id);
                int rowsAffected = preparedStatement.executeUpdate();

                if (rowsAffected == 0) {
                    throw new SQLException("Error al eliminar: No se afectaron filas.");
                }
            }
        } else {
            throw new NoSuchElementException("No existe el administrador con ID " + id);
        }
    }

    /**
     * Metodo para buscar un registro en la tabla administradores
     * @param id
     * @return retorna un objeto de tipo administrador
     * @throws SQLException
     */
    private boolean buscarPorId(Integer id) throws SQLException {
        String countQuery = "SELECT COUNT(*) FROM administradores WHERE ID = ?";

        try (PreparedStatement ps = connection.prepareStatement(countQuery)){
            ps.setInt(1,id);

            try (ResultSet rs = ps.executeQuery()){
                if (rs.next() && rs.getInt(1) == 0){
                    return false;
                }else {
                    return true;
                }
            }
        }
    }

}
