package com.fisinter.admin;

import org.json.JSONObject;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.List;

public class AdministradorService {

    private final Connection connection;

    public AdministradorService () throws SQLException {
        this.connection = DBConnection.getConnection();
    }

    /**
     * Metodo para actualizar un registro en la tabla administradores
     * @param id
     * @throws SQLException
     */
    public void desactivar(Integer id) throws Exception {


        if (buscarPorId(id)){
            String query = "UPDATE administradores SET activo = ? WHERE id = ?";

            try (PreparedStatement preparedStatement = connection.prepareStatement(query)){
                preparedStatement.setBoolean(1, false);
                preparedStatement.setLong(2, id);

                preparedStatement.executeUpdate();
            }
        }else{
            throw new Exception("Error: No hay registros con ese ID");
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
                return !rs.next() || rs.getInt(1) != 0;
            }
        }
    }

}
