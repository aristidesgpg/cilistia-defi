import React, {useContext} from "react";
import {defineMessages, FormattedMessage, useIntl} from "react-intl";
import CommerceCustomerContext from "contexts/CommerceCustomerContext";
import Page from "components/Page";
import HeaderBreadcrumbs from "components/HeaderBreadcrumbs";
import {Card, Container, Grid, Stack, Typography} from "@mui/material";
import Divider from "@mui/material/Divider";
import Copyable from "components/Copyable";
import {formatNumber} from "utils/formatter";
import CommerceTransactionTable from "components/CommerceTransactionTable";
import {route} from "services/Http";

const messages = defineMessages({
    title: {defaultMessage: "{name} - Commerce Customer"}
});

const Content = () => {
    const intl = useIntl();
    const {customer} = useContext(CommerceCustomerContext);

    return (
        <Page
            title={intl.formatMessage(messages.title, {
                name: customer.first_name
            })}>
            <Container>
                <HeaderBreadcrumbs title={customer.first_name} />

                <Grid container spacing={2}>
                    <Grid item xs={12} md={6}>
                        <ContentCard>
                            <ContentItem>
                                <Typography variant="subtitle1" noWrap>
                                    <FormattedMessage defaultMessage="First Name" />
                                </Typography>

                                <Copyable
                                    variant="body2"
                                    color="text.secondary"
                                    ellipsis>
                                    {customer.first_name}
                                </Copyable>
                            </ContentItem>

                            <ContentItem>
                                <Typography variant="subtitle1" noWrap>
                                    <FormattedMessage defaultMessage="Last Name" />
                                </Typography>

                                <Copyable
                                    variant="body2"
                                    color="text.secondary"
                                    ellipsis>
                                    {customer.last_name}
                                </Copyable>
                            </ContentItem>
                        </ContentCard>
                    </Grid>

                    <Grid item xs={12} md={6}>
                        <ContentCard>
                            <ContentItem>
                                <Typography variant="subtitle1" noWrap>
                                    <FormattedMessage defaultMessage="Email" />
                                </Typography>

                                <Copyable
                                    variant="body2"
                                    color="text.secondary"
                                    ellipsis>
                                    {customer.email}
                                </Copyable>
                            </ContentItem>

                            <ContentItem>
                                <Typography variant="subtitle1" noWrap>
                                    <FormattedMessage defaultMessage="Transactions" />
                                </Typography>

                                <Copyable
                                    variant="body2"
                                    color="text.secondary">
                                    {formatNumber(customer.transactions_count)}
                                </Copyable>
                            </ContentItem>
                        </ContentCard>
                    </Grid>

                    <Grid item xs={12}>
                        <Card>
                            <CommerceTransactionTable
                                url={route(
                                    "commerce-customer.transaction-paginate",
                                    {id: customer.id}
                                )}
                            />
                        </Card>
                    </Grid>
                </Grid>
            </Container>
        </Page>
    );
};

const ContentCard = (props) => {
    return (
        <Card>
            <Stack
                direction="row"
                divider={
                    <Divider
                        orientation="vertical"
                        sx={{borderStyle: "dashed"}}
                        flexItem={true}
                    />
                }
                sx={{minHeight: 90, p: 2}}
                spacing={2}
                {...props}
            />
        </Card>
    );
};

const ContentItem = (props) => {
    return (
        <Stack
            spacing={0.5}
            justifyContent="space-between"
            sx={{minWidth: 0, flex: 1}}
            {...props}
        />
    );
};

export default Content;
