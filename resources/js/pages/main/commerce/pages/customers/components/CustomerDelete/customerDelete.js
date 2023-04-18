import React, {useCallback, useContext} from "react";
import PopConfirm from "components/PopConfirm";
import {IconButton} from "@mui/material";
import LoadingIcon from "components/LoadingIcon";
import DeleteIcon from "@mui/icons-material/Delete";
import {defineMessages, useIntl} from "react-intl";
import TableContext from "contexts/TableContext";
import {errorHandler, route, useRequest} from "services/Http";
import {notify} from "utils/index";

const messages = defineMessages({
    success: {defaultMessage: "Customer was removed."}
});

const CustomerDelete = ({customer}) => {
    const intl = useIntl();
    const {reload: reloadTable} = useContext(TableContext);
    const [request, loading] = useRequest();

    const deleteCustomer = useCallback(() => {
        const url = route("commerce-customer.delete", {
            id: customer.id
        });

        request
            .delete(url)
            .then(() => {
                notify.success(intl.formatMessage(messages.success));
                reloadTable();
            })
            .catch(errorHandler());
    }, [request, customer, reloadTable, intl]);

    if (!customer.deletable) {
        return null;
    }

    return (
        <PopConfirm component={IconButton} onClick={deleteCustomer}>
            <LoadingIcon
                color="error"
                component={DeleteIcon}
                loading={loading}
            />
        </PopConfirm>
    );
};

export default CustomerDelete;
