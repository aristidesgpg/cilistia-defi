import React, {forwardRef, useContext, useMemo} from "react";
import {Box, Collapse, Stack, Typography} from "@mui/material";
import AddressDisplay from "./components/AddressDisplay";
import {FormattedMessage} from "react-intl";
import StatusFeedback from "./components/StatusFeedback";
import CommerceTransactionContext from "contexts/CommerceTransactionContext";
import PaymentDetails from "./components/PaymentDetails";

const TransactionView = forwardRef((props, ref) => {
    const {transaction} = useContext(CommerceTransactionContext);

    const title = useMemo(() => {
        switch (transaction.status) {
            case "canceled":
                return <FormattedMessage defaultMessage="Payment Canceled." />;
            case "completed":
                return <FormattedMessage defaultMessage="Payment Complete." />;
            default:
                return <FormattedMessage defaultMessage="Scan to Pay" />;
        }
    }, [transaction]);

    return (
        <Box ref={ref}>
            <Stack sx={{mb: 3}}>
                <Typography variant="h4">{title}</Typography>

                <Typography variant="caption" color="text.secondary">
                    <FormattedMessage defaultMessage="Please, keep this page open through out your payment session." />
                </Typography>
            </Stack>

            <Stack sx={{position: "relative"}} spacing={5}>
                <Collapse in={transaction.status === "pending"} unmountOnExit>
                    <AddressDisplay />
                </Collapse>

                <StatusFeedback />

                <Collapse in={transaction.status === "pending"} unmountOnExit>
                    <PaymentDetails />
                </Collapse>
            </Stack>
        </Box>
    );
});

export default TransactionView;
