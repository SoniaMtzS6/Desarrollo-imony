package com.fisinter.admin;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.HashMap;
import java.util.Map;

public class UsersDAO {

    private Connection connection;

    public UsersDAO() throws SQLException {
        this.connection = DBConnection.getConnection();
    }

    public boolean validateCurrentPassword(String email, String password) throws SQLException {
        String sql = "SELECT COUNT(*) FROM administradores WHERE email = ? AND password = ?";
        Map<String, Object> response = new HashMap<>();

        try (PreparedStatement ps = connection.prepareStatement(sql)) {
            ps.setString(1, email);
            ps.setString(2, password);

            try (ResultSet rs = ps.executeQuery()) {
                if (rs.next() && rs.getInt(1) == 1) {
                    return true;
                }
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
        return false;
    }

    void updatePassword(String email, String newPassword) throws Exception {

        String query = "UPDATE administradores SET password = ? WHERE email = ?";
        try (PreparedStatement ps = connection.prepareStatement(query)) {

            ps.setString(1, newPassword);
            ps.setString(2, email);
            ps.executeUpdate();
        }
    }
}
