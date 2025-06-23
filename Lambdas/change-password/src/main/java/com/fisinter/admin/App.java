package com.fisinter.admin;

import com.amazonaws.services.lambda.runtime.Context;
import com.amazonaws.services.lambda.runtime.RequestHandler;

import java.sql.SQLException;
import java.util.HashMap;
import java.util.Map;

/**
 * Hello world!
 *
 */
public class App implements RequestHandler <Map<String, Object>, Map<String, Object>>{
    public static final String STATUS_CODE = "statusCode";
    public static final String MESSAGE = "message";

    private final UsersDAO usersDAO;

    public App(UsersDAO usersDAO){
        this.usersDAO = usersDAO;
    }
    public App() throws SQLException {
        this(new UsersDAO());
    }

    @Override
    public Map<String, Object> handleRequest(Map<String, Object> input, Context context) {
        Map<String, Object> response = new HashMap<>();

        try {
            String email = input.containsKey("email")? input.get("email").toString() : null;
            String currentPassword = input.containsKey("currentPassword")? input.get("currentPassword").toString() : null;
            String newPassword = input.containsKey("newPassword")? input.get("newPassword").toString() : null;

            if ( email == null || currentPassword == null){
                response.put(STATUS_CODE,400);
                response.put(MESSAGE, "Missing parameters: email and password are required.");
                return response;
            }

            if (usersDAO.validateCurrentPassword(email, currentPassword)) {
                // Actualizar la contraseña
                usersDAO.updatePassword(email, newPassword);
                response.put(STATUS_CODE, 200);
                response.put(MESSAGE,"Password updated successfully");
            } else {
                response.put(STATUS_CODE, 401);
                response.put(MESSAGE,"Invalid current password.");
            }
        } catch (Exception e) {
            response.put(STATUS_CODE, 500);
            response.put(MESSAGE,"Internal server error :" + e.getMessage());
            e.printStackTrace();
        }
        return response;
    }

    private Map<String, Object> validInputData(Map<String, Object> input){
        return null;
    }
}
