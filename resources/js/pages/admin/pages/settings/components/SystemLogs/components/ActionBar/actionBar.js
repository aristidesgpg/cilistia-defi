import React, {useCallback, useContext} from "react";
import {defineMessages, FormattedMessage, useIntl} from "react-intl";
import {LoadingButton} from "@mui/lab";
import {errorHandler, route, useRequest} from "services/Http";
import BeenHereIcon from "@mui/icons-material/Beenhere";
import {notify} from "utils/index";
import SearchTable from "components/SearchTable";
import TableContext from "contexts/TableContext";
import ActionToolbar from "components/ActionToolbar";
import {Typography} from "@mui/material";

const messages = defineMessages({
    success: {defaultMessage: "Operation was successful."},
    search: {defaultMessage: "Search message..."}
});

const ActionBar = ({fetchData}) => {
    const intl = useIntl();
    const {reload: reloadTable} = useContext(TableContext);
    const [request, loading] = useRequest();

    const markAllAsSeen = useCallback(() => {
        request
            .post(route("admin.system-logs.mark-all-as-seen"))
            .then(() => {
                notify.success(intl.formatMessage(messages.success));
                fetchData();
                reloadTable();
            })
            .catch(errorHandler());
    }, [intl, request, reloadTable, fetchData]);

    return (
        <ActionToolbar>
            <SearchTable
                placeholder={intl.formatMessage(messages.search)}
                field="message"
            />

            <LoadingButton
                variant="contained"
                startIcon={<BeenHereIcon />}
                onClick={markAllAsSeen}
                loading={loading}>
                <Typography variant="inherit" noWrap>
                    <FormattedMessage defaultMessage="Mark As Seen" />
                </Typography>
            </LoadingButton>
        </ActionToolbar>
    );
};

export default ActionBar;
