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

        String query = "INSERT INTO adjustment(id_transaction, type_transaction, country_code, origin_transaction, source_transaction, network, original_transaction_id, local_date_time, id_merchant, merchant_mcc, merchant_name, id_card, product_type, provider_card, last_four, id_user, total_local_amount, currency_local_amount, total_transaction_amount, currency_transaction_amount, total_settlement_amount, currency_settlement_amount, type_details_amount, currency_details_amount, amount_details_amount, name_details_amount) " +
                "values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

        try (PreparedStatement ps = connection.prepareStatement(query)){
            ps.setString(1,data.getIdTransaction());
            ps.setString(2,data.getTypeTransaction());
            ps.setString(3,data.getCountryCode());
            ps.setString(4,data.getOriginTransaction());
            ps.setString(5,data.getSourceTransaction());
            ps.setString(6,data.getNetwork());
            ps.setString(7,data.getOriginalTransactionId());
            ps.setString(8,data.getLocalDateTime());
            ps.setInt(9,data.getIdMerchant());
            ps.setInt(10,data.getMerchantMcc());
            ps.setString(11,data.getMerchantName());
            ps.setString(12,data.getIdCard());
            ps.setString(13,data.getProductType());
            ps.setString(14,data.getProviderCard());
            ps.setInt(15,data.getLastFour());
            ps.setString(16,data.getIdUser());
            ps.setDouble(17,data.getTotalLocalAmount());
            ps.setString(18,data.getCurrencyLocalAmount());
            ps.setDouble(19,data.getTotalTransactionAmount());
            ps.setString(20,data.getCurrencyTransactionAmount());
            ps.setDouble(21,data.getTotalSettlementAmount());
            ps.setString(22,data.getCurrencySettlementAmount());
            ps.setString(23,data.getTypeDetailsAmount());
            ps.setString(24,data.getCurrencyDetailsAmount());
            ps.setDouble(25,data.getAmountDetailsAmount());
            ps.setString(26,data.getNameDetailsAmount());
            ps.execute();
        }catch (SQLException e) {
            //Error al guardar datos
            throw new RuntimeException(e);
        }

    }
}
