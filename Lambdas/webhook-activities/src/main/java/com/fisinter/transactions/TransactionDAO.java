package com.fisinter.transactions;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.SQLException;
import java.util.Map;

public class TransactionDAO {

    private Connection connection;

    public TransactionDAO () throws SQLException {
        this.connection = DBConnection.getConnection();
    }

    public void save(Activity data){

        /*String query = "INSERT INTO transaction(id_transaction, type_transaction, country_code, origin_transaction, source_transaction, network, original_transaction_id, local_date_time, id_merchant, merchant_mcc, merchant_name, id_card, product_type, provider_card, last_four, id_user, total_local_amount, currency_local_amount, total_transaction_amount, currency_transaction_amount, total_settlement_amount, currency_settlement_amount, type_details_amount, currency_details_amount, amount_details_amount, name_details_amount) " +
                "values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";*/
        String query = "INSERT INTO activity( account,country,currency,account_id,created_at,description,point_type,address,card_brand,entry_mode,merchant_name,merchant_id,card_type,mcc,last_digits,entry_type,forced,origin,origin_tx_id,process_type,result,total_amount,activity_type,datetime,idempotency_key,type,version)" +
                "VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

        try (PreparedStatement ps = connection.prepareStatement(query)){
            ps.setString(1,data.getAccount());
            ps.setString(2,data.getCountry());
            ps.setString(3,data.getCurrency());
            ps.setString(4,data.getAccountId());
            ps.setString(5,data.getCreatedAt());
            ps.setString(6,data.getDescription());
            ps.setString(7,data.getPointType());
            ps.setString(8,data.getAddress());
            ps.setString(9,data.getCardBrand());
            ps.setString(10,data.getEntryMode());
            ps.setString(11,data.getMerchantName());
            ps.setString(12,data.getMerchantId());
            ps.setString(13,data.getCardType());
            ps.setString(14,data.getMcc());
            ps.setString(15,data.getLastDigits());
            ps.setString(16,data.getEntryType());
            ps.setString(17,data.getForced());
            ps.setString(18,data.getOrigin());
            ps.setString(19,data.getOriginTxId());
            ps.setString(20,data.getProcessType());
            ps.setString(21,data.getResult());
            ps.setString(22,data.getTotalAmount());
            ps.setString(23,data.getActivityType());
            ps.setString(24,data.getDatetime());
            ps.setString(25,data.getIdempotencyKey());
            ps.setString(26,data.getType());
            ps.setString(27,data.getVersion());
            ps.execute();
        }catch (SQLException e) {
            //Error al guardar datos
            throw new RuntimeException(e);
        }

    }
}
