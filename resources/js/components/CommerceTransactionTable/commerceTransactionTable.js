import React, {useMemo} from "react";
import {defineMessages, useIntl} from "react-intl";
import AsyncTable from "components/AsyncTable";
import {route} from "services/Http";
import {defaultTo, isString} from "lodash";
import AmountCell from "components/TableCells/CommerceTransactionTable/AmountCell";
import ReceivedCell from "components/TableCells/CommerceTransactionTable/ReceivedCell";
import CustomerCell from "components/TableCells/CommerceTransactionTable/CustomerCell";
import StatusCell from "components/TableCells/CommerceTransactionTable/StatusCell";
import DateCell from "components/TableCells/DateCell";
import CoinCell from "components/TableCells/CoinCell";
import {Chip} from "@mui/material";
import Copyable from "components/Copyable";

const messages = defineMessages({
    date: {defaultMessage: "Created"},
    coin: {defaultMessage: "Coin"},
    address: {defaultMessage: "Address"},
    received: {defaultMessage: "Received"},
    customer: {defaultMessage: "Customer"},
    amount: {defaultMessage: "Amount"},
    status: {defaultMessage: "Status"}
});

const CommerceTransactionTable = ({url, status}) => {
    const intl = useIntl();

    url = defaultTo(url, route("commerce-transaction.paginate", {status}));

    const columns = useMemo(
        () => [
            {
                field: "wallet",
                width: 70,
                align: "center",
                headerName: intl.formatMessage(messages.coin),
                renderCell: ({value}) => <CoinCell value={value.coin} />
            },
            {
                field: "customer",
                minWidth: 150,
                flex: 1,
                headerName: intl.formatMessage(messages.customer),
                renderCell: ({row: transaction}) => (
                    <CustomerCell transaction={transaction} />
                )
            },
            {
                field: "value",
                minWidth: 100,
                flex: 0.5,
                headerName: intl.formatMessage(messages.amount),
                renderCell: ({row: transaction}) => (
                    <AmountCell transaction={transaction} />
                )
            },
            {
                field: "received",
                minWidth: 100,
                flex: 0.5,
                headerName: intl.formatMessage(messages.received),
                renderCell: ({row: transaction}) => (
                    <ReceivedCell transaction={transaction} />
                )
            },
            {
                field: "address",
                minWidth: 150,
                flex: 1,
                headerName: intl.formatMessage(messages.address),
                renderCell: ({value}) => (
                    <Copyable component="div" text={value} ellipsis>
                        <Chip variant="outlined" label={value} />
                    </Copyable>
                )
            },
            {
                field: "status",
                minWidth: 100,
                flex: 0.5,
                align: "center",
                headerName: intl.formatMessage(messages.status),
                hide: isString(status),
                renderCell: ({value}) => <StatusCell status={value} />
            },
            {
                field: "created_at",
                minWidth: 100,
                flex: 0.5,
                type: "dateTime",
                filterable: true,
                headerName: intl.formatMessage(messages.date),
                renderCell: ({value}) => <DateCell value={value} />
            }
        ],
        [intl, status]
    );

    return <AsyncTable columns={columns} url={url} />;
};

export default CommerceTransactionTable;
