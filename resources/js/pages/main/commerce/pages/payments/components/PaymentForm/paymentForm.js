import React from "react";
import {defineMessages, FormattedMessage, useIntl} from "react-intl";
import {isEmpty} from "lodash";
import Form, {
    DateTimePicker,
    RadioGroup,
    SelectAdornment,
    TextField
} from "components/Form";
import {LoadingButton} from "@mui/lab";
import ModalActions from "components/ModalActions";
import ModalContent from "components/ModalContent";
import WalletSelect from "components/WalletSelect";
import {InputAdornment, MenuItem, Stack, Typography} from "@mui/material";
import {useSupportedCurrencies} from "hooks/global";
import {useAuth} from "models/Auth";
import PublicIcon from "@mui/icons-material/Public";

const messages = defineMessages({
    title: {defaultMessage: "Title"},
    description: {defaultMessage: "Description"},
    coins: {defaultMessage: "Accepted Coins"},
    type: {defaultMessage: "Type"},
    expires_at_help: {
        defaultMessage: "Leave empty if the payment link should not expire."
    },
    message: {defaultMessage: "Message"},
    expires_at: {defaultMessage: "Expires At"},
    redirect: {defaultMessage: "Redirect"},
    amount: {defaultMessage: "Amount"}
});

const PaymentForm = ({
    initialValues,
    form,
    formLoading,
    onValuesChange,
    submitForm
}) => {
    const intl = useIntl();
    const auth = useAuth();
    const editMode = !isEmpty(initialValues);

    initialValues = {
        currency: auth.user.currency,
        ...initialValues
    };

    return (
        <Form
            form={form}
            initialValues={initialValues}
            onValuesChange={onValuesChange}
            onFinish={submitForm}>
            <ModalContent spacing={4}>
                <Stack spacing={2}>
                    <Form.Item
                        name="title"
                        label={intl.formatMessage(messages.title)}
                        rules={[{required: true}]}>
                        <TextField fullWidth />
                    </Form.Item>

                    <Form.Item
                        name="description"
                        label={intl.formatMessage(messages.description)}
                        rules={[{required: true}]}>
                        <TextField fullWidth multiline rows={3} />
                    </Form.Item>
                </Stack>

                {!editMode && (
                    <Stack spacing={2}>
                        <Typography variant="overline" color="text.secondary">
                            <FormattedMessage defaultMessage="Amount & Currency" />
                        </Typography>

                        <Form.Item
                            name="type"
                            label={intl.formatMessage(messages.type)}
                            rules={[{required: true}]}>
                            <RadioGroup options={typeOptions} />
                        </Form.Item>

                        <Form.Item
                            name="amount"
                            label={intl.formatMessage(messages.amount)}
                            rules={[{required: true}]}>
                            <TextField
                                type="number"
                                fullWidth
                                InputProps={{
                                    endAdornment: (
                                        <InputAdornment position="end">
                                            <CurrencySelect />
                                        </InputAdornment>
                                    )
                                }}
                            />
                        </Form.Item>

                        <Form.Item
                            name="coins"
                            label={intl.formatMessage(messages.coins)}
                            rules={[{required: true}]}>
                            <WalletSelect
                                getValue={(o) => o.coin.identifier}
                                multiple
                            />
                        </Form.Item>
                    </Stack>
                )}

                <Stack spacing={2}>
                    <Typography variant="overline" color="text.secondary">
                        <FormattedMessage defaultMessage="Confirmation" />
                    </Typography>

                    <Form.Item
                        name="redirect"
                        label={intl.formatMessage(messages.redirect)}
                        rules={[{type: "url"}]}>
                        <TextField
                            fullWidth
                            InputLabelProps={{shrink: true}}
                            InputProps={{
                                startAdornment: (
                                    <InputAdornment position="start">
                                        <PublicIcon />
                                    </InputAdornment>
                                )
                            }}
                        />
                    </Form.Item>

                    <Form.Item
                        name="message"
                        label={intl.formatMessage(messages.message)}>
                        <TextField fullWidth multiline rows={3} />
                    </Form.Item>
                </Stack>

                <Stack spacing={2}>
                    <Typography variant="overline" color="text.secondary">
                        <FormattedMessage defaultMessage="Duration" />
                    </Typography>

                    <Form.Item
                        name="expires_at"
                        label={intl.formatMessage(messages.expires_at)}>
                        <DateTimePicker
                            fullWidth
                            helperText={intl.formatMessage(
                                messages.expires_at_help
                            )}
                        />
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

const CurrencySelect = () => {
    const {currencies} = useSupportedCurrencies();

    return (
        <Form.Item name="currency" rules={[{required: true}]}>
            <SelectAdornment>
                {currencies.map((currency) => (
                    <MenuItem value={currency.code} key={currency.code}>
                        {currency.code}
                    </MenuItem>
                ))}
            </SelectAdornment>
        </Form.Item>
    );
};

const typeOptions = [
    {
        value: "single",
        label: <FormattedMessage defaultMessage="Single" />
    },
    {
        value: "multiple",
        label: <FormattedMessage defaultMessage="Multiple" />
    }
];

export default PaymentForm;
