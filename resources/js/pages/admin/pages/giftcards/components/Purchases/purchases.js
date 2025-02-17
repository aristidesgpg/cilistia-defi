import React, {useMemo} from "react";
import {route} from "services/Http";
import {defineMessages, useIntl} from "react-intl";
import {Card, Stack, Typography} from "@mui/material";
import TrapScrollBox from "components/TrapScrollBox";
import AsyncTable from "components/AsyncTable";
import GiftcardThumbnail from "components/GiftcardThumbnail";
import DateCell from "components/TableCells/DateCell";
import UserCell from "components/TableCells/UserCell";
import ActionBar from "./components/ActionBar";

const messages = defineMessages({
    name: {defaultMessage: "Giftcard"},
    item: {defaultMessage: "Item"},
    brand: {defaultMessage: "Brand"},
    title: {defaultMessage: "Title"},
    bought: {defaultMessage: "Bought At"},
    label: {defaultMessage: "Label"},
    description: {defaultMessage: "Description"},
    instruction: {defaultMessage: "Instruction"},
    price: {defaultMessage: "Price"},
    quantity: {defaultMessage: "Quantity"},
    serial: {defaultMessage: "Serial"},
    code: {defaultMessage: "Code"},
    value: {defaultMessage: "Value"},
    buyer: {defaultMessage: "Buyer"},
    total: {defaultMessage: "Total"}
});

const Purchases = () => {
    const intl = useIntl();

    const columns = useMemo(
        () => [
            {
                field: "thumbnail",
                width: 70,
                headerName: intl.formatMessage(messages.item),
                align: "center",
                renderCell: ({row}) => (
                    <GiftcardThumbnail src={row.giftcard.thumbnail} />
                )
            },
            {
                field: "buyer",
                minWidth: 200,
                flex: 1,
                headerName: intl.formatMessage(messages.buyer),
                renderCell: ({value: user}) => <UserCell user={user} />
            },
            {
                field: "title",
                minWidth: 150,
                flex: 1,
                headerName: intl.formatMessage(messages.title),
                renderCell: ({row}) => {
                    return (
                        <Stack sx={{minWidth: 0}}>
                            <Typography variant="subtitle2" noWrap>
                                {row.giftcard.title}
                            </Typography>

                            <Typography
                                variant="body2"
                                sx={{color: "text.secondary"}}
                                noWrap>
                                {row.giftcard.label}
                            </Typography>
                        </Stack>
                    );
                }
            },
            {
                field: "serial",
                flex: 1,
                minWidth: 200,
                headerName: intl.formatMessage(messages.serial)
            },
            {
                field: "bought_at",
                headerName: intl.formatMessage(messages.bought),
                minWidth: 150,
                flex: 0.5,
                renderCell: ({value}) => <DateCell value={value} />
            }
        ],
        [intl]
    );

    const url = route("admin.giftcard-content.purchased-paginate");

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

export default Purchases;
