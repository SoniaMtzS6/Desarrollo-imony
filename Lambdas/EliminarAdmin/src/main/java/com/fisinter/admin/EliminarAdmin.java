package com.fisinter.admin;

import com.amazonaws.services.lambda.runtime.Context;
import com.amazonaws.services.lambda.runtime.LambdaLogger;
import com.amazonaws.services.lambda.runtime.RequestHandler;
import com.fasterxml.jackson.databind.ObjectMapper;

import java.sql.SQLException;
import java.util.Map;
import java.util.NoSuchElementException;

/**
 * Hello world!
 *
 */
public class EliminarAdmin implements RequestHandler<Map<String, Object>, String>
{
    private final AdministradorService administradorService;
    private final ObjectMapper mapper = new ObjectMapper();

    public EliminarAdmin(AdministradorService administradorService){
        this.administradorService = administradorService;
    }

    public EliminarAdmin() throws SQLException {
        this(new AdministradorService());
    }

    @Override
    public String handleRequest(Map<String, Object> input, Context context) {

        LambdaLogger logger = context.getLogger();
        String msg = "";

        // Validar que el campo "id" esté presente y no vacío
        if (!input.containsKey("id") || input.get("id") == null || input.get("id").toString().trim().isEmpty()) {
            return "Error: ID es necesario";
        }

        try {
            // Convertir el input a objeto Administrador si la validación fue correcta
            Administrador administrador = mapper.convertValue(input, Administrador.class);

            // Validar que el ID sea un número positivo antes de eliminar
            if (administrador.getId() == null || administrador.getId() <= 0) {
                return "Error: ID debe ser un valor positivo";
            }

            // Eliminar administrador
            administradorService.eliminar(administrador.getId());
            msg = "Administrador eliminado correctamente";

        } catch (NoSuchElementException e) {
            logger.log("No se encontró el administrador con el ID proporcionado: " + input.get("id"));
            msg = "Error: No se encontró el administrador con el ID proporcionado";

        } catch (Exception e) {
            logger.log("Error al eliminar el administrador con ID " + input.get("id") + ": " + e.getMessage());
            msg = "Hubo un error al eliminar usuario Administrador";
        }

        return msg;
    }
}
