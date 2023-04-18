import React, {useMemo} from "react";
import {defineMessages, useIntl} from "react-intl";
import {route} from "services/Http";
import {Card} from "@mui/material";
import ActionBar from "./components/ActionBar";
import TrapScrollBox from "components/TrapScrollBox";
import AsyncTable from "components/AsyncTable";
import DateCell from "components/TableCells/DateCell";
import StatusCell from "components/TableCells/PaymentTable/StatusCell";
import ValueCell from "components/TableCells/PaymentTable/ValueCell";
import DescriptionCell from "components/TableCells/PaymentTable/DescriptionCell";
import UserCell from "components/TableCells/UserCell";

const messages = defineMessages({
    transactions: {defaultMessage: "Transactions"},
    title: {defaultMessage: "Title"},
    status: {defaultMessage: "Status"},
    description: {defaultMessage: "Description"},
    date: {defaultMessage: "Date"},
    balance: {defaultMessage: "Balance"},
    available: {defaultMessage: "Available"},
    user: {defaultMessage: "User"},
    type: {defaultMessage: "Type"},
    value: {defaultMessage: "Value"}
});

const Transactions = () => {
    const intl = useIntl();

    const columns = useMemo(
        () => [
            {
                field: "account",
                width: 200,
                headerName: intl.formatMessage(messages.user),
                renderCell: ({value}) => <UserCell user={value.user} />
            },
            {
                field: "type",
                width: 70,
                align: "center",
                headerName: intl.formatMessage(messages.type),
                renderCell: ({row: transaction}) => (
                    <StatusCell transaction={transaction} />
                )
            },
            {
                field: "value",
                type: "number",
                minWidth: 100,
                flex: 0.5,
                headerName: intl.formatMessage(messages.value),
                renderCell: ({row: transaction}) => (
                    <ValueCell transaction={transaction} />
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
                field: "formatted_balance",
                minWidth: 100,
                flex: 0.5,
                headerName: intl.formatMessage(messages.balance)
            },
            {
                field: "created_at",
                headerName: intl.formatMessage(messages.date),
                width: 150,
                renderCell: ({value}) => <DateCell value={value} />
            }
        ],
        [intl]
    );

    const url = route("admin.payment-transaction.paginate");

    return (
        <Card>
            <TrapScrollBox>
                <AsyncTable
                    columns={columns}
                    components={{Toolbar: ActionBar}}
                    url={url}
                />
            </TrapScrollBox>
        </Card>
    );
};

export default Transactions;
