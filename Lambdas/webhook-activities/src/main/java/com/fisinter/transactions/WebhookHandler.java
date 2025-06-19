package com.fisinter.transactions;

import com.amazonaws.services.lambda.runtime.Context;
import com.amazonaws.services.lambda.runtime.LambdaLogger;
import com.amazonaws.services.lambda.runtime.RequestHandler;
import com.amazonaws.services.lambda.runtime.events.APIGatewayProxyRequestEvent;
import com.fasterxml.jackson.core.JsonProcessingException;
import com.fasterxml.jackson.databind.JsonNode;
import com.fasterxml.jackson.databind.ObjectMapper;

import java.io.UnsupportedEncodingException;
import java.security.InvalidKeyException;
import java.security.NoSuchAlgorithmException;
import java.sql.SQLException;
import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.Base64;
import java.util.HashMap;
import java.util.Map;
import java.sql.Date;

import javax.crypto.Mac;
import javax.crypto.spec.SecretKeySpec;

public class WebhookHandler implements RequestHandler<APIGatewayProxyRequestEvent, Map<String, Object>> {

    private static final String SIGNATURE_HEADER = "X-Signature";
    private static final String TIMESTAMP_HEADER = "X-Timestamp";
    private static final String ENDPOINT_HEADER = "X-Endpoint";
    private static final String API_SECRET = "JBg9v1RYIS1viXlm1pUtVyp0c5yAIfJ6ztQwkhnoDYo=";

    @Override
    public Map<String, Object> handleRequest(APIGatewayProxyRequestEvent request, Context context) {
        LambdaLogger logger = context.getLogger();
        Map<String, Object> response = new HashMap<>();
        Map<String, String> responseBody = new HashMap<>();

        try {

            //Extraemos los headers
            Map<String,String> headers = request.getHeaders();
            String timestamp = headers.get(TIMESTAMP_HEADER);
            String endpoint = headers.get(ENDPOINT_HEADER);
            String requestSignature = headers.get(SIGNATURE_HEADER);
            if (requestSignature == null || requestSignature.isEmpty()){
                logger.log("requestSignature: " + requestSignature);
                responseBody.put("Status", "REJECTED");
                responseBody.put("StatusDetail","INVALID SIGNATURE");
                responseBody.put("Message","ERROR");

                response.put("body", new ObjectMapper().writeValueAsString(responseBody));

                return response;
            }

            //See extrae el cuerpo de la respuesta
            String body = request.getBody();

            //Construimos la firma con los datos
            String signatureData = timestamp + endpoint + body;

            byte[] clientApiSecretDecoded = Base64.getDecoder().decode(API_SECRET);

            Mac mac = Mac.getInstance("HmacSHA256");
            SecretKeySpec secretKeySpec = new SecretKeySpec(clientApiSecretDecoded,"HmacSHA256");
            mac.init(secretKeySpec);
            byte[] hMacBytes = mac.doFinal(signatureData.getBytes("UTF-8"));
            String recreatedSignature = Base64.getEncoder().encodeToString(hMacBytes);

            String expectedSignature = "hmac-sha256 " + recreatedSignature;
            boolean validSignature = requestSignature.equals(expectedSignature);

            //logs
            logger.log("recreatedSignature: " + recreatedSignature);
            logger.log("expectedSignature: " + expectedSignature);
            logger.log(" *************************************** ");
            logger.log("timestamp: " + timestamp);
            logger.log("endpoint: " + endpoint);
            logger.log("body: " + body);

            // Construir la respuesta
            if ( validSignature ){
                logger.log("VALID SIGNATURE  ");
                response.put("statusCode", 200);
                Map<String, String> responseHeaders = new HashMap<>();
                responseHeaders.put("Content-Type", "application/json");
                response.put("headers", responseHeaders);

                responseBody.put("Status", "APPROVED");
                responseBody.put("StatusDetail","APPROVED");
                responseBody.put("Message","OK");

                TransactionDAO transactionDAO = new TransactionDAO();
                transactionDAO.save(getActivityData(body));
            }else {
                logger.log("INVALID SIGNATURE  ");
                responseBody.put("Status", "REJECTED");
                responseBody.put("StatusDetail","INVALID SIGNATURE");
                responseBody.put("Message","ERROR");
            }

            response.put("body", new ObjectMapper().writeValueAsString(responseBody));

        } catch (NoSuchAlgorithmException | InvalidKeyException | UnsupportedEncodingException |
                 JsonProcessingException e) {
            logger.log("Error: " + e.getMessage());
            throw new RuntimeException(e);
        } catch (SQLException e) {
            logger.log("Error: " + e.getMessage());
            throw new RuntimeException(e);
        } catch (ParseException e) {
            logger.log("Error: " + e.getMessage());
            throw new RuntimeException(e);
        }
        return response;
    }

    private Activity getActivityData(String body) throws JsonProcessingException, ParseException {
        Activity transaction = new Activity();

        ObjectMapper mapper = new ObjectMapper();
        JsonNode rootNode = mapper.readTree(body);
        SimpleDateFormat formatter = new SimpleDateFormat("yyyy-MM-dd");

        transaction.setDatetime(rootNode.has("datetime")? rootNode.get("datetime").asText() : null);
        transaction.setIdempotencyKey(rootNode.has("idempotency_key")? rootNode.get("idempotency_key").asText() : "");
        transaction.setType(rootNode.has("type")? rootNode.get("type").asText() : "");
        transaction.setVersion(rootNode.has("version")? rootNode.get("version").asText() : "");

        if (rootNode.has("activity")){
            JsonNode activity = rootNode.get("activity");

            transaction.setCreatedAt(activity.has("created_at")? activity.get("created_at").asText(): null);
            transaction.setEntryType(activity.has("entry_type")? activity.get("entry_type").asText() : "");
            transaction.setForced(activity.has("forced")? activity.get("forced").asText() : "");
            transaction.setOrigin(activity.has("origin")? activity.get("origin").asText() : "");
            transaction.setOriginTxId(activity.has("origin_tx_id")? activity.get("origin_tx_id").asText() : "");
            transaction.setProcessType(activity.has("process_type")? activity.get("process_type").asText() : "");
            transaction.setResult(activity.has("result")? activity.get("result").asText() : "");
            transaction.setTotalAmount(activity.has("total_amount")? activity.get("total_amount").asText() : "");
            transaction.setType(activity.has("type")? activity.get("type").asText() : "");

            if (activity.has("account")){
                JsonNode account = activity.get("account");

                transaction.setCountry(account.has("country")? account.get("country").asText() : "");
                transaction.setCurrency(account.has("currency")? account.get("currency").asText() : "");
                transaction.setAccountId(account.has("id")? account.get("id").asText() : "");
            }
            if (activity.has("data")){
                JsonNode data = activity.get("data");
                if (data.has("description")){
                    JsonNode description = data.get("description");
                    transaction.setDescription(description.has("en-US")? description.get("en-US").asText() : "");
                }
                if (data.has("properties")){
                    JsonNode properties = data.get("properties");

                    transaction.setPointType(properties.has("point_type")? properties.get("point_type").asText() : "");
                    transaction.setAddress(properties.has("address")? properties.get("address").asText() : "");
                    transaction.setCardBrand(properties.has("card_brand")? properties.get("card_brand").asText() : "");
                    transaction.setEntryMode(properties.has("entry_mode")? properties.get("entry_mode").asText() : "");
                    transaction.setMerchantName(properties.has("merchant_name")? properties.get("merchant_name").asText() : "");
                    transaction.setMerchantId(properties.has("merchant_id")? properties.get("merchant_id").asText() : "");
                    transaction.setCardType(properties.has("card_type")? properties.get("card_type").asText() : "");
                    transaction.setMcc(properties.has("mcc")? properties.get("mcc").asText() : "");
                    transaction.setLastDigits(properties.has("last_digits")? properties.get("last_digits").asText() : "");

                }
            }
        }

        return transaction;
    }
}
