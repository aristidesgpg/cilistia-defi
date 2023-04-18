import React, {useMemo} from "react";
import {defineMessages, FormattedMessage, useIntl} from "react-intl";
import IconBuilder from "components/IconBuilder";
import WalletAccount from "models/WalletAccount";
import {useWalletAccounts} from "hooks/accounts";
import AddAccount from "../AddAccount";
import Balance from "./components/Balance";
import {
    Card,
    CardContent,
    CardHeader,
    Tooltip,
    Typography,
    useMediaQuery
} from "@mui/material";
import {experimentalStyled as styled} from "@mui/material/styles";
import Table from "components/Table";
import CircularProgress from "components/CircularProgress";

const messages = defineMessages({
    title: {defaultMessage: "Your Assets"},
    coin: {defaultMessage: "Coin"},
    balance: {defaultMessage: "Balance"},
    available: {defaultMessage: "Available"},
    quota: {defaultMessage: "Quota"}
});

const Assets = () => {
    const intl = useIntl();
    const {accounts, loading} = useWalletAccounts();

    const smDown = useMediaQuery((theme) => theme.breakpoints.down("sm"));

    const columns = useMemo(() => {
        return [
            {
                headerName: intl.formatMessage(messages.coin),
                field: "coin",
                minWidth: 100,
                flex: 1,
                renderCell: (params) => {
                    const account = WalletAccount.use(params.row);
                    const icon = account.wallet.coin.svgIcon();

                    return (
                        <Tooltip title={account.wallet.coin.name}>
                            <CoinWrapper>
                                <IconBuilder
                                    sx={{fontSize: "25px"}}
                                    icon={icon}
                                />

                                <Typography sx={{ml: 1}} noWrap>
                                    {account.wallet.coin.name}
                                </Typography>
                            </CoinWrapper>
                        </Tooltip>
                    );
                }
            },
            {
                headerName: intl.formatMessage(messages.available),
                field: "available",
                minWidth: 80,
                flex: 0.8,
                sortable: true,
                type: "number",
                headerAlign: "left",
                align: "left",
                renderCell: (params) => {
                    const account = WalletAccount.use(params.row);
                    return (
                        <BalanceStyle>
                            <Typography variant="subtitle2">
                                {account.available}
                            </Typography>

                            <Typography
                                sx={{color: "text.secondary"}}
                                variant="caption">
                                {account.formatted_available_price}
                            </Typography>
                        </BalanceStyle>
                    );
                }
            },
            {
                headerName: intl.formatMessage(messages.balance),
                field: "balance",
                minWidth: 80,
                flex: 0.8,
                sortable: true,
                type: "number",
                headerAlign: "left",
                align: "left",
                renderCell: (params) => {
                    const account = WalletAccount.use(params.row);
                    return (
                        <BalanceStyle>
                            <Typography variant="subtitle2">
                                {account.balance}
                            </Typography>

                            <Typography
                                sx={{color: "text.secondary"}}
                                variant="caption">
                                {account.formatted_balance_price}
                            </Typography>
                        </BalanceStyle>
                    );
                }
            },
            {
                field: "quota",
                headerName: intl.formatMessage(messages.quota),
                minWidth: 70,
                flex: 0.3,
                hide: smDown,
                align: "center",
                valueGetter: ({row}) => row.available_price_quota,
                sortable: true,
                renderCell: ({row: record}) => (
                    <CircularProgress
                        value={record.available_price_quota}
                        size={25}
                        thickness={8}
                    />
                )
            }
        ];
    }, [intl, smDown]);

    return (
        <Card>
            <CardHeader
                title={intl.formatMessage(messages.title)}
                action={<AddAccount />}
            />

            <CardContent>
                <TotalBalanceStyle sx={{my: 3, textAlign: "center"}}>
                    <Typography variant="h3">
                        <Balance />
                    </Typography>

                    <Typography variant="subtitle2">
                        <FormattedMessage defaultMessage="Total Balance" />
                    </Typography>
                </TotalBalanceStyle>
            </CardContent>

            <Table
                rows={accounts}
                columns={columns}
                loading={loading}
                rowHeight={60}
            />
        </Card>
    );
};

const CoinWrapper = styled("div")({
    display: "flex",
    flexGrow: 1,
    width: "100%",
    alignItems: "center",
    flexBasis: 0
});

const TotalBalanceStyle = styled("div")({
    display: "flex",
    flexDirection: "column"
});

const BalanceStyle = styled("div")({
    display: "flex",
    flexDirection: "column",
    alignItems: "start"
});

export default Assets;
