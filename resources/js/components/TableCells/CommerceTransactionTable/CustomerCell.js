import React from "react";
import {Stack, Typography} from "@mui/material";

const CustomerCell = ({transaction}) => {
    const customer = transaction.customer;

    return (
        <Stack>
            <Typography variant="body2" noWrap>
                {customer.email}
            </Typography>

            <Typography variant="caption" color="text.secondary" noWrap>
                {`${customer.first_name} ${customer.last_name}`}
            </Typography>
        </Stack>
    );
};

export default CustomerCell;
