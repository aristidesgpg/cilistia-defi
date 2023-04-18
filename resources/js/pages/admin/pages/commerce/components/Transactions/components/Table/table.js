import React, {useMemo} from "react";
import {defineMessages, useIntl} from "react-intl";
import {route} from "services/Http";
import CoinCell from "components/TableCells/CoinCell";
import CustomerCell from "components/TableCells/CommerceTransactionTable/CustomerCell";
import AmountCell from "components/TableCells/CommerceTransactionTable/AmountCell";
import ReceivedCell from "components/TableCells/CommerceTransactionTable/ReceivedCell";
import Copyable from "components/Copyable";
import {Chip} from "@mui/material";
import {isString} from "lodash";
import StatusCell from "components/TableCells/CommerceTransactionTable/StatusCell";
import DateCell from "components/TableCells/DateCell";
import AsyncTable from "components/AsyncTable";
import TrapScrollBox from "components/TrapScrollBox";
import UserCell from "components/TableCells/UserCell";
import ActionBar from "./components/ActionBar";

const messages = defineMessages({
    user: {defaultMessage: "User"},
    date: {defaultMessage: "Created"},
    coin: {defaultMessage: "Coin"},
    address: {defaultMessage: "Address"},
    received: {defaultMessage: "Received"},
    customer: {defaultMessage: "Customer"},
    amount: {defaultMessage: "Amount"},
    status: {defaultMessage: "Status"}
});

const Table = ({status}) => {
    const intl = useIntl();

    const columns = useMemo(
        () => [
            {
                field: "wallet_account",
                width: 200,
                headerName: intl.formatMessage(messages.user),
                renderCell: ({value}) => <UserCell user={value.user} />
            },
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

    const url = route("admin.commerce-transaction.paginate", {status});

    return (
        <TrapScrollBox>
            <AsyncTable
                columns={columns}
                components={{Toolbar: ActionBar}}
                url={url}
            />
        </TrapScrollBox>
    );
};

export default Table;
