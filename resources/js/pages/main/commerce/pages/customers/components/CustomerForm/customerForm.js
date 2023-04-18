import React from "react";
import {defineMessages, FormattedMessage, useIntl} from "react-intl";
import {LoadingButton} from "@mui/lab";
import ModalActions from "components/ModalActions";
import ModalContent from "components/ModalContent";
import Form, {TextField} from "components/Form";
import {Stack, Typography} from "@mui/material";

const messages = defineMessages({
    lastName: {defaultMessage: "Last Name"},
    firstName: {defaultMessage: "First Name"},
    email: {defaultMessage: "Email"}
});

const CustomerForm = ({
    initialValues,
    form,
    formLoading,
    onValuesChange,
    submitForm
}) => {
    const intl = useIntl();

    return (
        <Form
            form={form}
            initialValues={initialValues}
            onValuesChange={onValuesChange}
            onFinish={submitForm}>
            <ModalContent spacing={4}>
                <Stack spacing={2}>
                    <Typography variant="overline" color="text.secondary">
                        <FormattedMessage defaultMessage="Personal" />
                    </Typography>

                    <Form.Item
                        name="last_name"
                        label={intl.formatMessage(messages.lastName)}
                        rules={[{required: true}]}>
                        <TextField fullWidth />
                    </Form.Item>

                    <Form.Item
                        name="first_name"
                        label={intl.formatMessage(messages.firstName)}
                        rules={[{required: true}]}>
                        <TextField fullWidth />
                    </Form.Item>
                </Stack>

                <Stack spacing={2}>
                    <Typography variant="overline" color="text.secondary">
                        <FormattedMessage defaultMessage="Contact" />
                    </Typography>

                    <Form.Item
                        name="email"
                        label={intl.formatMessage(messages.email)}
                        rules={[{required: true, type: "email"}]}>
                        <TextField fullWidth />
                    </Form.Item>
                </Stack>
            </ModalContent>

            <ModalActions>
                <LoadingButton
                    variant="contained"
                    loading={formLoading}
                    type="submit">
                    <FormattedMessage defaultMessage="Submit" />
                </LoadingButton>
            </ModalActions>
        </Form>
    );
};

export default CustomerForm;
