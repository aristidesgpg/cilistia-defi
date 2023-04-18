import React, {useEffect, useMemo, useRef} from "react";
import {defineMessages, useIntl} from "react-intl";
import {useDispatch} from "react-redux";
import {useAuth} from "models/Auth";
import {usePrivateBroadcast} from "services/Broadcast";
import {fetchPaymentAccount} from "redux/slices/payment";
import {Card, CardHeader} from "@mui/material";
import {route} from "services/Http";
import AsyncTable from "components/AsyncTable";
import TrapScrollBox from "components/TrapScrollBox";
import CompactDateCell from "components/TableCells/CompactDateCell";
import StatusCell from "components/TableCells/PaymentTable/StatusCell";
import ValueCell from "components/TableCells/PaymentTable/ValueCell";
import DescriptionCell from "components/TableCells/PaymentTable/DescriptionCell";

const messages = defineMessages({
    transactions: {defaultMessage: "Transactions"},
    title: {defaultMessage: "Title"},
    status: {defaultMessage: "Status"},
    description: {defaultMessage: "Description"},
    date: {defaultMessage: "Date"},
    balance: {defaultMessage: "Balance"},
    available: {defaultMessage: "Available"},
    value: {defaultMessage: "Value"}
});

const Transaction = () => {
    const tableRef = useRef();
    const dispatch = useDispatch();
    const auth = useAuth();
    const broadcast = usePrivateBroadcast("App.Models.User." + auth.user.id);
    const intl = useIntl();

    useEffect(() => {
        const channel = "PaymentTransactionSaved";
        const handler = (e) => {
            dispatch(fetchPaymentAccount());
            tableRef.current.fetchData();
        };

        broadcast.listen(channel, handler);

        return () => {
            broadcast.stopListening(channel, handler);
        };
    }, [broadcast, dispatch]);

    const columns = useMemo(() => {
        return [
            {
                field: "created_at",
                type: "dateTime",
                width: 70,
                headerName: intl.formatMessage(messages.date),
                filterable: true,
                renderCell: ({value}) => <CompactDateCell value={value} />
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
            }
        ];
    }, [intl]);

    const url = route("payment.transaction-paginate");

    return (
        <Card>
            <CardHeader title={intl.formatMessage(messages.transactions)} />

            <TrapScrollBox>
                <AsyncTable ref={tableRef} columns={columns} url={url} />
            </TrapScrollBox>
        </Card>
    );
};

export default Transaction;
