import React, {Fragment, useCallback, } from "react";
import {useModal} from "utils/modal";
import WalletButtons from "./WalletButtons"
 import {
    Button,
} from "@mui/material";

const WalletConnectButton = () => {
    const [modal, modalElements] = useModal();

    const showModal = useCallback(() => {
        modal.confirm({
            title: 'Wallet connect',
            content: <WalletButtons />
        });
    }, [modal]);

    return (
        <Fragment>
            <Button variant="contained" onClick={showModal} sx={{borderRadius: '5px'}}>
                Connect
            </Button>

            {modalElements}
        </Fragment>
    );
};

export default WalletConnectButton;
