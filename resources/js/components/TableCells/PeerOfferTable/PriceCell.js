import React from "react";
import {Typography} from "@mui/material";

const PriceCell = ({offer}) => {
    return <Typography variant="body2">{offer.formatted_price}</Typography>;
};

export default PriceCell;
