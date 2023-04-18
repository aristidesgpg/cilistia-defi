import React, {forwardRef, useCallback, useContext} from "react";
import {Box, Stack, ToggleButton, Typography} from "@mui/material";
import {defineMessages, FormattedMessage, useIntl} from "react-intl";
import Form, {ToggleButtonGroup} from "components/Form";
import useCommercePayment from "hooks/useCommercePayment";
import Wallet from "models/Wallet";
import {experimentalStyled as styled} from "@mui/material/styles";
import IconBuilder from "components/IconBuilder";
import {LoadingButton} from "@mui/lab";
import {errorHandler, route, useFormRequest} from "services/Http";
import CommerceCustomerContext from "contexts/CommerceCustomerContext";

const messages = defineMessages({
    crypto: {defaultMessage: "Crypto"}
});

const TransactionCreate = forwardRef(({setTransaction}, ref) => {
    const intl = useIntl();
    const {payment} = useCommercePayment();
    const {customer} = useContext(CommerceCustomerContext);
    const [form] = Form.useForm();

    const [formRequest, formLoading] = useFormRequest(form);

    const submitForm = useCallback(
        (values) => {
            const url = route("page.commerce-payment.create-transaction", {
                payment: payment.id,
                email: customer.email
            });

            formRequest
                .post(url, values)
                .then((data) => setTransaction(data))
                .catch(errorHandler());
        },
        [formRequest, payment, customer, setTransaction]
    );

    return (
        <Box ref={ref}>
            <Form form={form} onFinish={submitForm}>
                <Stack sx={{mb: 3}}>
                    <Typography variant="h4">
                        <FormattedMessage defaultMessage="Select Cryptocurrency" />
                    </Typography>

                    <Typography variant="caption" color="text.secondary">
                        <FormattedMessage defaultMessage="We will use the market price to determine the amount required for this transaction." />
                    </Typography>
                </Stack>

                <Stack spacing={3}>
                    <Form.Item
                        name="coin"
                        label={intl.formatMessage(messages.crypto)}
                        rules={[{required: true}]}>
                        <ToggleButtonGroup
                            exclusive
                            orientation="vertical"
                            fullWidth>
                            {payment.wallets.map((record) => {
                                const wallet = Wallet.use(record);

                                return (
                                    <ToggleButton
                                        key={wallet.id}
                                        value={wallet.coin.identifier}>
                                        <CoinWrapper>
                                            <IconBuilder
                                                icon={wallet.coin.svgIcon()}
                                                sx={{fontSize: "25px"}}
                                            />

                                            <Box component="span" sx={{ml: 2}}>
                                                {wallet.coin.name}
                                            </Box>
                                        </CoinWrapper>
                                    </ToggleButton>
                                );
                            })}
                        </ToggleButtonGroup>
                    </Form.Item>

                    <LoadingButton
                        size="large"
                        variant="contained"
                        loading={formLoading}
                        type="submit"
                        fullWidth>
                        <FormattedMessage defaultMessage="Pay Now" />
                    </LoadingButton>
                </Stack>
            </Form>
        </Box>
    );
});

const CoinWrapper = styled("span")({
    display: "flex",
    alignItems: "center",
    flexGrow: 1
});

export default TransactionCreate;
