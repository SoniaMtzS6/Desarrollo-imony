package com.fisinter.admin;

import com.amazonaws.services.lambda.runtime.ClientContext;
import com.amazonaws.services.lambda.runtime.CognitoIdentity;
import com.amazonaws.services.lambda.runtime.Context;
import com.amazonaws.services.lambda.runtime.LambdaLogger;
import com.fasterxml.jackson.databind.ObjectMapper;

import java.util.HashMap;
import java.util.Map;

public class Main {
    public static void main(String[] args) {
        Map<String,Object> input = new HashMap<>();
        Map<String,Object> output = new HashMap<>();
        input.put("page", 1);
        input.put("size", 10);
        input.put("filter", "id");
        input.put("value",6);

        try {
            ObjectMapper objectMapper = new ObjectMapper();
            BusquedaAdmin busquedaAdmin = new BusquedaAdmin();
            output = busquedaAdmin.handleRequest(input, new Context() {
                @Override
                public String getAwsRequestId() {
                    return "";
                }

                @Override
                public String getLogGroupName() {
                    return "";
                }

                @Override
                public String getLogStreamName() {
                    return "";
                }

                @Override
                public String getFunctionName() {
                    return "";
                }

                @Override
                public String getFunctionVersion() {
                    return "";
                }

                @Override
                public String getInvokedFunctionArn() {
                    return "";
                }

                @Override
                public CognitoIdentity getIdentity() {
                    return null;
                }

                @Override
                public ClientContext getClientContext() {
                    return null;
                }

                @Override
                public int getRemainingTimeInMillis() {
                    return 0;
                }

                @Override
                public int getMemoryLimitInMB() {
                    return 0;
                }

                @Override
                public LambdaLogger getLogger() {
                    return null;
                }
            });
            System.out.println("output = " + objectMapper.writeValueAsString(output));
        }catch (Exception e){
            e.getMessage();
        }
    }
}
