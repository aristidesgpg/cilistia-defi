import React from "react";
import widgets from "./widgets";
import ResponsiveWidgets from "components/ResponsiveWidgets";
import Page from "components/Page";
import {useIntl, defineMessages} from "react-intl";
import {Container} from "@mui/material";

const messages = defineMessages({
    title: {defaultMessage: "Home"}
});

const Home = () => {
    const intl = useIntl();
    return (
        <Page title={intl.formatMessage(messages.title)}>
            <Container maxWidth="xl" disableGutters>
                <ResponsiveWidgets widgets={widgets} page="index.home" />
            </Container>
        </Page>
    );
};

export default Home;
