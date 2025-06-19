package com.fisinter.admin.crear;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;

public class AdministradorService {

    private Connection connection;

    public AdministradorService () throws SQLException {
        this.connection = DBConnection.getConnection();
    }

    /**
     * Metodo para crear Administradores
     * @param  administrador
     * @throws SQLException
     */
    public void crear(Administrador administrador) throws SQLException{
        PassGen generador = new PassGen();
        String psswd = generador.generatePassword();
        String body = "Te compartimos el password temporal: "+psswd;

        String query = "INSERT INTO administradores(nombre, email, telefono, password, perfil, idEmpresa, direccion, dato_extra, image, activo, fecha_creacion, is_password_temporary) values(?,?,?,?,?,?,?,?,?,?,now(), true)";

        EmailService emailService = new EmailService();
        try (PreparedStatement preparedStatement = connection.prepareStatement(query)){
            preparedStatement.setString(1, administrador.getNombre());
            preparedStatement.setString(2, administrador.getEmail());
            preparedStatement.setString(3, administrador.getTelefono());
            preparedStatement.setString(4, psswd);
            preparedStatement.setString(5, administrador.getPerfil());
            preparedStatement.setInt(6, administrador.getIdEmpresa());
            preparedStatement.setString(7, administrador.getDireccion());
            preparedStatement.setString(8, administrador.getDatoExtra());
            preparedStatement.setBytes(9, administrador.getImage());
            preparedStatement.setBoolean(10, true);
            preparedStatement.execute();

            emailService.sendEmail(administrador.getEmail(), body);
        }
    }

    /**
     * Metodo para buscar si existe un registro con idEmpresa
     * @param idEmpresa
     * @return retorna un objeto de tipo administrador
     * @throws SQLException
     */
    public boolean validateAdminIdEmpresa(Integer idEmpresa) throws SQLException {
        String countQuery = "SELECT COUNT(*) FROM administradores WHERE idEmpresa = ?";

        try (PreparedStatement ps = connection.prepareStatement(countQuery)){
            ps.setInt(1,idEmpresa);

            try (ResultSet rs = ps.executeQuery()){
                if (rs.next() && rs.getInt(1) == 0){
                    return false;
                }else {
                    return true;
                }
            }
        }
    }

    /**
     * Metodo para buscar un registro en la tabla empresas
     * @param id
     * @return retorna un objeto de tipo administrador
     * @throws SQLException
     */
    public boolean buscarPorId(Integer id) throws SQLException {
        String countQuery = "SELECT COUNT(*) FROM empresas WHERE ID_EMPRESA = ?";

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
