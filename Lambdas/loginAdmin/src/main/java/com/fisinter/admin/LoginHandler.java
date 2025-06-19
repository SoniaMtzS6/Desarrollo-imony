package com.fisinter.admin;


import com.amazonaws.services.lambda.runtime.Context;
import com.amazonaws.services.lambda.runtime.RequestHandler;

import java.sql.SQLException;
import java.util.HashMap;
import java.util.Map;

public class LoginHandler implements RequestHandler<Map<String, Object>, Map<String, Object>> {

    public static final String STATUS_CODE = "statusCode";
    public static final String MESSAGE = "message";

    private final UsersDAO usersDAO;

    public LoginHandler(UsersDAO usersDAO){
        this.usersDAO = usersDAO;
    }
    public LoginHandler() throws SQLException {
        this(new UsersDAO());
    }
    @Override
    public Map<String, Object> handleRequest(Map<String, Object> input, Context context) {

        Map<String, Object> response = new HashMap<>();

        if (!input.containsKey("email") || !input.containsKey("password")) {
            response.put(STATUS_CODE, 400);
            response.put(MESSAGE, "Missing parameters: email and password are required.");
            return response;
        }

        String email = input.get("email").toString();
        String password = input.get("password").toString();

        try {
            Map<String, Object> loginResponse = usersDAO.valid(email,password);

            if (loginResponse != null && loginResponse.containsKey("isPasswordTemporary")){
                boolean isPasswordTemporary = Boolean.parseBoolean(loginResponse.get("isPasswordTemporary").toString());

                if (isPasswordTemporary){
                    response.put(STATUS_CODE,202);
                    response.put(MESSAGE, "Change password required.");
                }else {
                    response.put(STATUS_CODE,200);
                    response.put(MESSAGE, "Login successful.");
                }
            }else {
                response.put(STATUS_CODE,401);
                response.put(MESSAGE, "Invalid credentials.");
            }
        } catch (Exception e) {
            response.put(STATUS_CODE, 500);
            response.put(MESSAGE, "Internal server error: " + e.getMessage());
            e.printStackTrace();
        }

        return response;
    }
}
