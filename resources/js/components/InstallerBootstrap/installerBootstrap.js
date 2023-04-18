import React from "react";
import {useInstaller} from "hooks/settings";
import {lazy} from "utils/index";

const Installer = lazy(() =>
    import(/* webpackChunkName: 'installer' */ "installer")
);

const InstallerBootstrap = ({children}) => {
    const {display} = useInstaller();
    return display ? <Installer /> : children;
};

export default InstallerBootstrap;
