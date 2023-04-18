import React, {useCallback} from "react";
import PropTypes from "prop-types";
import {
    Button,
    Card,
    CardActions,
    CardContent,
    CardHeader,
    Stack
} from "@mui/material";
import {FormattedMessage} from "react-intl";
import Form from "components/Form";
import CountryField from "./components/CountryField";
import AmountField from "./components/AmountField";
import CurrencyField from "./components/CurrencyField";
import WalletField from "./components/WalletField";
import PaymentSelect from "./components/PaymentSelect";
import PaymentField from "./components/PaymentField";
import {tap} from "lodash";

const Filter = ({apply}) => {
    const [form] = Form.useForm();

    const applyFilters = useCallback(
        (values) => {
            normalize(values);
            return apply(values);
        },
        [apply]
    );

    const reset = () => {
        form.resetFields();
        form.submit();
    };

    return (
        <Form form={form} onFinish={applyFilters}>
            <Card>
                <CardHeader
                    title={<FormattedMessage defaultMessage="Filter Offers" />}
                />

                <CardContent>
                    <Stack spacing={3}>
                        <WalletField />
                        <PaymentSelect />
                        <PaymentField />
                        <CurrencyField />
                        <AmountField />
                        <CountryField />
                    </Stack>
                </CardContent>

                <Form.Item shouldUpdate>
                    {(form) => {
                        const touched = form.isFieldsTouched();

                        return (
                            <CardActions>
                                <Button
                                sx={{borderRadius: '5px'}}
                                    color="inherit"
                                    variant="outlined"
                                    size="large"
                                    onClick={reset}
                                    disabled={!touched}
                                    fullWidth>
                                    <FormattedMessage defaultMessage="Clear" />
                                </Button>

                                <Button
                                sx={{borderRadius: '5px'}}
                                    color="primary"
                                    variant="contained"
                                    size="large"
                                    type="submit"
                                    disabled={!touched}
                                    fullWidth>
                                    <FormattedMessage defaultMessage="Apply" />
                                </Button>
                            </CardActions>
                        );
                    }}
                </Form.Item>
            </Card>
        </Form>
    );
};

const normalize = (values) => {
    return tap(values, (values) => {
        values.payment_method = values.payment_method?.id;
    });
};

Filter.propTypes = {
    apply: PropTypes.func.isRequired
};

export default Filter;
