package com.fisinter.admin;

import com.amazonaws.services.lambda.runtime.Context;
import com.amazonaws.services.lambda.runtime.LambdaLogger;
import com.amazonaws.services.lambda.runtime.RequestHandler;

import java.sql.SQLException;
import java.util.HashMap;
import java.util.List;
import java.util.Map;
import java.util.Set;

import static java.util.Set.of;

/**
 * Hello world!
 *
 */
public class BusquedaAdmin implements RequestHandler<Map<String, Object>, Map<String, Object>>
{
    private final AdministradorService administradorService;

    public BusquedaAdmin(AdministradorService administradorService){
        this.administradorService = administradorService;
    }

    public BusquedaAdmin() throws SQLException {
        this(new AdministradorService());
    }

    @Override
    public Map<String,Object> handleRequest(Map<String, Object> input, Context context) {

        LambdaLogger logger = context.getLogger();
        Map<String,Object> response = new HashMap<>();

        try {

            int page = input.containsKey("page")? (Integer) input.get("page") : 1;
            int size = input.containsKey("size")? (Integer) input.get("size") : 10;

            String filter = input.containsKey("filter")? input.get("filter").toString() : "nombre";
            String value = input.containsKey("value")? input.get("value").toString() : "";

            if ( !filterValidation(filter) ){
                response.put("Status","Error");
                response.put("Message","Invalid Filter");

                return response;
            }

            List<Administrador> adminList = administradorService.buscarConPaginacion(filter, value, page, size);

            response.put("data", adminList);
            response.put("currentPage",page);
            response.put("pageSize", size);
            response.put("totalItems",administradorService.obtenerTotalRegistros(filter, value));
            response.put("totalPages",(int) Math.ceil((double) administradorService.obtenerTotalRegistros(filter, value) /size));

        } catch (Exception e) {
            logger.log("Hubo un error en la busqueda: " + e.getMessage());
            response.put("Error","Hubo un error con la busqueda");
        }

        return response;
    }

    private boolean filterValidation(String filter){
        Set<String> allowedFilters = of("id","nombre", "email","telefono","perfil", "idEmpresa","activo");
        return allowedFilters.contains(filter);
    }
}
