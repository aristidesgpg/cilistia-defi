import React from "react";
import {Stack, Typography} from "@mui/material";

const AmountCell = ({transaction}) => {
    return (
        <Stack>
            <Typography variant="body2">{transaction.value}</Typography>

            <Typography variant="caption" color="text.secondary">
                {transaction.formatted_price}
            </Typography>
        </Stack>
    );
};

export default AmountCell;
