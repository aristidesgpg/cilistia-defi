import React, {useCallback, useMemo} from "react";
import Form, {TextField} from "components/Form";
import {
    Card,
    CardActions,
    CardContent,
    CardHeader,
    MenuItem
} from "@mui/material";
import {useDispatch} from "react-redux";
import {defaultTo} from "lodash";
import {useAuth} from "models/Auth";
import {defineMessages, FormattedMessage, useIntl} from "react-intl";
import {errorHandler, route, useFormRequest} from "services/Http";
import {notify} from "utils/index";
import {fetchUser} from "redux/slices/auth";
import {LoadingButton} from "@mui/lab";
import Alert from "components/Alert";
import {useSupportedCurrencies} from "hooks/global";

const messages = defineMessages({
    currency: {defaultMessage: "Currency"},
    profileUpdated: {defaultMessage: "Your profile was updated."}
});

const PaymentForm = () => {
    const dispatch = useDispatch();
    const auth = useAuth();
    const [form] = Form.useForm();
    const [request, loading] = useFormRequest(form);
    const intl = useIntl();

    const submitForm = useCallback(
        (values) => {
            request
                .patch(route("user.update"), values)
                .then(() => {
                    notify.success(intl.formatMessage(messages.profileUpdated));
                    dispatch(fetchUser());
                })
                .catch(errorHandler());
        },
        [intl, request, dispatch]
    );

    const {currencies} = useSupportedCurrencies();

    const initialValues = useMemo(() => {
        return {currency: defaultTo(auth.user.currency, "")};
    }, [auth]);

    return (
        <Form form={form} initialValues={initialValues} onFinish={submitForm}>
            <Card>
                <CardHeader
                    title={<FormattedMessage defaultMessage="Payments" />}
                />
                <CardContent>
                    <Form.Item
                        name="currency"
                        rules={[{required: true}]}
                        label={intl.formatMessage(messages.currency)}>
                        <TextField fullWidth select>
                            {currencies.map((currency) => (
                                <MenuItem
                                    value={currency.code}
                                    key={currency.code}>
                                    {`${currency.name} (${currency.code})`}
                                </MenuItem>
                            ))}
                        </TextField>
                    </Form.Item>

                    {auth.user.currency && (
                        <Alert severity="warning" sx={{mt: 1}}>
                            <FormattedMessage
                                defaultMessage="You will no longer have access to your {currency} payment account once changed."
                                values={{
                                    currency: <b>{auth.user.currency}</b>
                                }}
                            />
                        </Alert>
                    )}
                </CardContent>

                <CardActions sx={{justifyContent: "flex-end"}}>
                    <LoadingButton
                        type="submit"
                        variant="contained"
                        loading={loading}>
                        <FormattedMessage defaultMessage="Save Changes" />
                    </LoadingButton>
                </CardActions>
            </Card>
        </Form>
    );
};

export default PaymentForm;
