import React, {useCallback, useContext} from "react";
import SearchTable from "components/SearchTable";
import {defineMessages, useIntl} from "react-intl";
import ActionToolbar from "components/ActionToolbar";
import {useModal} from "utils/modal";
import {Button} from "@mui/material";
import AddIcon from "@mui/icons-material/Add";
import Form from "components/Form";
import {errorHandler, route, useFormRequest} from "services/Http";
import TableContext from "contexts/TableContext";
import PaymentForm from "../PaymentForm";
import {notify} from "utils/index";

const messages = defineMessages({
    search: {defaultMessage: "Search payments..."},
    createPayment: {defaultMessage: "Create Payment"},
    success: {defaultMessage: "Payment was created."}
});

const ActionBar = () => {
    const intl = useIntl();
    const [modal, modalElements] = useModal();

    const createPayment = useCallback(() => {
        modal.confirm({
            title: intl.formatMessage(messages.createPayment),
            content: <CreateForm />,
            dialog: {fullWidth: true}
        });
    }, [intl, modal]);

    return (
        <ActionToolbar>
            <SearchTable
                placeholder={intl.formatMessage(messages.search)}
                field="title"
            />

            {modalElements}

            <Button variant="contained" onClick={createPayment} sx={{borderRadius: '5px'}}>
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
                .post(route("commerce-payment.create"), values)
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
        <PaymentForm
            form={form}
            formLoading={formLoading}
            submitForm={submitForm}
        />
    );
};

export default ActionBar;
