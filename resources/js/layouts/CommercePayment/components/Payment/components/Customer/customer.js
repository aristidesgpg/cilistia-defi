import React, {useCallback, useState} from "react";
import {defineMessages, FormattedMessage, useIntl} from "react-intl";
import {Box, Collapse, Stack, Typography} from "@mui/material";
import Form, {TextField} from "components/Form";
import {LoadingButton} from "@mui/lab";
import {errorHandler, route, useFormRequest, useRequest} from "services/Http";
import useCommercePayment from "hooks/useCommercePayment";

const messages = defineMessages({
    email: {defaultMessage: "Email"},
    firstName: {defaultMessage: "First Name"},
    lastName: {defaultMessage: "Last Name"}
});

const Customer = ({setCustomer}) => {
    const intl = useIntl();
    const {payment} = useCommercePayment();
    const [register, setRegister] = useState(false);
    const [form] = Form.useForm();

    const [request, loading] = useRequest();
    const [formRequest, formLoading] = useFormRequest(form);

    const fetchCustomer = useCallback(
        (values) => {
            const url = route("page.commerce-payment.get-customer", {
                payment: payment.id,
                email: values.email
            });

            request
                .get(url)
                .then((data) => setCustomer(data))
                .catch(({canceled, response}) => {
                    if (!canceled && response?.status === 404) {
                        return setRegister(true);
                    }
                });
        },
        [request, payment, setCustomer]
    );

    const createCustomer = useCallback(
        (values) => {
            const url = route("page.commerce-payment.create-customer", {
                payment: payment.id
            });

            formRequest
                .post(url, values)
                .then((data) => setCustomer(data))
                .catch(errorHandler());
        },
        [formRequest, payment, setCustomer]
    );

    const submitForm = useCallback(
        (values) => (register ? createCustomer(values) : fetchCustomer(values)),
        [register, createCustomer, fetchCustomer]
    );

    const handleValuesChange = useCallback(
        (values) => {
            if (values.email && register) {
                setRegister(false);
            }
        },
        [register]
    );

    return (
        <Box
            sx={{
                margin: "0 auto",
                maxWidth: {xs: "100%", md: 450},
                textAlign: "left"
            }}>
            <Form
                form={form}
                onValuesChange={handleValuesChange}
                onFinish={submitForm}>
                <Stack sx={{mb: 3}}>
                    <Typography variant="h4">
                        <FormattedMessage defaultMessage="Customer Information" />
                    </Typography>

                    <Typography variant="caption" color="text.secondary">
                        <FormattedMessage defaultMessage="This will be used by merchant, for this and subsequent payment reference." />
                    </Typography>
                </Stack>

                <Stack spacing={3}>
                    <Form.Item
                        name="email"
                        label={intl.formatMessage(messages.email)}
                        rules={[{required: true, type: "email"}]}>
                        <TextField fullWidth />
                    </Form.Item>

                    <Collapse in={register} unmountOnExit>
                        <Stack
                            spacing={{xs: 3, sm: 3}}
                            direction={{xs: "column", sm: "row"}}>
                            <Form.Item
                                name="first_name"
                                label={intl.formatMessage(messages.firstName)}
                                rules={[{required: true}]}>
                                <TextField fullWidth />
                            </Form.Item>

                            <Form.Item
                                name="last_name"
                                label={intl.formatMessage(messages.lastName)}
                                rules={[{required: true}]}>
                                <TextField fullWidth />
                            </Form.Item>
                        </Stack>
                    </Collapse>

                    <LoadingButton
                        size="large"
                        variant="contained"
                        type="submit"
                        loading={formLoading}
                        disabled={loading}
                        fullWidth>
                        <FormattedMessage defaultMessage="Proceed" />
                    </LoadingButton>
                </Stack>
            </Form>
        </Box>
    );
};

export default Customer;
