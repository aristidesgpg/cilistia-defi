import React, {Fragment, useCallback, useContext} from "react";
import {defineMessages, useIntl} from "react-intl";
import {useModal} from "utils/modal";
import {IconButton} from "@mui/material";
import EditIcon from "@mui/icons-material/Edit";
import PaymentForm from "../PaymentForm";
import Form from "components/Form";
import TableContext from "contexts/TableContext";
import {errorHandler, route, useFormRequest} from "services/Http";
import {notify} from "utils/index";

const messages = defineMessages({
    title: {defaultMessage: "Edit Payment"},
    success: {defaultMessage: "Payment was updated."}
});

const PaymentEdit = ({payment}) => {
    const intl = useIntl();
    const [modal, modalElements] = useModal();

    const editPayment = useCallback(() => {
        modal.confirm({
            title: intl.formatMessage(messages.title),
            content: <EditForm payment={payment} />,
            dialog: {fullWidth: true}
        });
    }, [modal, intl, payment]);

    return (
        <Fragment>
            <IconButton onClick={editPayment}>
                <EditIcon />
            </IconButton>

            {modalElements}
        </Fragment>
    );
};

const EditForm = ({closeModal, payment}) => {
    const [form] = Form.useForm();
    const intl = useIntl();
    const {reload: reloadTable} = useContext(TableContext);
    const [formRequest, formLoading] = useFormRequest(form);

    const submitForm = useCallback(
        (values) => {
            const url = route("commerce-payment.update", {
                id: payment.id
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
        [closeModal, formRequest, reloadTable, payment, intl]
    );

    return (
        <PaymentForm
            form={form}
            initialValues={payment}
            formLoading={formLoading}
            submitForm={submitForm}
        />
    );
};

export default PaymentEdit;
