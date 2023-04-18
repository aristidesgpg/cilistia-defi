import React from "react";
import {Stack, Typography} from "@mui/material";

const ReceivedCell = ({transaction}) => {
    return (
        <Stack>
            <Typography variant="body2">{transaction.received}</Typography>

            <Typography variant="caption" color="text.secondary">
                {transaction.formatted_received_price}
            </Typography>
        </Stack>
    );
};

export default ReceivedCell;
