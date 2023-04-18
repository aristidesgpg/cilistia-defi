import React, {useMemo} from "react";
import {defineMessages, useIntl} from "react-intl";
import stack from "@iconify-icons/ri/stack-fill.js";
import layoutGrid from "@iconify-icons/ri/layout-grid-fill.js";
import PageTabs from "components/PageTabs";
import Modules from "./components/Modules";
import Grid from "./components/Grid";

const messages = defineMessages({
    title: {defaultMessage: "Modules"},
    modules: {defaultMessage: "Modules"},
    grid: {defaultMessage: "Grid"}
});

const ModulesPage = () => {
    const intl = useIntl();

    const tabs = useMemo(
        () => [
            {
                value: "modules",
                label: intl.formatMessage(messages.modules),
                icon: stack,
                component: <Modules />
            },
            {
                value: "grid",
                label: intl.formatMessage(messages.grid),
                icon: layoutGrid,
                component: <Grid />
            }
        ],
        [intl]
    );

    return (
        <PageTabs
            initial="modules"
            title={intl.formatMessage(messages.title)}
            tabs={tabs}
        />
    );
};

export default ModulesPage;
