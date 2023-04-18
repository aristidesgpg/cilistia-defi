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
import ModalActions from "components/ModalActions";
import ModalContent from "components/ModalContent";
import ActionToolbar from "components/ActionToolbar";

const messages = defineMessages({
    success: {defaultMessage: "Brand was created."},
    search: {defaultMessage: "Search brand..."},
    createBrand: {defaultMessage: "Create Brand"},
    name: {defaultMessage: "Name"},
    description: {defaultMessage: "Description"}
});

const ActionBar = () => {
    const intl = useIntl();
    const [modal, modalElements] = useModal();

    const createBrand = useCallback(() => {
        modal.confirm({
            title: intl.formatMessage(messages.createBrand),
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

            <Button variant="contained" onClick={createBrand} sx={{borderRadius: '5px'}}>
                <AddIcon />
            </Button>
        </ActionToolbar>
    );
};

const CreateForm = ({closeModal}) => {
    const [form] = Form.useForm();
    const intl = useIntl();
    const {reload: reloadTable} = useContext(TableContext);
    const [formRequest, formLoading] = useFormRequest(form);

    const submitForm = useCallback(
        (values) => {
            formRequest
                .post(route("admin.giftcard-brand.create"), values)
                .then(() => {
                    reloadTable();
                    notify.success(intl.formatMessage(messages.success));
                    closeModal();
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
