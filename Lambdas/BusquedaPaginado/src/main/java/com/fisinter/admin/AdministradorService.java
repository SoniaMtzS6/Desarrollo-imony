package com.fisinter.admin;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.List;

public class AdministradorService {

    private Connection connection;

    public AdministradorService () throws SQLException {
        this.connection = DBConnection.getConnection();
    }

    /**
     * Metodo para actualizar un registro en la tabla administradores
     * @param id
     * @throws SQLException
     */
    public List<Administrador> buscarConPaginacion(String filter, String value, int page, int size) throws SQLException {
        filter = filter.isEmpty()? "nombre": filter;
        // Construir la consulta dinámicamente con el filtro
        //String query = "SELECT * FROM administradores WHERE " + filter + " LIKE ? LIMIT ? OFFSET ?";
        //SELECT a.*, e.nombre AS empresa_nombre FROM administradores a JOIN empresa e ON a.empresa_id = e.id WHERE " + filter + " LIKE ? ORDER BY a.fecha_creacion DESC LIMIT ? OFFSET ?"
        //String query = "SELECT * FROM administradores WHERE " + filter + " LIKE ? ORDER BY fecha_creacion DESC LIMIT ? OFFSET ?";
        System.out.println("filter = " + filter);
        System.out.println("value = " + value);
        System.out.println("page = " + page);
        System.out.println("size = " + size);
        String query = "SELECT a.*, e.nombre_empresa AS nombre_empresa FROM administradores a JOIN empresas e ON a.idEmpresa = e.id_empresa WHERE " + filter + " LIKE ? ORDER BY a.id DESC LIMIT ? OFFSET ?";
        List<Administrador> administradores = new ArrayList<>();

        try (PreparedStatement preparedStatement = connection.prepareStatement(query)) {
            // Asignar los valores para el valor del filtro, límite y offset
            preparedStatement.setString(1, "%" + value + "%");
            preparedStatement.setInt(2, size);  // Tamaño de página (LIMIT)
            preparedStatement.setInt(3, (page - 1) * size);  // Offset

            ResultSet rs = preparedStatement.executeQuery();
            while (rs.next()) {
                Administrador administrador = new Administrador();
                administrador.setId(rs.getInt("id"));
                administrador.setNombre(rs.getString("nombre"));
                administrador.setEmail(rs.getString("email"));
                administrador.setTelefono(rs.getString("telefono"));
                administrador.setPerfil(rs.getString("perfil"));
                administrador.setNombreEmpresa(rs.getString("nombre_empresa"));
                administrador.setFechaCreacion(rs.getDate("fecha_creacion"));
                administrador.setActivo(rs.getBoolean("activo"));
                administrador.setDatoExtra(rs.getString("dato_extra"));
                administrador.setImage(rs.getBytes("image"));
                administradores.add(administrador);
            }
        }catch (SQLException e){
            e.getMessage();
            e.printStackTrace();
        }

        return administradores;
    }

    /**
     * Metodo para buscar un registro en la tabla administradores
     * @param id
     * @return retorna un objeto de tipo administrador
     * @throws SQLException
     */
    public int obtenerTotalRegistros(String filter, String value) throws SQLException {
        filter = filter.isEmpty()? "nombre": filter;
        String query = "SELECT COUNT(*) FROM administradores WHERE " + filter + " LIKE ?";

        try (PreparedStatement preparedStatement = connection.prepareStatement(query)) {
            preparedStatement.setString(1, "%" + value + "%");
            ResultSet rs = preparedStatement.executeQuery();
            if (rs.next()) {
                return rs.getInt(1);  // Devuelve el total de registros
            }
        }

        return 0;
    }

}
