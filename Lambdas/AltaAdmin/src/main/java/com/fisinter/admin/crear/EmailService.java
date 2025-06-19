package com.fisinter.admin.crear;

import org.apache.hc.client5.http.classic.methods.HttpPost;
import org.apache.hc.client5.http.impl.classic.CloseableHttpClient;
import org.apache.hc.client5.http.impl.classic.CloseableHttpResponse;
import org.apache.hc.client5.http.impl.classic.HttpClients;
import org.apache.hc.core5.http.io.entity.StringEntity;
import org.apache.hc.core5.http.ContentType;

import java.io.BufferedReader;
import java.io.InputStreamReader;

public class EmailService {

    private static final String API_URL = "https://v6g3vgism2.execute-api.us-east-2.amazonaws.com/dev/sendMail";

    public static final String SUBJECT = "Usuario administrador creado correctamente";


    public void sendEmail(String email, String body) {
        CloseableHttpClient httpClient = HttpClients.createDefault();

        try {
            // Crear la solicitud POST
            HttpPost postRequest = new HttpPost(API_URL);

            // Crear el cuerpo de la solicitud
            String jsonPayload = String.format(
                    "{ \"to\": \"%s\", \"subject\": \"%s\", \"body\": \"%s\" }",
                    email, SUBJECT, body
            );

            StringEntity entity = new StringEntity(jsonPayload, ContentType.APPLICATION_JSON);
            postRequest.setEntity(entity);

            // Enviar la solicitud
            CloseableHttpResponse response = httpClient.execute(postRequest);
            try {
                int statusCode = response.getCode();

                BufferedReader reader = new BufferedReader(
                        new InputStreamReader(response.getEntity().getContent(), "UTF-8")
                );
                StringBuilder responseBody = new StringBuilder();
                String line;
                while ((line = reader.readLine()) != null) {
                    responseBody.append(line);
                }

                // Manejar la respuesta
                if (statusCode == 200) {
                    System.out.println("Email enviado con éxito: " + responseBody.toString());
                } else {
                    System.err.println("Error al enviar el correo. Código de estado: " + statusCode);
                    System.err.println("Respuesta: " + responseBody.toString());
                }
            } finally {
                response.close();
            }
        } catch (Exception e) {
            e.printStackTrace();
            System.err.println("Excepción al enviar el correo: " + e.getMessage());
        } finally {
            try {
                httpClient.close();
            } catch (Exception ex) {
                System.err.println("Error al cerrar el cliente HTTP: " + ex.getMessage());
            }
        }
    }
}
