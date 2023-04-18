import React, {useMemo, useRef} from "react";
import PropTypes from "prop-types";
import AsyncTable from "components/AsyncTable";
import TrapScrollBox from "components/TrapScrollBox";
import {defineMessages, useIntl} from "react-intl";
import TraderCell from "components/TableCells/PeerTradeTable/TraderCell";
import StatusCell from "components/TableCells/PeerTradeTable/StatusCell";
import AmountCell from "components/TableCells/PeerTradeTable/AmountCell";
import PriceCell from "components/TableCells/PeerTradeTable/PriceCell";
import PaymentCell from "components/TableCells/PeerTradeTable/PaymentCell";
import ActionCell from "components/TableCells/PeerTradeTable/ActionCell";
import CoinCell from "components/TableCells/CoinCell";
import {route} from "services/Http";
import {isString} from "lodash";

const messages = defineMessages({
    buyer: {defaultMessage: "Buyer"},
    seller: {defaultMessage: "Seller"},
    status: {defaultMessage: "Status"},
    amount: {defaultMessage: "Amount"},
    coin: {defaultMessage: "Coin"},
    action: {defaultMessage: "Action"},
    payment: {defaultMessage: "Payment"},
    price: {defaultMessage: "Price"}
});

const Table = ({type, status}) => {
    const intl = useIntl();
    const tableRef = useRef();

    const columns = useMemo(() => {
        return [
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
                field: "buyer",
                minWidth: 200,
                flex: 1,
                headerName: intl.formatMessage(messages.buyer),
                hide: type !== "sell",
                renderCell: ({value}) => <TraderCell user={value} />
            },
            {
                field: "seller",
                minWidth: 200,
                flex: 1,
                headerName: intl.formatMessage(messages.seller),
                hide: type === "sell",
                renderCell: ({value}) => <TraderCell user={value} />
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
                renderCell: ({row: data}) => <ActionCell trade={data} />
            }
        ];
    }, [intl, type, status]);

    const url = useMemo(() => {
        if (type === "sell") {
            return route("peer-trade.sell-paginate", {status});
        } else {
            return route("peer-trade.buy-paginate", {status});
        }
    }, [type, status]);

    return (
        <TrapScrollBox>
            <AsyncTable ref={tableRef} columns={columns} url={url} />
        </TrapScrollBox>
    );
};

Table.propTypes = {
    status: PropTypes.oneOf(["active", "completed", "canceled", "disputed"]),
    type: PropTypes.oneOf(["buy", "sell"])
};

export default Table;
