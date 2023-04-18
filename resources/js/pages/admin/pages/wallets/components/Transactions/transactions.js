import React, {useMemo} from "react";
import {route} from "services/Http";
import {defineMessages, useIntl} from "react-intl";
import {Card, Stack, Tooltip, Typography} from "@mui/material";
import ActionBar from "./components/ActionBar";
import TrapScrollBox from "components/TrapScrollBox";
import AsyncTable from "components/AsyncTable";
import RecordDelete from "./components/RecordDelete";
import DateCell from "components/TableCells/DateCell";
import UserCell from "components/TableCells/UserCell";
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
    available: {defaultMessage: "Available"},
    user: {defaultMessage: "User"},
    action: {defaultMessage: "Action"},
    type: {defaultMessage: "Type"},
    value: {defaultMessage: "Value"}
});

const Transactions = () => {
    const intl = useIntl();

    const columns = useMemo(
        () => [
            {
                field: "wallet_account",
                width: 200,
                headerName: intl.formatMessage(messages.user),
                renderCell: ({value}) => <UserCell user={value?.user} />
            },
            {
                field: "type",
                width: 70,
                headerName: intl.formatMessage(messages.type),
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
                filterable: true,
                renderCell: ({row: transaction}) => (
                    <ValueCell transaction={transaction} />
                )
            },
            {
                field: "coin",
                width: 70,
                headerName: intl.formatMessage(messages.coin),
                align: "center",
                renderCell: ({value}) => <CoinCell value={value} />
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
                field: "balance",
                type: "number",
                minWidth: 100,
                flex: 0.5,
                headerName: intl.formatMessage(messages.balance),
                filterable: true,
                renderCell: ({row}) => {
                    return (
                        <Tooltip title={row.balance}>
                            <Stack alignItems="flex-end" sx={{width: "100%"}}>
                                <Typography
                                    sx={{whiteSpace: "nowrap"}}
                                    variant="body2">
                                    {row.balance}
                                </Typography>

                                <Typography
                                    color="text.secondary"
                                    sx={{whiteSpace: "nowrap"}}
                                    variant="caption">
                                    {row.formatted_balance_price}
                                </Typography>
                            </Stack>
                        </Tooltip>
                    );
                }
            },
            {
                field: "created_at",
                headerName: intl.formatMessage(messages.date),
                width: 150,
                renderCell: ({value}) => <DateCell value={value} />
            },
            {
                field: "action",
                minWidth: 100,
                flex: 0.5,
                headerAlign: "right",
                headerName: intl.formatMessage(messages.action),
                align: "right",
                renderCell: ({row: record}) => {
                    return (
                        <Stack direction="row" spacing={1}>
                            <RecordDelete record={record} />
                        </Stack>
                    );
                }
            }
        ],
        [intl]
    );

    const url = route("admin.transfer-record.paginate");

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
