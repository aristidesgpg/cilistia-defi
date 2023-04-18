import React, {useMemo} from "react";
import router from "router/router";
import {IconButton} from "@mui/material";
import VisibilityIcon from "@mui/icons-material/Visibility";
import {Link as RouterLink} from "react-router-dom";

const CustomerView = ({customer}) => {
    const link = useMemo(() => {
        return router.generatePath("main.commerce.customer", {id: customer.id});
    }, [customer]);

    return (
        <IconButton color="primary" component={RouterLink} to={link}>
            <VisibilityIcon />
        </IconButton>
    );
};

export default CustomerView;
