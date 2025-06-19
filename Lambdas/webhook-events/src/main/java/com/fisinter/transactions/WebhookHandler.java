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
import java.util.Base64;
import java.util.HashMap;
import java.util.Map;

import javax.crypto.Mac;
import javax.crypto.spec.SecretKeySpec;

public class WebhookHandler implements RequestHandler<APIGatewayProxyRequestEvent, Map<String, Object>> {

    private static final String SIGNATURE_HEADER = "X-Signature";
    private static final String TIMESTAMP_HEADER = "X-Timestamp";
    private static final String ENDPOINT_HEADER = "X-Endpoint";
    private static final String API_SECRET = "hByKl5U+zzpMibm7MiEnjEsnBHC4ntATnEhjzKRw2fw=";

    @Override
    public Map<String, Object> handleRequest(APIGatewayProxyRequestEvent request, Context context) {
        LambdaLogger logger = context.getLogger();
        Map<String, Object> response = new HashMap<>();
        Map<String, String> responseBody = new HashMap<>();

        try {
            //See extrae el cuerpo de la respuesta
            String body = request.getBody();
            logger.log("********* Request Body **********" + body);

            //Extraemos los headers
            Map<String,String> headers = request.getHeaders();
            String timestamp = headers.get(TIMESTAMP_HEADER);
            String endpoint = headers.get(ENDPOINT_HEADER);
            String requestSignature = headers.get(SIGNATURE_HEADER);
            if (requestSignature == null || requestSignature.isEmpty()){
                responseBody.put("Status", "REJECTED");
                responseBody.put("StatusDetail","INVALID SIGNATURE");
                responseBody.put("Message","ERROR");

                response.put("body", new ObjectMapper().writeValueAsString(responseBody));

                return response;
            }


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

            // Construir la respuesta
            if ( validSignature ){
                response.put("statusCode", 200);
                Map<String, String> responseHeaders = new HashMap<>();
                responseHeaders.put("Content-Type", "application/json");
                response.put("headers", responseHeaders);

                responseBody.put("Status", "APPROVED");
                responseBody.put("StatusDetail","APPROVED");
                responseBody.put("Message","OK");

                TransactionDAO transactionDAO = new TransactionDAO();
                transactionDAO.save(getDataTransaction(body));
            }else {
                responseBody.put("Status", "REJECTED");
                responseBody.put("StatusDetail","INVALID SIGNATURE");
                responseBody.put("Message","ERROR");
            }

            response.put("body", new ObjectMapper().writeValueAsString(responseBody));
            logger.log("Request Body " + body);

        } catch (NoSuchAlgorithmException | InvalidKeyException | UnsupportedEncodingException |
                 JsonProcessingException e) {
            logger.log("Error: " + e.getMessage());
            throw new RuntimeException(e);
        } catch (SQLException e) {
            throw new RuntimeException(e);
        }
        return response;
    }

    private Transaction getDataTransaction(String body) throws JsonProcessingException {
        Transaction transaction = new Transaction();

        ObjectMapper mapper = new ObjectMapper();
        JsonNode rootNode = mapper.readTree(body);

        if (rootNode.has("transaction")){
            JsonNode transactionNode = rootNode.get("transaction");

            transaction.setIdTransaction(transactionNode.has("id")? transactionNode.get("id").asText() : "");
            transaction.setTypeTransaction(transactionNode.has("type")? transactionNode.get("type").asText() : "");
            transaction.setCountryCode(transactionNode.has("country_code")? transactionNode.get("country_code").asText() : "");
            transaction.setOriginTransaction(transactionNode.has("origin")? transactionNode.get("origin").asText() : "");
            transaction.setSourceTransaction(transactionNode.has("source")? transactionNode.get("source").asText() : "");
            transaction.setNetwork(transactionNode.has("network")? transactionNode.get("network").asText() : "");
            transaction.setOriginalTransactionId(transactionNode.has("original_transaction_id")? transactionNode.get("original_transaction_id").asText() : "");
            transaction.setLocalDateTime(transactionNode.has("local_date_time")? transactionNode.get("local_date_time").asText() : "");
        }
        if (rootNode.has("merchant")){
            JsonNode merchantnNode = rootNode.get("merchant");

            transaction.setIdMerchant(merchantnNode.has("id")? merchantnNode.get("id").asInt() : null);
            transaction.setMerchantMcc(merchantnNode.has("mcc")?  merchantnNode.get("id").asInt() : null);
            transaction.setMerchantName(merchantnNode.has("name")? merchantnNode.get("id").asText() : null);

        }
        if (rootNode.has("card")){
            JsonNode merchantnNode = rootNode.get("card");

            transaction.setIdCard(merchantnNode.has("id")? merchantnNode.get("id").asText() : null);
            transaction.setProductType(merchantnNode.has("product_type")? merchantnNode.get("product_type").asText() : null);
            transaction.setProviderCard(merchantnNode.has("provider")? merchantnNode.get("provider").asText() : null);
            transaction.setLastFour(merchantnNode.has("last_four")? merchantnNode.get("last_four").asInt() : null);

        }
        /*if (dataMap.containsKey("installments")){
            transaction.set(dataMap.containsKey("quantity")? (Integer) dataMap.get("quantity") : null);
            transaction.setIdTransaction(dataMap.containsKey("credit_type")? (Integer) dataMap.get("credit_type") : null);
            transaction.setIdTransaction(dataMap.containsKey("grace_period")? (Integer) dataMap.get("grace_period") : null);
            transaction.setIdTransaction(dataMap.containsKey("current_installment")? (Integer) dataMap.get("current_installment") : null);
            transaction.setIdTransaction(dataMap.containsKey("promotion_type")? (Integer) dataMap.get("promotion_type") : null);

        }*/
        if (rootNode.has("user")){
            JsonNode userNode = rootNode.get("user");

            transaction.setIdUser(userNode.has("id")? userNode.get("id").asText() : null);

        }
        if (rootNode.has("amount")){
            JsonNode amountNode = rootNode.get("amount");

            if (amountNode.has("local")){
                JsonNode localNode = amountNode.get("local");

                transaction.setTotalLocalAmount(localNode.has("total")? localNode.get("total").asDouble() : null);
                transaction.setCurrencyLocalAmount(localNode.has("currency")? localNode.get("currency").asText() : null);

            }
            if (amountNode.has("settlement")){
                JsonNode settlementNode = amountNode.get("settlement");

                transaction.setTotalSettlementAmount(settlementNode.has("total")? settlementNode.get("total").asDouble() : null);
                transaction.setCurrencySettlementAmount(settlementNode.has("currency")? settlementNode.get("currency").asText() : null);

            }
            if (amountNode.has("transaction")){
                JsonNode transactionNode = amountNode.get("transaction");
                transaction.setTotalTransactionAmount(transactionNode.has("total")? transactionNode.get("total").asDouble() : null);
                transaction.setCurrencyTransactionAmount(transactionNode.has("currency")? transactionNode.get("currency").asText() : null);

            }

            if (amountNode.has("details")){
                JsonNode detailsNode = amountNode.get("details");
                if (detailsNode.isArray()){
                    for (JsonNode detail : detailsNode){
                        transaction.setTypeDetailsAmount(detail.has("type")? detail.get("type").asText() : "");
                        transaction.setCurrencyDetailsAmount(detail.has("currency")? detail.get("currency").asText() : "");
                        transaction.setAmountDetailsAmount(Double.parseDouble(detail.has("amount")? detail.get("amount").asText() : ""));
                        transaction.setNameDetailsAmount(detail.has("name")? detail.get("name").asText() : "");
                    }
                }
            }
        }

        return transaction;
    }

}
