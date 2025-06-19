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

    public void save(Transaction data){

        String query = "INSERT INTO notifications(transaction_id, event_id, type_transaction, country_code, origin_transaction, source_transaction, network, original_transaction_id, local_date_time, merchant_id, merchant_mcc, merchant_name, card_id, product_type, provider_card, last_four, user_id, total_local_amount, currency_local_amount, total_transaction_amount, currency_transaction_amount, total_settlement_amount, currency_settlement_amount, type_details_amount, currency_details_amount, amount_details_amount, name_details_amount, status, status_detail, extra_detail) " +
                "values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

        try (PreparedStatement ps = connection.prepareStatement(query)){
            ps.setString(1,data.getIdTransaction());
            ps.setString(2,data.getEventId());
            ps.setString(3,data.getTypeTransaction());
            ps.setString(4,data.getCountryCode());
            ps.setString(5,data.getOriginTransaction());
            ps.setString(6,data.getSourceTransaction());
            ps.setString(7,data.getNetwork());
            ps.setString(8,data.getOriginalTransactionId());
            ps.setString(9,data.getLocalDateTime());
            ps.setInt(10,data.getIdMerchant());
            ps.setInt(11,data.getMerchantMcc());
            ps.setString(12,data.getMerchantName());
            ps.setString(13,data.getIdCard());
            ps.setString(14,data.getProductType());
            ps.setString(15,data.getProviderCard());
            ps.setInt(16,data.getLastFour());
            ps.setString(17,data.getIdUser());
            ps.setDouble(18,data.getTotalLocalAmount());
            ps.setString(19,data.getCurrencyLocalAmount());
            ps.setDouble(20,data.getTotalTransactionAmount());
            ps.setString(21,data.getCurrencyTransactionAmount());
            ps.setDouble(22,data.getTotalSettlementAmount());
            ps.setString(23,data.getCurrencySettlementAmount());
            ps.setString(24,data.getTypeDetailsAmount());
            ps.setString(25,data.getCurrencyDetailsAmount());
            ps.setDouble(26,data.getAmountDetailsAmount());
            ps.setString(27,data.getNameDetailsAmount());
            ps.setString(28,data.getStatus());
            ps.setString(29,data.getStatusDetail());
            ps.setString(30,data.getExtraDetail());
            ps.execute();
        }catch (SQLException e) {
            //Error al guardar datos
            throw new RuntimeException(e);
        }

    }
}
