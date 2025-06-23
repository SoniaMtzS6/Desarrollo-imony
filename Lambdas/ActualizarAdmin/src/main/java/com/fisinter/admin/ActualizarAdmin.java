package com.fisinter.admin;

import com.amazonaws.services.lambda.runtime.Context;
import com.amazonaws.services.lambda.runtime.RequestHandler;
import com.fasterxml.jackson.databind.ObjectMapper;

import java.sql.SQLException;
import java.util.Base64;
import java.util.HashMap;
import java.util.Map;

/**
 * Hello world!
 *
 */
public class ActualizarAdmin implements RequestHandler<Map<String, Object>, Map<String, Object>> {
    private final AdministradorService administradorService;

    public ActualizarAdmin(AdministradorService administradorService) {
        this.administradorService = administradorService;
    }

    public ActualizarAdmin() throws SQLException {
        this(new AdministradorService());
    }

    @Override
    public Map<String, Object> handleRequest(Map<String, Object> input, Context context) {
        Map<String, Object> response = new HashMap<>();
        String msg = "";

        // Validación del ID
        if (!input.containsKey("id") || input.get("id") == null || input.get("id").toString().isEmpty()) {
            response.put("Error", "ID es necesario");
            return response;
        }

        try {
            // Verificar y decodificar la imagen solo si está presente
            if (input.containsKey("image") && input.get("image") != null && !input.get("image").toString().isEmpty()) {
                String base64Image = input.get("image").toString();
                if (base64Image.contains(",")) {
                    base64Image = base64Image.split(",")[1]; // Eliminar el encabezado de base64 si existe
                }
                try {
                    byte[] imageByte = Base64.getDecoder().decode(base64Image);
                    input.replace("image", input.get("image"), imageByte); // Reemplazar la imagen en el mapa
                } catch (IllegalArgumentException e) {
                    response.put("Error", "Formato de imagen inválido");
                    return response;
                }
            }

            administradorService.actualizar(input);
            response.put("OK", "Administrador actualizado correctamente");

        } catch (SQLException e) {
            response.put("OK", "Hubo un error en la actualizacion");
            response.put("Detail", e.getMessage());
        }

        return response;
    }
}
