import React from "react";
import {defineMessages, useIntl} from "react-intl";
import ResponsiveWidgets from "components/ResponsiveWidgets";
import widgets from "./widgets";
import Page from "components/Page";
import {Container} from "@mui/material";

const messages = defineMessages({
    title: {defaultMessage: "Home"}
});

const Home = () => {
    const intl = useIntl();
    return (
        <Page title={intl.formatMessage(messages.title)}>
            <Container maxWidth="xl" disableGutters>
                <ResponsiveWidgets widgets={widgets} page="admin.home" />
            </Container>
        </Page>
    );
};

export default Home;
