import React, {useMemo} from "react";
import {defineMessages, FormattedMessage, useIntl} from "react-intl";
import AmountCell from "components/TableCells/PeerTradeTable/AmountCell";
import CoinCell from "components/TableCells/CoinCell";
import PaymentCell from "components/TableCells/PeerTradeTable/PaymentCell";
import TraderCell from "components/TableCells/PeerTradeTable/TraderCell";
import ActionCell from "components/TableCells/PeerTradeTable/ActionCell";
import {route} from "services/Http";
import AsyncTable from "components/AsyncTable";
import PropTypes from "prop-types";
import {CardHeader, Stack, Typography} from "@mui/material";
import Label from "components/Label";
import TrapScrollBox from "components/TrapScrollBox";
import ResponsiveCard from "components/ResponsiveWidgets/responsiveCard";

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

const PeerTradesTable = ({type, status}) => {
    const intl = useIntl();

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
                field: "action",
                minWidth: 100,
                flex: 0.5,
                align: "right",
                headerName: intl.formatMessage(messages.action),
                renderCell: ({row: data}) => <ActionCell trade={data} />
            }
        ];
    }, [intl, type]);

    const url = useMemo(() => {
        if (type === "sell") {
            return route("peer-trade.sell-paginate", {status});
        } else {
            return route("peer-trade.buy-paginate", {status});
        }
    }, [type, status]);

    return (
        <ResponsiveCard>
            <CardHeader
                title={
                    <Stack direction="row" alignItems="center" spacing={1}>
                        <Typography variant="inherit">
                            <FormattedMessage defaultMessage="Trades" />
                        </Typography>

                        {type === "sell" ? (
                            <Label color="error">
                                <FormattedMessage defaultMessage="Sell" />
                            </Label>
                        ) : (
                            <Label color="success">
                                <FormattedMessage defaultMessage="Buy" />
                            </Label>
                        )}
                    </Stack>
                }
            />

            <TrapScrollBox sx={{flexGrow: 1}}>
                <AsyncTable columns={columns} url={url} />
            </TrapScrollBox>
        </ResponsiveCard>
    );
};

PeerTradesTable.propTypes = {
    status: PropTypes.oneOf(["active", "completed", "canceled", "disputed"]),
    type: PropTypes.oneOf(["buy", "sell"])
};

export default PeerTradesTable;
