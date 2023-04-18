import React, {forwardRef, useCallback, useContext} from "react";
import {Box, Button, Grid, Paper, Stack, Typography} from "@mui/material";
import Divider from "@mui/material/Divider";
import {defineMessages, FormattedMessage, useIntl} from "react-intl";
import CommerceTransactionContext from "contexts/CommerceTransactionContext";
import Progress from "components/RoundedLinearProgress";
import {useModal} from "utils/modal";
import Form, {TextField} from "components/Form";
import ModalActions from "components/ModalActions";
import {LoadingButton} from "@mui/lab";
import {errorHandler, route, useFormRequest} from "services/Http";
import {notify} from "utils/index";
import useCommercePayment from "hooks/useCommercePayment";
import ModalContent from "components/ModalContent";

const messages = defineMessages({
    hash: {defaultMessage: "Transfer Hash"},
    relayTransfer: {defaultMessage: "Report Transfer"},
    success: {defaultMessage: "Report was submitted."}
});

const PaymentDetails = forwardRef((props, ref) => {
    const {transaction} = useContext(CommerceTransactionContext);
    const symbol = transaction.wallet.coin.symbol;

    return (
        <Box sx={{width: 1}} ref={ref}>
            <Paper elevation={1}>
                <Stack divider={<Divider sx={{borderStyle: "dashed"}} />}>
                    <ItemDetail
                        title={<FormattedMessage defaultMessage="Received" />}
                        content={`${transaction.received} ${symbol}`}
                    />

                    <ItemDetail
                        title={<FormattedMessage defaultMessage="Amount" />}
                        content={`${transaction.value} ${symbol}`}
                    />

                    <ItemDetail
                        title={<FormattedMessage defaultMessage="Pending" />}
                        content={`${transaction.unconfirmed_received} ${symbol}`}
                    />

                    <ItemDetail
                        title={<FormattedMessage defaultMessage="Progress" />}
                        content={<Progress value={transaction.progress} />}
                    />
                </Stack>
            </Paper>

            <RelayButton />
        </Box>
    );
});

const ItemDetail = ({title, content}) => {
    return (
        <Box sx={{minHeight: 50, p: 2}}>
            <Grid container alignItems="center" spacing={2}>
                <Grid item xs={4}>
                    <Typography
                        variant="subtitle2"
                        color="text.secondary"
                        noWrap>
                        {title}
                    </Typography>
                </Grid>

                <Grid item xs={8}>
                    <Typography variant="subtitle2" noWrap>
                        {content}
                    </Typography>
                </Grid>
            </Grid>
        </Box>
    );
};

const RelayButton = () => {
    const intl = useIntl();
    const [modal, modalElements] = useModal();

    const showAction = useCallback(() => {
        modal.confirm({
            title: intl.formatMessage(messages.relayTransfer),
            content: <RelayTransfer />,
            dialog: {fullWidth: true}
        });
    }, [modal, intl]);

    return (
        <Stack alignItems="center" sx={{mt: 1}}>
            <Button variant="text" color="inherit" onClick={showAction} sx={{borderRadius: '5px'}}>
                <FormattedMessage defaultMessage="Undetected transfer?" />
            </Button>

            {modalElements}
        </Stack>
    );
};

const RelayTransfer = ({closeModal}) => {
    const [form] = Form.useForm();
    const intl = useIntl();
    const [formRequest, formLoading] = useFormRequest(form);
    const {transaction} = useContext(CommerceTransactionContext);
    const {payment} = useCommercePayment();

    const submitForm = useCallback(
        (values) => {
            const url = route("page.commerce-payment.update-transaction", {
                payment: payment.id,
                id: transaction.id
            });

            formRequest
                .patch(url, values)
                .then(() => {
                    notify.success(intl.formatMessage(messages.success));
                    closeModal();
                })
                .catch(errorHandler());
        },
        [closeModal, formRequest, intl, payment, transaction]
    );

    return (
        <Form form={form} onFinish={submitForm}>
            <ModalContent spacing={2}>
                <Form.Item
                    name="hash"
                    label={intl.formatMessage(messages.hash)}
                    rules={[{required: true, max: 100}]}>
                    <TextField fullWidth />
                </Form.Item>
            </ModalContent>

            <ModalActions>
                <LoadingButton
                    variant="contained"
                    loading={formLoading}
                    type="submit">
                    <FormattedMessage defaultMessage="Report" />
                </LoadingButton>
            </ModalActions>
        </Form>
    );
};

export default PaymentDetails;
