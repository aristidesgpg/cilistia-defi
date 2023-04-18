import React from "react";
import {Grid} from "@mui/material";
import Banks from "./components/Banks";
import Accounts from "./components/Accounts";

const BankAccounts = () => {
    return (
        <Grid container spacing={3}>
            <Grid item xs={12}>
                <Accounts />
            </Grid>

            <Grid item xs={12}>
                <Banks />
            </Grid>
        </Grid>
    );
};

export default BankAccounts;
