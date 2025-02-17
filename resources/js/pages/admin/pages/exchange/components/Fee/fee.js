import React, {useCallback, useEffect, useState} from "react";
import {defineMessages, FormattedMessage, useIntl} from "react-intl";
import Form, {TextField} from "components/Form";
import {errorHandler, route, useFormRequest, useRequest} from "services/Http";
import {notify} from "utils/index";
import {
    Alert,
    Card,
    CardActions,
    CardContent,
    CardHeader,
    InputAdornment,
    Stack,
    Typography
} from "@mui/material";
import {isEmpty} from "lodash";
import {LoadingButton} from "@mui/lab";
import LoadingFallback from "components/LoadingFallback";

const messages = defineMessages({
    updated: {defaultMessage: "Fee was updated."},
    buy: {defaultMessage: "Buy"},
    sell: {defaultMessage: "Sell"}
});

const Fee = () => {
    const [form] = Form.useForm();
    const intl = useIntl();
    const [data, setData] = useState([]);
    const [formRequest, formLoading] = useFormRequest(form);
    const [request, loading] = useRequest();

    const fetchData = useCallback(() => {
        request
            .get(route("admin.exchange-fee.all"))
            .then((data) => setData(data))
            .catch(errorHandler());
    }, [request]);

    useEffect(() => {
        fetchData();
    }, [fetchData]);

    const submitForm = useCallback(
        (values) => {
            formRequest
                .patch(route("admin.exchange-fee.update"), values)
                .then(() => {
                    notify.success(intl.formatMessage(messages.updated));
                    fetchData();
                })
                .catch(errorHandler());
        },
        [intl, formRequest, fetchData]
    );

    return (
        <Form form={form} onFinish={submitForm}>
            <Card>
                <CardHeader title={<FormattedMessage defaultMessage="Fee" />} />

                <CardContent>
                    <LoadingFallback content={data} loading={loading}>
                        {(data) => (
                            <Stack spacing={3}>
                                <Alert severity="info">
                                    <FormattedMessage
                                        defaultMessage="This is credited to the {operator}'s account, the operator can be set in modules' configuration."
                                        values={{operator: <b>Operator</b>}}
                                    />
                                </Alert>

                                {data.map((wallet) => (
                                    <WalletFields
                                        key={wallet.id}
                                        wallet={wallet}
                                    />
                                ))}
                            </Stack>
                        )}
                    </LoadingFallback>
                </CardContent>

                {!isEmpty(data) && (
                    <CardActions sx={{justifyContent: "flex-end"}}>
                        <LoadingButton
                            variant="contained"
                            loading={formLoading}
                            disabled={loading}
                            type="submit">
                            <FormattedMessage defaultMessage="Save Changes" />
                        </LoadingButton>
                    </CardActions>
                )}
            </Card>
        </Form>
    );
};

const WalletFields = ({wallet}) => {
    const id = wallet.coin.identifier;
    const intl = useIntl();

    const sellFee = wallet.exchange_fees.find((o) => {
        return o.category === "sell";
    });

    const buyFee = wallet.exchange_fees.find((o) => {
        return o.category === "buy";
    });

    return (
        <Stack spacing={2}>
            <Typography variant="overline">{wallet.coin.name}</Typography>

            <Stack spacing={2} direction="row">
                <Form.Item
                    name={["fees", id, "buy"]}
                    label={intl.formatMessage(messages.buy)}
                    initialValue={buyFee?.value || 0}
                    rules={[{required: true}]}>
                    <InputField />
                </Form.Item>

                <Form.Item
                    name={["fees", id, "sell"]}
                    label={intl.formatMessage(messages.sell)}
                    initialValue={sellFee?.value || 0}
                    rules={[{required: true}]}>
                    <InputField />
                </Form.Item>
            </Stack>
        </Stack>
    );
};

const InputField = (props) => {
    return (
        <TextField
            type="number"
            fullWidth
            InputProps={{
                endAdornment: (
                    <InputAdornment position="end">
                        <FormattedMessage defaultMessage="Percent" />
                    </InputAdornment>
                )
            }}
            {...props}
        />
    );
};

export default Fee;
