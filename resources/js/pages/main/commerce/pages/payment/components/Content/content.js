import React, {useContext} from "react";
import {defineMessages, FormattedMessage, useIntl} from "react-intl";
import CommercePaymentContext from "contexts/CommercePaymentContext";
import HeaderBreadcrumbs from "components/HeaderBreadcrumbs";
import {
    Box,
    Card,
    Chip,
    Container,
    Grid,
    Stack,
    Typography
} from "@mui/material";
import Page from "components/Page";
import Divider from "@mui/material/Divider";
import Copyable from "components/Copyable";
import LinkCell from "components/TableCells/CommercePaymentTable/LinkCell";
import StatusCell from "components/TableCells/CommercePaymentTable/StatusCell";
import TypeCell from "components/TableCells/CommercePaymentTable/TypeCell";
import {formatNumber} from "utils/formatter";
import CommerceTransactionTable from "components/CommerceTransactionTable";
import {route} from "services/Http";

const messages = defineMessages({
    title: {defaultMessage: "{title} - Commerce Payment"}
});

const Content = () => {
    const intl = useIntl();
    const {payment, fetchPayment} = useContext(CommercePaymentContext);

    return (
        <Page
            title={intl.formatMessage(messages.title, {
                title: payment.title
            })}>
            <Container>
                <HeaderBreadcrumbs
                    title={payment.title}
                    action={
                        <StatusCell
                            payment={payment}
                            onChange={() => fetchPayment()}
                        />
                    }
                />

                <Grid container spacing={2}>
                    <Grid item xs={12} md={6}>
                        <ContentCard>
                            <ContentItem>
                                <Typography variant="subtitle1" noWrap>
                                    <FormattedMessage defaultMessage="Title" />
                                </Typography>

                                <Copyable
                                    variant="body2"
                                    color="text.secondary"
                                    ellipsis>
                                    {payment.title}
                                </Copyable>
                            </ContentItem>

                            <ContentItem>
                                <Typography variant="subtitle1" noWrap>
                                    <FormattedMessage defaultMessage="Description" />
                                </Typography>

                                <Copyable
                                    variant="body2"
                                    color="text.secondary"
                                    ellipsis>
                                    {payment.description}
                                </Copyable>
                            </ContentItem>
                        </ContentCard>
                    </Grid>

                    <Grid item xs={12} md={6}>
                        <ContentCard>
                            <ContentItem>
                                <Typography variant="subtitle1" noWrap>
                                    <FormattedMessage defaultMessage="Amount" />
                                </Typography>

                                <Copyable
                                    variant="body2"
                                    color="text.secondary">
                                    {payment.formatted_amount}
                                </Copyable>
                            </ContentItem>

                            <ContentItem spacing={0} alignItems="flex-start">
                                <Typography variant="subtitle1" noWrap>
                                    <FormattedMessage defaultMessage="Link" />
                                </Typography>

                                <LinkCell payment={payment} />
                            </ContentItem>
                        </ContentCard>
                    </Grid>

                    <Grid item xs={12} md={6}>
                        <ContentCard>
                            <ContentItem>
                                <Typography variant="subtitle1" noWrap>
                                    <FormattedMessage defaultMessage="Redirect" />
                                </Typography>

                                <Copyable
                                    variant="body2"
                                    color="text.secondary"
                                    ellipsis>
                                    {payment.redirect ?? "N/A"}
                                </Copyable>
                            </ContentItem>

                            <ContentItem>
                                <Typography variant="subtitle1" noWrap>
                                    <FormattedMessage defaultMessage="Message" />
                                </Typography>

                                <Copyable
                                    variant="body2"
                                    color="text.secondary"
                                    ellipsis>
                                    {payment.message ?? "N/A"}
                                </Copyable>
                            </ContentItem>
                        </ContentCard>
                    </Grid>

                    <Grid item xs={12} md={6}>
                        <ContentCard>
                            <ContentItem alignItems="flex-start">
                                <Typography variant="subtitle1" noWrap>
                                    <FormattedMessage defaultMessage="Type" />
                                </Typography>

                                <TypeCell type={payment.type} />
                            </ContentItem>

                            <ContentItem alignItems="flex-start">
                                <Typography variant="subtitle1" noWrap>
                                    <FormattedMessage defaultMessage="Transactions" />
                                </Typography>

                                <Copyable
                                    variant="body2"
                                    color="text.secondary">
                                    {formatNumber(payment.transactions_count)}
                                </Copyable>
                            </ContentItem>
                        </ContentCard>
                    </Grid>

                    <Grid item xs={12}>
                        <ContentCard>
                            <ContentItem spacing={0}>
                                <Typography variant="subtitle1" noWrap>
                                    <FormattedMessage defaultMessage="Accepted Coins" />
                                </Typography>

                                <Box sx={{mx: -0.5, mt: 1}}>
                                    {payment.wallets.map((wallet) => (
                                        <Chip
                                            key={wallet.id}
                                            label={wallet.coin.name}
                                            sx={{m: 0.5}}
                                        />
                                    ))}
                                </Box>
                            </ContentItem>
                        </ContentCard>
                    </Grid>

                    <Grid item xs={12}>
                        <Card>
                            <CommerceTransactionTable
                                url={route(
                                    "commerce-payment.transaction-paginate",
                                    {id: payment.id}
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
