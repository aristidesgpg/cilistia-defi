import React, {useCallback, useEffect, useState} from "react";
import {
    Alert,
    Card,
    CardActions,
    CardContent,
    CardHeader,
    InputAdornment,
    MenuItem,
    Stack,
    Typography
} from "@mui/material";
import {defineMessages, FormattedMessage, useIntl} from "react-intl";
import Form, {SelectAdornment, TextField} from "components/Form";
import {errorHandler, route, useFormRequest, useRequest} from "services/Http";
import {isEmpty} from "lodash";
import {LoadingButton} from "@mui/lab";
import {notify} from "utils/index";
import LoadingFallback from "components/LoadingFallback";

const messages = defineMessages({
    updated: {defaultMessage: "Fee was updated."},
    withdrawal: {defaultMessage: "Withdrawal"}
});

const Fee = () => {
    const [form] = Form.useForm();
    const intl = useIntl();
    const [data, setData] = useState([]);
    const [formRequest, formLoading] = useFormRequest(form);
    const [request, loading] = useRequest();

    const fetchData = useCallback(() => {
        request
            .get(route("admin.withdrawal-fee.all"))
            .then((data) => setData(data))
            .catch(errorHandler());
    }, [request]);

    useEffect(() => {
        fetchData();
    }, [fetchData]);

    const submitForm = useCallback(
        (values) => {
            formRequest
                .patch(route("admin.withdrawal-fee.update"), values)
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
                    title={
                        <FormattedMessage defaultMessage="Withdrawal Fees" />
                    }
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
                            type="submit"
                            loading={formLoading}>
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
    const fee = wallet.withdrawal_fee;

    return (
        <Stack spacing={2}>
            <Typography variant="overline">{wallet.coin.name}</Typography>

            <Form.Item
                name={["fees", id, "value"]}
                label={intl.formatMessage(messages.withdrawal)}
                initialValue={fee?.value || 0}
                rules={[{required: true}]}>
                <TextField
                    type="number"
                    fullWidth
                    InputProps={{
                        endAdornment: (
                            <InputAdornment position="end">
                                <TypeSelect wallet={wallet} />
                            </InputAdornment>
                        )
                    }}
                />
            </Form.Item>
        </Stack>
    );
};

const TypeSelect = ({wallet}) => {
    const id = wallet.coin.identifier;
    const fee = wallet.withdrawal_fee;

    return (
        <Form.Item
            name={["fees", id, "type"]}
            initialValue={fee?.type || "percent"}>
            <SelectAdornment>
                <MenuItem value="fixed">
                    <FormattedMessage defaultMessage="Fixed" />
                </MenuItem>

                <MenuItem value="percent">
                    <FormattedMessage defaultMessage="Percent" />
                </MenuItem>
            </SelectAdornment>
        </Form.Item>
    );
};

export default Fee;
