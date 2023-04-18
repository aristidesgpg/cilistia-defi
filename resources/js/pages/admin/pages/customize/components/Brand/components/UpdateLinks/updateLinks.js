import React, {useCallback} from "react";
import {
    Card,
    CardContent,
    CardHeader,
    InputAdornment,
    Stack
} from "@mui/material";
import {defineMessages, FormattedMessage, useIntl} from "react-intl";
import Form, {TextField} from "components/Form";
import {errorHandler, route, useFormRequest} from "services/Http";
import {notify} from "utils/index";
import {useBrand} from "hooks/settings";
import SupportIcon from "@mui/icons-material/Support";
import FactCheckIcon from "@mui/icons-material/FactCheck";
import RuleIcon from "@mui/icons-material/Rule";
import {LoadingButton} from "@mui/lab";
import {useDispatch} from "react-redux";
import {fetchBrand} from "redux/slices/settings";

const messages = defineMessages({
    success: {defaultMessage: "Your settings was saved"},
    sitePlace: {defaultMessage: "https://example.com"},
    supportUrl: {defaultMessage: "Support"},
    termsUrl: {defaultMessage: "Terms"},
    policyUrl: {defaultMessage: "Policy"}
});

const UpdateLinks = () => {
    const [form] = Form.useForm();
    const intl = useIntl();
    const [formRequest, formLoading] = useFormRequest(form);
    const dispatch = useDispatch();

    const submitForm = useCallback(
        (values) => {
            formRequest
                .patch(route("admin.brand.update-links"), values)
                .then(() => {
                    notify.success(intl.formatMessage(messages.success));
                    dispatch(fetchBrand());
                })
                .catch(errorHandler());
        },
        [formRequest, intl, dispatch]
    );

    const brand = useBrand();

    return (
        <Card>
            <CardHeader
                title={<FormattedMessage defaultMessage="Update Links" />}
            />

            <CardContent>
                <Form form={form} onFinish={submitForm}>
                    <Stack spacing={2}>
                        <Form.Item
                            name="support_url"
                            label={intl.formatMessage(messages.supportUrl)}
                            initialValue={brand.support_url}
                            rules={[{required: true}]}>
                            <TextField
                                fullWidth
                                placeholder={intl.formatMessage(
                                    messages.sitePlace
                                )}
                                InputProps={{
                                    startAdornment: (
                                        <InputAdornment position="start">
                                            <SupportIcon />
                                        </InputAdornment>
                                    )
                                }}
                            />
                        </Form.Item>

                        <Form.Item
                            name="terms_url"
                            label={intl.formatMessage(messages.termsUrl)}
                            initialValue={brand.terms_url}
                            rules={[{required: true}]}>
                            <TextField
                                fullWidth
                                placeholder={intl.formatMessage(
                                    messages.sitePlace
                                )}
                                InputProps={{
                                    startAdornment: (
                                        <InputAdornment position="start">
                                            <FactCheckIcon />
                                        </InputAdornment>
                                    )
                                }}
                            />
                        </Form.Item>

                        <Form.Item
                            name="policy_url"
                            label={intl.formatMessage(messages.policyUrl)}
                            initialValue={brand.policy_url}
                            rules={[{required: true}]}>
                            <TextField
                                fullWidth
                                placeholder={intl.formatMessage(
                                    messages.sitePlace
                                )}
                                InputProps={{
                                    startAdornment: (
                                        <InputAdornment position="start">
                                            <RuleIcon />
                                        </InputAdornment>
                                    )
                                }}
                            />
                        </Form.Item>
                    </Stack>

                    <Stack direction="row" justifyContent="flex-end" mt={3}>
                        <LoadingButton
                            variant="contained"
                            loading={formLoading}
                            type="submit">
                            <FormattedMessage defaultMessage="Save Changes" />
                        </LoadingButton>
                    </Stack>
                </Form>
            </CardContent>
        </Card>
    );
};

export default UpdateLinks;
