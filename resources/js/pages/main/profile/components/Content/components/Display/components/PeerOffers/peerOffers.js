import React, {useContext, useMemo, useRef} from "react";
import {Card, CardHeader} from "@mui/material";
import TrapScrollBox from "components/TrapScrollBox";
import AsyncTable from "components/AsyncTable";
import {defineMessages, FormattedMessage, useIntl} from "react-intl";
import PriceCell from "components/TableCells/PeerOfferTable/PriceCell";
import LimitCell from "components/TableCells/PeerOfferTable/LimitCell";
import PaymentCell from "components/TableCells/PeerOfferTable/PaymentCell";
import ActionButton from "./components/ActionButton";
import CoinCell from "components/TableCells/CoinCell";
import {route} from "services/Http";
import PropTypes from "prop-types";
import UserContext from "contexts/UserContext";
import {useAuth} from "models/Auth";
import StatusCell from "components/TableCells/PeerOfferTable/StatusCell";

const messages = defineMessages({
    price: {defaultMessage: "Price"},
    coin: {defaultMessage: "Coin"},
    limit: {defaultMessage: "Limit"},
    payment: {defaultMessage: "Payment"},
    action: {defaultMessage: "Action"},
    status: {defaultMessage: "Status"}
});

const PeerOffers = ({type}) => {
    const intl = useIntl();
    const auth = useAuth();
    const {user} = useContext(UserContext);
    const tableRef = useRef();

    const title = useMemo(() => {
        return type === "sell" ? (
            <FormattedMessage defaultMessage="Sell Offers" />
        ) : (
            <FormattedMessage defaultMessage="Buy Offers" />
        );
    }, [type]);

    const isManager = useMemo(() => {
        return auth.user.id === user.id || auth.user.can("manage_peer_trades");
    }, [auth, user]);

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
                field: "price",
                minWidth: 150,
                flex: 1,
                headerName: intl.formatMessage(messages.price),
                renderCell: ({row: data}) => <PriceCell offer={data} />
            },
            {
                field: "limit",
                minWidth: 130,
                flex: 1,
                headerName: intl.formatMessage(messages.limit),
                renderCell: ({row: data}) => <LimitCell offer={data} />
            },
            {
                field: "payment",
                minWidth: 130,
                flex: 1,
                headerName: intl.formatMessage(messages.payment),
                renderCell: ({row: data}) => <PaymentCell offer={data} />
            },
            {
                field: "status",
                minWidth: 100,
                flex: 0.5,
                headerName: intl.formatMessage(messages.status),
                align: "center",
                hide: !isManager,
                renderCell: ({row: data}) => <StatusCell offer={data} />
            },
            {
                field: "action",
                minWidth: 80,
                flex: 0.5,
                headerName: intl.formatMessage(messages.action),
                align: "right",
                renderCell: ({row: data}) => <ActionButton offer={data} />
            }
        ];
    }, [isManager, intl]);

    const url = useMemo(() => {
        const params = {user: user.name, type};
        return route("user-profile.peer-offer.paginate", params);
    }, [type, user]);

    return (
        <Card>
            <CardHeader title={title} />

            <TrapScrollBox>
                <AsyncTable ref={tableRef} columns={columns} url={url} />
            </TrapScrollBox>
        </Card>
    );
};

PeerOffers.propTypes = {
    type: PropTypes.oneOf(["buy", "sell"])
};

export default PeerOffers;
