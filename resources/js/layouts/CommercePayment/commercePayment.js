import React from "react";
import {Container, Grid, Stack} from "@mui/material";
import Sidebar from "./components/Sidebar";
import {experimentalStyled as styled} from "@mui/material/styles";
import {Helmet} from "react-helmet-async";
import context from "core/context";
import {defineMessages, useIntl} from "react-intl";
import useCommercePayment from "hooks/useCommercePayment";
import Header from "./components/Header";
import Divider from "@mui/material/Divider";
import {HEADER_CONFIG} from "layouts/config";
import Payment from "./components/Payment";

const messages = defineMessages({
    title: {defaultMessage: "{title} Payment"}
});

const CommercePayment = () => {
    const intl = useIntl();
    const {payment} = useCommercePayment();

    const title = intl.formatMessage(messages.title, {
        title: payment.title
    });

    return (
        <PageContent>
            <Helmet titleTemplate={`%s • ${context.name}`}>
                <title>{title}</title>
                <meta name="description" content={payment.description} />
            </Helmet>

            <Container>
                <Grid container spacing={{xs: 10, md: 3}}>
                    <Grid item xs={12} md={8}>
                        <Stack
                            spacing={2}
                            divider={<Divider sx={{borderStyle: "dashed"}} />}>
                            <Header />
                            <Payment />
                        </Stack>
                    </Grid>

                    <Grid item xs={12} md={4}>
                        <Sidebar />
                    </Grid>
                </Grid>
            </Container>
        </PageContent>
    );
};

const PageContent = styled("main")(({theme}) => ({
    paddingTop: HEADER_CONFIG.MOBILE_HEIGHT,
    paddingBottom: HEADER_CONFIG.MOBILE_HEIGHT,
    overflow: "auto",
    minHeight: "100%",
    flexGrow: 1,
    [theme.breakpoints.up("lg")]: {
        paddingTop: HEADER_CONFIG.DASHBOARD_DESKTOP_HEIGHT,
        paddingBottom: HEADER_CONFIG.DASHBOARD_DESKTOP_HEIGHT
    }
}));

export default CommercePayment;
