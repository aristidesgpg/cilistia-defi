import React, {useState} from "react";
import Customer from "./components/Customer";
import {Box} from "@mui/material";
import Transaction from "./components/Transaction";
import {CommerceCustomerProvider} from "contexts/CommerceCustomerContext";

const Payment = () => {
    const [customer, setCustomer] = useState();

    return (
        <CommerceCustomerProvider customer={customer}>
            <Box sx={{textAlign: "center"}}>
                {!customer ? (
                    <Customer setCustomer={setCustomer} />
                ) : (
                    <Transaction />
                )}
            </Box>
        </CommerceCustomerProvider>
    );
};

export default Payment;
