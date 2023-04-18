import React from "react";
import HeaderBreadcrumbs from "components/HeaderBreadcrumbs";
import {defineMessages, useIntl} from "react-intl";
import Page from "components/Page";
import AccountForm from "./components/AccountForm";
import {Container} from "@mui/material";

const messages = defineMessages({
    title: {defaultMessage: "Business Account"}
});

const Account = () => {
    const intl = useIntl();

    return (
        <Page title={intl.formatMessage(messages.title)}>
            <Container>
                <HeaderBreadcrumbs />
                <AccountForm />
            </Container>
        </Page>
    );
};

export default Account;
