import React, {useCallback, useEffect, useState} from "react";
import {
    Card,
    CardActions,
    CardContent,
    CardHeader,
    Stack,
    Switch,
    Typography
} from "@mui/material";
import {defineMessages, FormattedMessage, useIntl} from "react-intl";
import Form, {ControlLabel} from "components/Form";
import {isEmpty} from "lodash";
import {errorHandler, route, useFormRequest, useRequest} from "services/Http";
import Spin from "components/Spin";
import {notify} from "utils/index";
import {LoadingButton} from "@mui/lab";

const messages = defineMessages({
    title: {defaultMessage: "Update Settings"},
    verifiedPhone: {defaultMessage: "Verified phone"},
    verifiedEmail: {defaultMessage: "Verified email"},
    completeProfile: {defaultMessage: "Complete profile"},
    verifiedDocuments: {defaultMessage: "Verified documents"},
    verifiedAddress: {defaultMessage: "Verified address"},
    success: {defaultMessage: "Settings was updated."}
});

const UpdateSettings = () => {
    const [form] = Form.useForm();
    const intl = useIntl();
    const [request, loading] = useRequest();
    const [formRequest, formLoading] = useFormRequest(form);
    const [settings, setSettings] = useState({});

    const fetchSettings = useCallback(() => {
        request
            .get(route("admin.feature-limit.get-settings"))
            .then((data) => setSettings(data))
            .catch(errorHandler());
    }, [request]);

    useEffect(() => {
        fetchSettings();
    }, [fetchSettings]);

    const submitForm = useCallback(
        (values) => {
            formRequest
                .patch(route("admin.feature-limit.update-settings"), values)
                .then(() => {
                    notify.success(intl.formatMessage(messages.success));
                    fetchSettings();
                })
                .catch(errorHandler());
        },
        [formRequest, intl, fetchSettings]
    );

    useEffect(() => {
        if (!isEmpty(settings)) {
            form.resetFields();
        }
    }, [settings, form]);

    return (
        <Form form={form} onFinish={submitForm}>
            <Card>
                <CardHeader
                    title={<FormattedMessage defaultMessage="Settings" />}
                />

                <CardContent>
                    <Spin spinning={loading}>
                        <Stack spacing={3}>
                            <Basic settings={settings} />
                            <Advanced settings={settings} />
                        </Stack>
                    </Spin>
                </CardContent>

                <CardActions sx={{justifyContent: "flex-end"}}>
                    <LoadingButton
                        variant="contained"
                        type="submit"
                        loading={formLoading}>
                        <FormattedMessage defaultMessage="Save Changes" />
                    </LoadingButton>
                </CardActions>
            </Card>
        </Form>
    );
};

const Basic = ({settings}) => {
    const intl = useIntl();

    return (
        <Stack>
            <Typography variant="overline">
                <FormattedMessage defaultMessage="Basic" />
            </Typography>

            <Form.Item
                name="verified_phone"
                initialValue={settings.verified_phone}
                label={intl.formatMessage(messages.verifiedPhone)}
                valuePropName="checked">
                <ControlLabel>
                    <Switch />
                </ControlLabel>
            </Form.Item>

            <Form.Item
                name="verified_email"
                initialValue={settings.verified_email}
                label={intl.formatMessage(messages.verifiedEmail)}
                valuePropName="checked">
                <ControlLabel>
                    <Switch />
                </ControlLabel>
            </Form.Item>

            <Form.Item
                name="complete_profile"
                initialValue={settings.complete_profile}
                label={intl.formatMessage(messages.completeProfile)}
                valuePropName="checked">
                <ControlLabel>
                    <Switch />
                </ControlLabel>
            </Form.Item>
        </Stack>
    );
};

const Advanced = ({settings}) => {
    const intl = useIntl();

    return (
        <Stack>
            <Typography variant="overline">
                <FormattedMessage defaultMessage="Advanced" />
            </Typography>

            <Form.Item
                name="verified_documents"
                initialValue={settings.verified_documents}
                label={intl.formatMessage(messages.verifiedDocuments)}
                valuePropName="checked">
                <ControlLabel>
                    <Switch />
                </ControlLabel>
            </Form.Item>

            <Form.Item
                name="verified_address"
                initialValue={settings.verified_address}
                label={intl.formatMessage(messages.verifiedAddress)}
                valuePropName="checked">
                <ControlLabel>
                    <Switch />
                </ControlLabel>
            </Form.Item>
        </Stack>
    );
};

export default UpdateSettings;
