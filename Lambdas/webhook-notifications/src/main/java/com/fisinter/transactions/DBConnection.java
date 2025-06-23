package com.fisinter.transactions;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;
import java.util.Properties;

public class DBConnection {

    static Connection connection;

    public static Connection getConnection() throws SQLException {

        if (connection == null){
            Properties properties = new Properties();
            try {
                properties.load(DBConnection.class.getClassLoader().getResourceAsStream("db.properties"));

                String url = properties.getProperty("db.url");
                String username = properties.getProperty("db.username");
                String password = properties.getProperty("db.password");

                connection = DriverManager.getConnection(url,username,password);

            } catch (Exception e) {
                throw new SQLException("Error al conectar a la base de datos", e);
            }
        }

        return connection;
    }
}
