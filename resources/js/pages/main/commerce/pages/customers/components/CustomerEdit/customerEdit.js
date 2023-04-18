import React, {Fragment, useCallback, useContext} from "react";
import {defineMessages, useIntl} from "react-intl";
import {useModal} from "utils/modal";
import {IconButton} from "@mui/material";
import EditIcon from "@mui/icons-material/Edit";
import Form from "components/Form";
import TableContext from "contexts/TableContext";
import {errorHandler, route, useFormRequest} from "services/Http";
import {notify} from "utils/index";
import CustomerForm from "../CustomerForm";

const messages = defineMessages({
    title: {defaultMessage: "Edit Customer"},
    success: {defaultMessage: "Customer was updated."}
});

const CustomerEdit = ({customer}) => {
    const intl = useIntl();
    const [modal, modalElements] = useModal();

    const editCustomer = useCallback(() => {
        modal.confirm({
            title: intl.formatMessage(messages.title),
            content: <EditForm customer={customer} />,
            dialog: {fullWidth: true}
        });
    }, [modal, intl, customer]);

    return (
        <Fragment>
            <IconButton onClick={editCustomer}>
                <EditIcon />
            </IconButton>

            {modalElements}
        </Fragment>
    );
};

const EditForm = ({closeModal, customer}) => {
    const [form] = Form.useForm();
    const intl = useIntl();
    const {reload: reloadTable} = useContext(TableContext);
    const [formRequest, formLoading] = useFormRequest(form);

    const submitForm = useCallback(
        (values) => {
            const url = route("commerce-customer.update", {
                id: customer.id
            });

            formRequest
                .patch(url, values)
                .then(() => {
                    notify.success(intl.formatMessage(messages.success));
                    closeModal();
                    reloadTable();
                })
                .catch(errorHandler());
        },
        [closeModal, formRequest, reloadTable, customer, intl]
    );

    return (
        <CustomerForm
            form={form}
            initialValues={customer}
            formLoading={formLoading}
            submitForm={submitForm}
        />
    );
};

export default CustomerEdit;
