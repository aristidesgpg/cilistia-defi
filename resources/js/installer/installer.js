import React from "react";
import {useInstaller} from "hooks/settings";
import License from "./License";
import Register from "./Register";

const Installer = () => {
    const {license} = useInstaller();
    return license ? <Register /> : <License />;
};

export default Installer;
