import React, {useEffect, useMemo, useRef} from "react";
import ResponsiveCard from "../ResponsiveWidgets/responsiveCard";
import {defineMessages, FormattedMessage, useIntl} from "react-intl";
import {CardHeader} from "@mui/material";
import {useDispatch} from "react-redux";
import {useAuth} from "models/Auth";
import {usePrivateBroadcast} from "services/Broadcast";
import {route} from "services/Http";
import AsyncTable from "../AsyncTable";
import TrapScrollBox from "../TrapScrollBox";
import DateCell from "components/TableCells/DateCell";
import DescriptionCell from "components/TableCells/WalletTable/DescriptionCell";
import StatusCell from "components/TableCells/WalletTable/StatusCell";
import ValueCell from "components/TableCells/WalletTable/ValueCell";
import CoinCell from "components/TableCells/CoinCell";

const messages = defineMessages({
    transactions: {defaultMessage: "Transactions"},
    title: {defaultMessage: "Title"},
    description: {defaultMessage: "Description"},
    date: {defaultMessage: "Date"},
    hash: {defaultMessage: "Hash"},
    transactionHash: {defaultMessage: "Transaction Hash"},
    coin: {defaultMessage: "Coin"},
    balance: {defaultMessage: "Balance"},
    status: {defaultMessage: "Status"},
    available: {defaultMessage: "Available"},
    value: {defaultMessage: "Value"}
});

const RecentTransaction = () => {
    const tableRef = useRef();
    const auth = useAuth();
    const dispatch = useDispatch();
    const intl = useIntl();
    const broadcast = usePrivateBroadcast("App.Models.User." + auth.user.id);

    useEffect(() => {
        const channel = "TransferRecordSaved";
        const handler = (e) => tableRef.current.fetchData();

        broadcast.listen(channel, handler);

        return () => {
            broadcast.stopListening(channel, handler);
        };
    }, [broadcast, dispatch]);

    const columns = useMemo(() => {
        return [
            {
                field: "coin",
                width: 70,
                headerName: intl.formatMessage(messages.coin),
                align: "center",
                renderCell: ({value}) => <CoinCell value={value} />
            },
            {
                field: "value",
                headerName: intl.formatMessage(messages.value),
                type: "number",
                minWidth: 100,
                flex: 0.5,
                filterable: true,
                renderCell: ({row: transaction}) => (
                    <ValueCell transaction={transaction} />
                )
            },
            {
                field: "type",
                width: 70,
                headerName: intl.formatMessage(messages.status),
                align: "center",
                renderCell: ({row: transaction}) => (
                    <StatusCell transaction={transaction} />
                )
            },
            {
                field: "description",
                minWidth: 200,
                flex: 1,
                headerName: intl.formatMessage(messages.description),
                filterable: true,
                renderCell: ({row: transaction}) => (
                    <DescriptionCell transaction={transaction} />
                )
            },
            {
                field: "created_at",
                headerName: intl.formatMessage(messages.date),
                width: 150,
                type: "dateTime",
                filterable: true,
                renderCell: ({value}) => <DateCell value={value} />
            }
        ];
    }, [intl]);

    const url = route("transfer-record.paginate");

    return (
        <ResponsiveCard>
            <CardHeader
                title={<FormattedMessage defaultMessage="Recent Transaction" />}
            />

            <TrapScrollBox sx={{flexGrow: 1}}>
                <AsyncTable
                    ref={tableRef}
                    columns={columns}
                    autoHeight={false}
                    url={url}
                />
            </TrapScrollBox>
        </ResponsiveCard>
    );
};

RecentTransaction.dimensions = {
    lg: {w: 8, h: 3, minW: 8, minH: 3},
    md: {w: 6, h: 3, minW: 6, minH: 3},
    sm: {w: 2, h: 3, minW: 2, minH: 3},
    xs: {w: 1, h: 3, minW: 1, minH: 3}
};

export default RecentTransaction;
