import React, {useMemo} from "react";
import {Link as RouterLink} from "react-router-dom";
import VisibilityIcon from "@mui/icons-material/Visibility";
import {IconButton} from "@mui/material";
import router from "router/router";

const PaymentView = ({payment}) => {
    const link = useMemo(() => {
        return router.generatePath("main.commerce.payment", {id: payment.id});
    }, [payment]);

    return (
        <IconButton color="primary" component={RouterLink} to={link}>
            <VisibilityIcon />
        </IconButton>
    );
};

export default PaymentView;
