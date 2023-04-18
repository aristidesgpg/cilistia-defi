import React, {useMemo} from "react";
import {defineMessages, useIntl} from "react-intl";
import AmountCell from "components/TableCells/PeerTradeTable/AmountCell";
import CoinCell from "components/TableCells/CoinCell";
import PriceCell from "components/TableCells/PeerTradeTable/PriceCell";
import PaymentCell from "components/TableCells/PeerTradeTable/PaymentCell";
import TraderCell from "components/TableCells/PeerTradeTable/TraderCell";
import StatusCell from "components/TableCells/PeerTradeTable/StatusCell";
import AsyncTable from "components/AsyncTable";
import TrapScrollBox from "components/TrapScrollBox";
import {route} from "services/Http";
import PropTypes from "prop-types";
import ActionButton from "./components/ActionButton";
import {isString} from "lodash";

const messages = defineMessages({
    buyer: {defaultMessage: "Buyer"},
    seller: {defaultMessage: "Seller"},
    coin: {defaultMessage: "Coin"},
    status: {defaultMessage: "Status"},
    amount: {defaultMessage: "Amount"},
    payment: {defaultMessage: "Payment"},
    action: {defaultMessage: "Action"},
    price: {defaultMessage: "Price"}
});

const Table = ({status}) => {
    const intl = useIntl();

    const columns = useMemo(() => {
        return [
            {
                field: "buyer",
                minWidth: 200,
                flex: 1,
                headerName: intl.formatMessage(messages.buyer),
                renderCell: ({value}) => <TraderCell user={value} />
            },
            {
                field: "seller",
                minWidth: 200,
                flex: 1,
                headerName: intl.formatMessage(messages.seller),
                renderCell: ({value}) => <TraderCell user={value} />
            },
            {
                field: "amount",
                minWidth: 150,
                flex: 1,
                headerName: intl.formatMessage(messages.amount),
                renderCell: ({row: data}) => <AmountCell trade={data} />
            },
            {
                field: "coin",
                width: 70,
                align: "center",
                headerName: intl.formatMessage(messages.coin),
                renderCell: ({value}) => <CoinCell value={value} />
            },
            {
                field: "price",
                minWidth: 150,
                flex: 1,
                headerName: intl.formatMessage(messages.price),
                renderCell: ({row: data}) => <PriceCell trade={data} />
            },
            {
                field: "payment",
                minWidth: 130,
                flex: 1,
                headerName: intl.formatMessage(messages.payment),
                renderCell: ({row: data}) => <PaymentCell trade={data} />
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
                field: "action",
                minWidth: 120,
                flex: 0.5,
                align: "right",
                headerName: intl.formatMessage(messages.action),
                hide: status !== "disputed",
                renderCell: ({row: data}) => <ActionButton trade={data} />
            }
        ];
    }, [intl, status]);

    const url = useMemo(() => {
        return route("admin.peer-trade.paginate", {status});
    }, [status]);

    return (
        <TrapScrollBox>
            <AsyncTable columns={columns} url={url} />
        </TrapScrollBox>
    );
};

Table.propTypes = {
    status: PropTypes.oneOf(["active", "completed", "canceled", "disputed"])
};

export default Table;
