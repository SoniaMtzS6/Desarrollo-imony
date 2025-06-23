package com.fisinter.admin.crear;

import com.amazonaws.services.lambda.runtime.Context;
import com.amazonaws.services.lambda.runtime.LambdaLogger;
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
public class AltaAdmin implements RequestHandler<Map<String, Object>, Map<String, Object>>
{
    private final AdministradorService administradorService;
    private final ObjectMapper mapper = new ObjectMapper();

    public AltaAdmin(AdministradorService administradorService){
        this.administradorService = administradorService;
    }

    public AltaAdmin() throws SQLException {
        this(new AdministradorService());
    }

    @Override
    public Map<String, Object> handleRequest(Map<String, Object> input, Context context) {

        Map<String, Object> response = new HashMap<>();
        Map<String, Object> error = new HashMap<>();
        LambdaLogger logger = context.getLogger();

        // Validación de campos requeridos
        validarCampo(input, "email", error);
        validarCampo(input, "nombre", error);
        validarCampo(input, "telefono", error);
        validarCampo(input, "idEmpresa", error);
        validarCampo(input, "perfil", error);

        // Si hay errores de validación, los devolvemos
        if (!error.isEmpty()) {
            response.put("Error", error);
            return response;
        }

        try {

            if (!administradorService.buscarPorId((Integer) input.get("idEmpresa"))){
                response.put("Error", "idEmpresa no existe");
                return response;
            }

            if (administradorService.validateAdminIdEmpresa((Integer) input.get("idEmpresa"))){
                response.put("Error", "idEmpresa ya está asignado a un Administrador");
                return response;
            }

            //Convertimos el base64 a byte
            if ( input.containsKey("image") && !input.get("image").equals("") ){
                String base64Image = input.get("image").toString();
                if (input.get("image").toString().contains(",")) {
                    base64Image = base64Image.split(",")[1];
                }
                byte[] imageByte = Base64.getDecoder().decode(base64Image);
                input.replace("image",input.get("image"),imageByte);
            }
            // Convertimos el input en un objeto Administrador
            Administrador administrador = mapper.convertValue(input, Administrador.class);

            // Llamamos al servicio para crear el administrador
            administradorService.crear(administrador);

            // Respuesta de éxito
            response.put("OK", "Administrador creado correctamente");
        } catch (SQLException e) {
            logger.log("Error al crear administrador: " + e.getMessage());
            response.put("Error", "Hubo un error al crear el usuario Administrador");
        } catch (Exception e) {
            logger.log("Error inesperado: " + e.getMessage());
            response.put("Error", "Error inesperado al crear el administrador");
        }

        return response;
    }

    // Método auxiliar para validar campos
    private void validarCampo(Map<String, Object> input, String campo, Map<String, Object> errorMap) {
        if (!input.containsKey(campo) || input.get(campo).toString().trim().isEmpty()) {
            errorMap.put(campo, campo + " es requerido");
        }

    }
}
