import React, {useCallback, useContext} from "react";
import {defineMessages, useIntl} from "react-intl";
import {useModal} from "utils/modal";
import SearchTable from "components/SearchTable";
import {Button} from "@mui/material";
import AddIcon from "@mui/icons-material/Add";
import ActionToolbar from "components/ActionToolbar";
import Form from "components/Form";
import {errorHandler, route, useFormRequest} from "services/Http";
import TableContext from "contexts/TableContext";
import CustomerForm from "../CustomerForm";
import {notify} from "utils/index";

const messages = defineMessages({
    search: {defaultMessage: "Search customers..."},
    createCustomer: {defaultMessage: "Create Customer"},
    success: {defaultMessage: "Customer was created."}
});

const ActionBar = () => {
    const intl = useIntl();
    const [modal, modalElements] = useModal();

    const createCustomer = useCallback(() => {
        modal.confirm({
            title: intl.formatMessage(messages.createCustomer),
            content: <CreateForm />,
            dialog: {fullWidth: true}
        });
    }, [intl, modal]);

    return (
        <ActionToolbar>
            <SearchTable
                placeholder={intl.formatMessage(messages.search)}
                field="first_name"
            />

            {modalElements}

            <Button variant="contained" onClick={createCustomer} sx={{borderRadius: '5px'}}>
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
                .post(route("commerce-customer.create"), values)
                .then(() => {
                    notify.success(intl.formatMessage(messages.success));
                    closeModal();
                    reloadTable();
                })
                .catch(errorHandler());
        },
        [formRequest, intl, reloadTable, closeModal]
    );

    return (
        <CustomerForm
            form={form}
            formLoading={formLoading}
            submitForm={submitForm}
        />
    );
};

export default ActionBar;
