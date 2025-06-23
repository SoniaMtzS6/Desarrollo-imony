package com.fisinter.admin;

import lombok.extern.slf4j.Slf4j;

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

    public Map<String, Object> valid(String email, String password) throws SQLException {
        String sql = "SELECT is_password_temporary FROM administradores WHERE email = ? AND password = ?";
        Map<String, Object> response = new HashMap<>();

        try (PreparedStatement ps = connection.prepareStatement(sql)) {
            ps.setString(1, email);
            ps.setString(2, password);

            try (ResultSet rs = ps.executeQuery()) {
                if (rs.next()) {
                    boolean isPasswordTemporary = rs.getBoolean("is_password_temporary");
                    response.put("isPasswordTemporary", isPasswordTemporary);
                    return response;
                }
            }
        } catch (SQLException e) {
            response.put("Error", "Database error: " + e.getMessage());
            e.printStackTrace();
        }

        response.put("Error", "Invalid credentials");
        return response;
    }
}
