import React, {useCallback, useContext} from "react";
import {defineMessages, useIntl} from "react-intl";
import TableContext from "contexts/TableContext";
import {errorHandler, route, useRequest} from "services/Http";
import PopConfirm from "components/PopConfirm";
import {IconButton} from "@mui/material";
import LoadingIcon from "components/LoadingIcon";
import DeleteIcon from "@mui/icons-material/Delete";
import {notify} from "utils/index";

const messages = defineMessages({
    success: {defaultMessage: "Payment was removed."}
});

const PaymentDelete = ({payment}) => {
    const intl = useIntl();
    const {reload: reloadTable} = useContext(TableContext);
    const [request, loading] = useRequest();

    const deletePayment = useCallback(() => {
        const url = route("commerce-payment.delete", {
            id: payment.id
        });

        request
            .delete(url)
            .then(() => {
                notify.success(intl.formatMessage(messages.success));
                reloadTable();
            })
            .catch(errorHandler());
    }, [request, payment, reloadTable, intl]);

    if (!payment.deletable) {
        return null;
    }

    return (
        <PopConfirm component={IconButton} onClick={deletePayment}>
            <LoadingIcon
                color="error"
                component={DeleteIcon}
                loading={loading}
            />
        </PopConfirm>
    );
};

export default PaymentDelete;
