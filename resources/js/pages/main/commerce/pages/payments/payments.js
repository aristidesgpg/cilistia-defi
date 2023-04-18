import React, {useMemo} from "react";
import {defineMessages, useIntl} from "react-intl";
import {route} from "services/Http";
import {Card, Container, Stack} from "@mui/material";
import AsyncTable from "components/AsyncTable";
import HeaderBreadcrumbs from "components/HeaderBreadcrumbs";
import Page from "components/Page";
import TypeCell from "components/TableCells/CommercePaymentTable/TypeCell";
import ActionBar from "./components/ActionBar";
import PaymentEdit from "./components/PaymentEdit";
import PaymentDelete from "./components/PaymentDelete";
import StatusCell from "components/TableCells/CommercePaymentTable/StatusCell";
import LinkCell from "components/TableCells/CommercePaymentTable/LinkCell";
import PaymentView from "./components/PaymentView";
import {formatDate} from "utils/formatter";

const messages = defineMessages({
    page: {defaultMessage: "Commerce Payments"},
    title: {defaultMessage: "Title"},
    description: {defaultMessage: "Description"},
    type: {defaultMessage: "Type"},
    amount: {defaultMessage: "Amount"},
    transactions: {defaultMessage: "Transactions"},
    link: {defaultMessage: "Link"},
    created: {defaultMessage: "Created"},
    status: {defaultMessage: "Status"},
    action: {defaultMessage: "Action"}
});

const Payments = () => {
    const intl = useIntl();

    const columns = useMemo(
        () => [
            {
                field: "title",
                minWidth: 150,
                flex: 1,
                headerName: intl.formatMessage(messages.title)
            },
            {
                field: "formatted_amount",
                minWidth: 100,
                flex: 0.5,
                headerName: intl.formatMessage(messages.amount)
            },
            {
                field: "type",
                minWidth: 100,
                flex: 0.5,
                align: "center",
                headerName: intl.formatMessage(messages.type),
                headerAlign: "center",
                renderCell: ({value}) => <TypeCell type={value} />
            },
            {
                field: "status",
                minWidth: 150,
                flex: 0.5,
                align: "center",
                headerName: intl.formatMessage(messages.status),
                headerAlign: "center",
                renderCell: ({row: payment}) => <StatusCell payment={payment} />
            },
            {
                field: "link",
                minWidth: 150,
                flex: 0.5,
                headerName: intl.formatMessage(messages.link),
                renderCell: ({row: payment}) => <LinkCell payment={payment} />
            },
            {
                field: "created_at",
                minWidth: 150,
                flex: 0.5,
                headerName: intl.formatMessage(messages.created),
                renderCell: ({value}) => formatDate(value, "lll")
            },
            {
                field: "action",
                minWidth: 150,
                flex: 0.5,
                align: "right",
                headerName: intl.formatMessage(messages.action),
                headerAlign: "right",
                renderCell: ({row: payment}) => {
                    return (
                        <Stack direction="row" spacing={1}>
                            <PaymentDelete payment={payment} />
                            <PaymentView payment={payment} />
                            <PaymentEdit payment={payment} />
                        </Stack>
                    );
                }
            }
        ],
        [intl]
    );

    const url = route("commerce-payment.paginate");

    return (
        <Page title={intl.formatMessage(messages.page)}>
            <Container>
                <HeaderBreadcrumbs />

                <Card>
                    <AsyncTable
                        columns={columns}
                        components={{Toolbar: ActionBar}}
                        url={url}
                    />
                </Card>
            </Container>
        </Page>
    );
};

export default Payments;
