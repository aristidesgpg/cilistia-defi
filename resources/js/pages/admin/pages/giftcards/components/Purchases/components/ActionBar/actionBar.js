import React from "react";
import {defineMessages, useIntl} from "react-intl";
import SearchTable from "components/SearchTable";
import ActionToolbar from "components/ActionToolbar";

const messages = defineMessages({
    search: {defaultMessage: "Search buyer..."}
});

const ActionBar = () => {
    const intl = useIntl();

    return (
        <ActionToolbar>
            <SearchTable
                field="searchUser"
                placeholder={intl.formatMessage(messages.search)}
                withParams={true}
            />
        </ActionToolbar>
    );
};

export default ActionBar;
