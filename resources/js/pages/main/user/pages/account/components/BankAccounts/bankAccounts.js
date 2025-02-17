import React, {useCallback, useEffect, useState} from "react";
import {
    Box,
    Button,
    Card,
    CardContent,
    CardHeader,
    Grid,
    IconButton,
    Paper,
    Stack,
    Typography
} from "@mui/material";
import {defineMessages, FormattedMessage, useIntl} from "react-intl";
import DeleteIcon from "@mui/icons-material/Delete";
import {useAuth} from "models/Auth";
import Result from "components/Result";
import {CountryIllustration} from "assets/index";
import {useModal} from "utils/modal";
import AddAccount from "./components/AddAccount";
import {errorHandler, route, useRequest} from "services/Http";
import PopConfirm from "components/PopConfirm";
import AddIcon from "@mui/icons-material/Add";
import BankLogo from "components/BankLogo";
import {formatDate} from "utils/formatter";
import LoadingFallback from "components/LoadingFallback";

const messages = defineMessages({
    submit: {defaultMessage: "Submit"},
    addAccount: {defaultMessage: "Add Account"},
    confirm: {defaultMessage: "Are you sure?"}
});

const BankAccounts = () => {
    const auth = useAuth();
    const intl = useIntl();
    const [request, loading] = useRequest();
    const [accounts, setAccounts] = useState([]);
    const [modal, modalElements] = useModal();

    const fetchAccounts = useCallback(() => {
        request
            .get(route("bank-account.all"))
            .then((data) => setAccounts(data))
            .catch(errorHandler());
    }, [request]);

    useEffect(() => {
        if (auth.countryOperation()) {
            fetchAccounts();
        }
    }, [fetchAccounts, auth]);

    const deleteAccount = useCallback(
        (id) => {
            request
                .delete(route("bank-account.delete", {id}))
                .then(() => fetchAccounts())
                .catch(errorHandler());
        },
        [request, fetchAccounts]
    );

    const addAccount = useCallback(() => {
        modal.confirm({
            title: intl.formatMessage(messages.addAccount),
            content: <AddAccount fetchAccounts={fetchAccounts} />,
            dialog: {fullWidth: true}
        });
    }, [modal, intl, fetchAccounts]);

    return (
        <Card>
            <CardHeader
                title={<FormattedMessage defaultMessage="Bank Accounts" />}
                action={
                    auth.countryOperation() && (
                        <Button variant="contained" onClick={addAccount} sx={{borderRadius: '5px'}}>
                            <AddIcon />
                        </Button>
                    )
                }
            />

            {modalElements}

            <CardContent>
                {auth.countryOperation() ? (
                    <LoadingFallback content={accounts} loading={loading}>
                        {(accounts) => (
                            <Grid container spacing={3}>
                                {accounts.map((account) => (
                                    <AccountItem
                                        key={account.id}
                                        remove={() => deleteAccount(account.id)}
                                        account={account}
                                    />
                                ))}
                            </Grid>
                        )}
                    </LoadingFallback>
                ) : (
                    <Result
                        title={
                            <FormattedMessage defaultMessage="Country Unsupported" />
                        }
                        description={
                            <FormattedMessage defaultMessage="We are currently not available in your country." />
                        }
                        icon={CountryIllustration}
                    />
                )}
            </CardContent>
        </Card>
    );
};

const AccountItem = ({account, remove}) => {
    const intl = useIntl();
    const createdAt = formatDate(account.created_at);

    return (
        <Grid item xs={12} sm={6} md={4}>
            <Paper variant="outlined" sx={{p: 1.5}}>
                <Stack direction="row" spacing={2}>
                    <BankLogo src={account.bank_logo} />

                    <Box sx={{width: "100%", my: 1, minWidth: 0}}>
                        <Typography variant="subtitle1" noWrap>
                            {account.bank_name}
                        </Typography>

                        <Typography variant="body2" noWrap>
                            {`${account.number} (${account.currency})`}
                        </Typography>

                        <Stack
                            direction="row"
                            justifyContent="space-between"
                            alignItems="center"
                            spacing={2}>
                            <Typography
                                noWrap
                                color="text.disabled"
                                variant="caption">
                                <FormattedMessage
                                    defaultMessage="added {date}"
                                    values={{date: createdAt}}
                                />
                            </Typography>

                            <PopConfirm
                                size="small"
                                component={IconButton}
                                content={intl.formatMessage(messages.confirm)}
                                onClick={remove}>
                                <DeleteIcon fontSize="inherit" />
                            </PopConfirm>
                        </Stack>
                    </Box>
                </Stack>
            </Paper>
        </Grid>
    );
};

export default BankAccounts;
