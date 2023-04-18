import React, {useCallback, useContext, useEffect, useState} from "react";
import {Alert, Box, Button, Chip, Grid, Stack, Typography} from "@mui/material";
import {defineMessages, FormattedMessage, useIntl} from "react-intl";
import {lowerCase} from "lodash";
import AddIcon from "@mui/icons-material/Add";
import {useModal} from "utils/modal";
import Form, {Checkbox, TextField} from "components/Form";
import {errorHandler, route, useFormRequest, useRequest} from "services/Http";
import {notify} from "utils/index";
import {LoadingButton} from "@mui/lab";
import SearchTable from "components/SearchTable";
import TableContext from "contexts/TableContext";
import ModalActions from "components/ModalActions";
import ModalContent from "components/ModalContent";
import LoadingFallback from "components/LoadingFallback";
import Scrollbar from "components/Scrollbar";
import ActionToolbar from "components/ActionToolbar";

const messages = defineMessages({
    name: {defaultMessage: "Name"},
    rank: {defaultMessage: "Rank"},
    success: {defaultMessage: "Role was created"},
    search: {defaultMessage: "Search name..."},
    createRole: {defaultMessage: "Create Role"}
});

const ActionBar = () => {
    const intl = useIntl();
    const [modal, modalElements] = useModal();

    const createRole = useCallback(() => {
        modal.confirm({
            title: intl.formatMessage(messages.createRole),
            content: <CreateForm />,
            dialog: {fullWidth: true}
        });
    }, [modal, intl]);

    return (
        <ActionToolbar>
            <SearchTable
                placeholder={intl.formatMessage(messages.search)}
                field="name"
            />

            {modalElements}

            <Button variant="contained" onClick={createRole} sx={{borderRadius: '5px'}}>
                <AddIcon />
            </Button>
        </ActionToolbar>
    );
};

const CreateForm = ({closeModal}) => {
    const [form] = Form.useForm();
    const intl = useIntl();
    const [permissions, setPermissions] = useState([]);
    const [formRequest, formLoading] = useFormRequest(form);
    const {reload: reloadTable} = useContext(TableContext);
    const [request, loading] = useRequest();

    const fetchPermissions = useCallback(() => {
        request
            .get(route("admin.role.get-permissions"))
            .then((data) => setPermissions(data))
            .catch(errorHandler());
    }, [request]);

    useEffect(() => {
        fetchPermissions();
    }, [fetchPermissions]);

    const submitForm = useCallback(
        (values) => {
            formRequest
                .post(route("admin.role.create"), values)
                .then(() => {
                    notify.success(intl.formatMessage(messages.success));
                    closeModal();
                    reloadTable();
                })
                .catch(errorHandler());
        },
        [closeModal, formRequest, intl, reloadTable]
    );

    return (
        <Form form={form} onFinish={submitForm}>
            <ModalContent spacing={2}>
                <Box component="div">
                    <Grid container spacing={1}>
                        <Grid item xs={8}>
                            <Form.Item
                                name="name"
                                label={intl.formatMessage(messages.name)}
                                rules={[{required: true}]}>
                                <TextField fullWidth />
                            </Form.Item>
                        </Grid>

                        <Grid item xs={4}>
                            <Form.Item
                                name="rank"
                                initialValue={10}
                                label={intl.formatMessage(messages.rank)}
                                rules={[{required: true, type: "number"}]}
                                normalize={(v) => v && parseInt(v)}>
                                <TextField fullWidth />
                            </Form.Item>
                        </Grid>
                    </Grid>
                </Box>

                <Alert severity="info">
                    <FormattedMessage defaultMessage="Set priority of roles with Rank, lesser value having greater priority." />
                </Alert>

                <LoadingFallback
                    content={permissions}
                    fallbackIconSize={130}
                    loading={loading}>
                    {(permissions) => (
                        <Stack spacing={1}>
                            <Typography
                                sx={{color: "text.secondary"}}
                                variant="overline">
                                <FormattedMessage defaultMessage="Permissions" />
                            </Typography>

                            <Scrollbar sx={{maxHeight: 360}}>
                                {permissions.map((permission) => (
                                    <PermissionItem
                                        key={permission.id}
                                        permission={permission}
                                    />
                                ))}
                            </Scrollbar>
                        </Stack>
                    )}
                </LoadingFallback>
            </ModalContent>

            <ModalActions>
                <LoadingButton
                    variant="contained"
                    disabled={loading}
                    type="submit"
                    loading={formLoading}>
                    <FormattedMessage defaultMessage="Submit" />
                </LoadingButton>
            </ModalActions>
        </Form>
    );
};

const PermissionItem = ({permission}) => {
    return (
        <Grid container spacing={1}>
            <Grid item xs={10}>
                <Chip
                    label={lowerCase(permission.name)}
                    size="small"
                    variant="outlined"
                />
            </Grid>

            <Grid item xs={2}>
                <Form.Item
                    valuePropName="checked"
                    name={["permissions", permission.name]}>
                    <Checkbox />
                </Form.Item>
            </Grid>
        </Grid>
    );
};

export default ActionBar;
