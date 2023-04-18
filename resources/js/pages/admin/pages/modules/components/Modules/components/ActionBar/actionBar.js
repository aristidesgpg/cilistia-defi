import React from "react";
import SearchTable from "components/SearchTable";
import {defineMessages, useIntl} from "react-intl";
import ActionToolbar from "components/ActionToolbar";

const messages = defineMessages({
    search: {defaultMessage: "Search module..."}
});

const ActionBar = () => {
    const intl = useIntl();

    return (
        <ActionToolbar>
            <SearchTable
                placeholder={intl.formatMessage(messages.search)}
                field="title"
            />
        </ActionToolbar>
    );
};

export default ActionBar;
