import React, {useMemo} from "react";
import {defineMessages, useIntl} from "react-intl";
import Copyable from "components/Copyable";
import HeaderBreadcrumbs from "components/HeaderBreadcrumbs";
import Page from "components/Page";
import {Card, Container, Stack} from "@mui/material";
import AsyncTable from "components/AsyncTable";
import {route} from "services/Http";
import ActionBar from "./components/ActionBar";
import CustomerDelete from "./components/CustomerDelete";
import CustomerEdit from "./components/CustomerEdit";
import CustomerView from "./components/CustomerView";
import {formatDate} from "utils/formatter";

const messages = defineMessages({
    title: {defaultMessage: "Commerce Customers"},
    firstName: {defaultMessage: "First Name"},
    lastName: {defaultMessage: "Last Name"},
    email: {defaultMessage: "Email"},
    created: {defaultMessage: "Created"},
    action: {defaultMessage: "Action"}
});

const Customers = () => {
    const intl = useIntl();

    const columns = useMemo(
        () => [
            {
                field: "first_name",
                minWidth: 100,
                flex: 1,
                headerName: intl.formatMessage(messages.firstName)
            },
            {
                field: "last_name",
                minWidth: 100,
                flex: 1,
                headerName: intl.formatMessage(messages.lastName)
            },
            {
                field: "email",
                minWidth: 100,
                flex: 1,
                headerName: intl.formatMessage(messages.email),
                renderCell: ({value}) => <Copyable ellipsis>{value}</Copyable>
            },
            {
                field: "created_at",
                minWidth: 150,
                flex: 1,
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
                renderCell: ({row: customer}) => {
                    return (
                        <Stack direction="row" spacing={1}>
                            <CustomerDelete customer={customer} />
                            <CustomerView customer={customer} />
                            <CustomerEdit customer={customer} />
                        </Stack>
                    );
                }
            }
        ],
        [intl]
    );

    const url = route("commerce-customer.paginate");

    return (
        <Page title={intl.formatMessage(messages.title)}>
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

export default Customers;
