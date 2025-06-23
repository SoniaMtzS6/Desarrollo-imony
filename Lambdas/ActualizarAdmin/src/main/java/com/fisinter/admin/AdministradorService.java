package com.fisinter.admin;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.*;

public class AdministradorService {

    private Connection connection;

    public AdministradorService () throws SQLException {
        this.connection = DBConnection.getConnection();
    }

    /**
     * Metodo para actualizar un registro en la tabla administradores
     * @param administrador
     * @throws SQLException
     */
    public void actualizar(Map<String, Object> input) throws SQLException {
        int id = (Integer) input.get("id");
        input.remove("id");

        Set<String> keys = input.keySet();

        if (buscarPorId(id)){
            String query = buildQuery(keys);

            try (PreparedStatement preparedStatement = connection.prepareStatement(query)){
                int index = 1;
                for (String key : keys) {
                    if (!key.equals("id")) {
                        if (key.equals("image")) {
                            preparedStatement.setBytes(index, (byte[]) input.get(key));
                            index++;
                        }else{
                            preparedStatement.setString(index, input.get(key).toString());
                            index++;
                        }

                    }

                }

                // Finalmente asignamos el valor del id
                preparedStatement.setInt(index, id);

                preparedStatement.executeUpdate();
            }
        }else{
            throw new RuntimeException("Error: No existe registro");
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

    private String buildQuery(Set<String> keys) {
        StringBuilder query = new StringBuilder("UPDATE administradores SET ");
        for (String item : keys) {
            if (!item.equals("id")) {
                query.append(item).append(" = ?,");
            }
        }
        // Eliminar la última coma
        query.setLength(query.length() - 1);
        query.append(" WHERE id = ?");
        return query.toString();
    }
}
