import React, {useCallback, useContext} from "react";
import {defineMessages, FormattedMessage, useIntl} from "react-intl";
import {useModal} from "utils/modal";
import {Button} from "@mui/material";
import AddIcon from "@mui/icons-material/Add";
import Form, {TextField} from "components/Form";
import {errorHandler, route, useFormRequest} from "services/Http";
import {notify} from "utils/index";
import {LoadingButton} from "@mui/lab";
import SearchTable from "components/SearchTable";
import TableContext from "contexts/TableContext";
import ModalContent from "components/ModalContent";
import ModalActions from "components/ModalActions";
import ActionToolbar from "components/ActionToolbar";

const messages = defineMessages({
    create: {defaultMessage: "Create Requirement"},
    success: {defaultMessage: "Requirement was created."},
    search: {defaultMessage: "Search name..."},
    name: {defaultMessage: "Name"},
    description: {defaultMessage: "Description"}
});

const ActionBar = () => {
    const intl = useIntl();
    const [modal, modalElements] = useModal();

    const create = useCallback(() => {
        modal.confirm({
            title: intl.formatMessage(messages.create),
            content: <CreateForm />,
            dialog: {fullWidth: true}
        });
    }, [intl, modal]);

    return (
        <ActionToolbar>
            <SearchTable
                placeholder={intl.formatMessage(messages.search)}
                field="name"
            />

            {modalElements}

            <Button variant="contained" onClick={create} sx={{borderRadius: '5px'}}>
                <AddIcon />
            </Button>
        </ActionToolbar>
    );
};

const CreateForm = ({closeModal}) => {
    const [form] = Form.useForm();
    const intl = useIntl();
    const [formRequest, formLoading] = useFormRequest(form);
    const {reload: reloadTable} = useContext(TableContext);

    const submitForm = useCallback(
        (values) => {
            formRequest
                .post(route("admin.required-document.create"), values)
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
                <Form.Item
                    name="name"
                    label={intl.formatMessage(messages.name)}
                    rules={[{required: true}]}>
                    <TextField fullWidth />
                </Form.Item>

                <Form.Item
                    name="description"
                    label={intl.formatMessage(messages.description)}
                    rules={[{required: true}]}>
                    <TextField fullWidth multiline rows={3} />
                </Form.Item>
            </ModalContent>

            <ModalActions>
                <LoadingButton
                    variant="contained"
                    type="submit"
                    loading={formLoading}>
                    <FormattedMessage defaultMessage="Submit" />
                </LoadingButton>
            </ModalActions>
        </Form>
    );
};

export default ActionBar;
