package com.fisinter.admin;

import com.amazonaws.services.lambda.runtime.Context;
import com.amazonaws.services.lambda.runtime.LambdaLogger;
import com.amazonaws.services.lambda.runtime.RequestHandler;
import com.fasterxml.jackson.databind.ObjectMapper;

import java.sql.SQLException;
import java.util.HashMap;
import java.util.Map;

/**
 * Hello world!
 *
 */
public class DesactivarAdmin implements RequestHandler<Map<String, Object>, Map<String, Object>>
{
    private final AdministradorService administradorService;
    private final ObjectMapper mapper = new ObjectMapper();

    public DesactivarAdmin(AdministradorService administradorService){
        this.administradorService = administradorService;
    }

    public DesactivarAdmin() throws SQLException {
        this(new AdministradorService());
    }

    @Override
    public Map<String, Object> handleRequest(Map<String, Object> input, Context context) {

        LambdaLogger logger = context.getLogger();

        Map<String, Object> response = new HashMap<>();

        if (!input.containsKey("id") || input.get("id").equals("")){
            response.put("Error","ID es necesario");
            return response;
        }

        try {

            Administrador administrador = mapper.convertValue(input, Administrador.class);
            administradorService.desactivar(administrador.getId());
            response.put("OK","Administrador ha sido desactivado correctamente");

        } catch (Exception e) {
            response.put("Error","Hubo un error al desactivar usuario Administrador");
            logger.log(e.getMessage());
        }

        return response;
    }
}
