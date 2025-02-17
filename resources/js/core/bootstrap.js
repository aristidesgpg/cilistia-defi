import React, {useEffect} from "react";
import {HelmetProvider} from "react-helmet-async";
import {useIntl} from "react-intl";
import {getValidationMessages} from "utils/form";
import MuiBootstrap from "components/MuiBootstrap";
import context, {AppContext} from "core/context";
import Form from "components/Form";
import {notify} from "utils/index";

const Bootstrap = ({children}) => {
    const intl = useIntl();

    useEffect(() => {
        const data = context.notification;
        notify[data?.type]?.(data.message);
    }, []);

    return (
        <HelmetProvider>
            <MuiBootstrap>
                <Form.Provider validateMessages={getValidationMessages(intl)}>
                    <AppContext.Provider value={context}>
                        {children}
                    </AppContext.Provider>
                </Form.Provider>
            </MuiBootstrap>
        </HelmetProvider>
    );
};

export default Bootstrap;
