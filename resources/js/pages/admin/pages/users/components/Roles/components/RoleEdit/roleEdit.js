import React, {
    Fragment,
    useCallback,
    useContext,
    useEffect,
    useState
} from "react";
import {
    Alert,
    Box,
    Chip,
    Grid,
    IconButton,
    Stack,
    Typography
} from "@mui/material";
import {useModal} from "utils/modal";
import EditIcon from "@mui/icons-material/Edit";
import Form, {Checkbox, TextField} from "components/Form";
import {defineMessages, FormattedMessage, useIntl} from "react-intl";
import {errorHandler, route, useFormRequest, useRequest} from "services/Http";
import {notify} from "utils/index";
import {lowerCase} from "lodash";
import {LoadingButton} from "@mui/lab";
import TableContext from "contexts/TableContext";
import ModalActions from "components/ModalActions";
import ModalContent from "components/ModalContent";
import LoadingFallback from "components/LoadingFallback";
import Scrollbar from "components/Scrollbar";

const messages = defineMessages({
    name: {defaultMessage: "Name"},
    success: {defaultMessage: "Role was updated."},
    editRole: {defaultMessage: "Edit Role"},
    rank: {defaultMessage: "Rank"}
});

const RoleEdit = ({role}) => {
    const intl = useIntl();
    const [modal, modalElements] = useModal();

    const editRole = useCallback(() => {
        modal.confirm({
            title: intl.formatMessage(messages.editRole),
            content: <EditForm role={role} />,
            dialog: {fullWidth: true}
        });
    }, [modal, intl, role]);

    return (
        <Fragment>
            <IconButton onClick={editRole}>
                <EditIcon />
            </IconButton>

            {modalElements}
        </Fragment>
    );
};

const EditForm = ({closeModal, role}) => {
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
                .put(route("admin.role.update", {role: role.id}), values)
                .then(() => {
                    notify.success(intl.formatMessage(messages.success));
                    closeModal();
                    reloadTable();
                })
                .catch(errorHandler());
        },
        [closeModal, formRequest, intl, role, reloadTable]
    );

    const selected = useCallback(
        (name) => role.permissions?.findIndex((p) => p.name === name) >= 0,
        [role]
    );

    return (
        <Form form={form} onFinish={submitForm}>
            <ModalContent spacing={2}>
                <Box component="div">
                    <Grid container spacing={1}>
                        <Grid item xs={8}>
                            <Form.Item
                                name="name"
                                rules={[{required: true}]}
                                label={intl.formatMessage(messages.name)}
                                initialValue={role.name}>
                                <TextField
                                    disabled={role.protected}
                                    fullWidth
                                />
                            </Form.Item>
                        </Grid>

                        <Grid item xs={4}>
                            <Form.Item
                                name="rank"
                                label={intl.formatMessage(messages.rank)}
                                rules={[{required: true, type: "number"}]}
                                normalize={(v) => v && parseInt(v)}
                                initialValue={role.rank}>
                                <TextField
                                    disabled={role.protected}
                                    fullWidth
                                />
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
                                        selected={selected}
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

const PermissionItem = ({permission, selected}) => {
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
                    initialValue={selected(permission.name)}
                    name={["permissions", permission.name]}>
                    <Checkbox />
                </Form.Item>
            </Grid>
        </Grid>
    );
};

export default RoleEdit;
