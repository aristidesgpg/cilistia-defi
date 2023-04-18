import React, {useCallback, useEffect, useState} from "react";
import Form, {TextField} from "components/Form";
import {errorHandler, route, useFormRequest, useRequest} from "services/Http";
import {defineMessages, FormattedMessage, useIntl} from "react-intl";
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
import LoadingFallback from "components/LoadingFallback";
import {isEmpty} from "lodash";
import {LoadingButton} from "@mui/lab";

const messages = defineMessages({
    updated: {defaultMessage: "Fee was updated."},
    fee: {defaultMessage: "Fee"}
});

const Fee = () => {
    const [form] = Form.useForm();
    const intl = useIntl();
    const [data, setData] = useState([]);
    const [formRequest, formLoading] = useFormRequest(form);
    const [request, loading] = useRequest();

    const fetchData = useCallback(() => {
        request
            .get(route("admin.commerce-fee.all"))
            .then((data) => setData(data))
            .catch(errorHandler());
    }, [request]);

    useEffect(() => {
        fetchData();
    }, [fetchData]);

    const submitForm = useCallback(
        (values) => {
            formRequest
                .patch(route("admin.commerce-fee.update"), values)
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
                <CardHeader
                    title={<FormattedMessage defaultMessage="Commerce Fees" />}
                />

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
    const intl = useIntl();

    const id = wallet.coin.identifier;
    const fee = wallet.commerce_fee;

    return (
        <Stack spacing={2}>
            <Typography variant="overline">{wallet.coin.name}</Typography>

            <Form.Item
                name={["fees", id]}
                label={intl.formatMessage(messages.fee)}
                initialValue={fee?.value || 0}
                rules={[{required: true}]}>
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
                />
            </Form.Item>
        </Stack>
    );
};

export default Fee;
