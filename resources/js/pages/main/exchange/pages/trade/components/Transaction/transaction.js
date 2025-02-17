import React, {useEffect, useMemo, useRef} from "react";
import {defineMessages, useIntl} from "react-intl";
import {useDispatch} from "react-redux";
import {useAuth} from "models/Auth";
import {usePrivateBroadcast} from "services/Broadcast";
import {useActiveWalletAccount} from "hooks/accounts";
import {fetchWalletAccounts} from "redux/slices/wallet";
import {fetchPaymentAccount} from "redux/slices/payment";
import {route} from "services/Http";
import {Card} from "@mui/material";
import AsyncTable from "components/AsyncTable";
import UserAvatar from "components/UserAvatar";
import TrapScrollBox from "components/TrapScrollBox";
import CompactDateCell from "components/TableCells/CompactDateCell";
import PaymentCell from "components/TableCells/ExchangeTable/PaymentCell";
import WalletCell from "components/TableCells/ExchangeTable/WalletCell";
import StatusCell from "components/TableCells/ExchangeTable/StatusCell";

const messages = defineMessages({
    transactions: {defaultMessage: "Transactions"},
    title: {defaultMessage: "Title"},
    date: {defaultMessage: "Date"},
    coin: {defaultMessage: "Coin"},
    status: {defaultMessage: "Status"},
    payment: {defaultMessage: "Payment"},
    wallet: {defaultMessage: "Wallet"},
    trader: {defaultMessage: "Trader"}
});

const Transaction = () => {
    const tableRef = useRef();
    const dispatch = useDispatch();
    const auth = useAuth();
    const broadcast = usePrivateBroadcast("App.Models.User." + auth.user.id);
    const intl = useIntl();
    const {account} = useActiveWalletAccount();

    useEffect(() => {
        const channel = "ExchangeTradeSaved";
        const handler = (e) => {
            dispatch(fetchWalletAccounts());
            dispatch(fetchPaymentAccount());
            tableRef.current.fetchData();
        };

        broadcast.listen(channel, handler);

        return () => {
            broadcast.stopListening(channel, handler);
        };
    }, [broadcast, dispatch]);

    const columns = useMemo(
        () => [
            {
                field: "created_at",
                type: "dateTime",
                width: 70,
                headerName: intl.formatMessage(messages.date),
                filterable: true,
                renderCell: ({value}) => <CompactDateCell value={value} />
            },
            {
                field: "status",
                width: 100,
                headerName: intl.formatMessage(messages.status),
                align: "center",
                renderCell: ({row: trade}) => <StatusCell trade={trade} />
            },
            {
                field: "payment_value",
                headerName: intl.formatMessage(messages.payment),
                minWidth: 100,
                flex: 0.5,
                renderCell: ({row: trade}) => <PaymentCell trade={trade} />
            },
            {
                field: "wallet_value",
                headerName: intl.formatMessage(messages.wallet),
                minWidth: 100,
                flex: 0.5,
                renderCell: ({row: trade}) => <WalletCell trade={trade} />
            },
            {
                field: "trader",
                headerName: intl.formatMessage(messages.trader),
                width: 100,
                align: "center",
                renderCell: ({value: user}) => <UserAvatar user={user} />
            }
        ],
        [intl]
    );

    const url = useMemo(() => {
        return account.isNotEmpty()
            ? route("exchange-trade.paginate", {account: account.get("id")})
            : route("exchange-trade.paginate");
    }, [account]);

    return (
        <Card>
            <TrapScrollBox>
                <AsyncTable ref={tableRef} columns={columns} url={url} />
            </TrapScrollBox>
        </Card>
    );
};

export default Transaction;
