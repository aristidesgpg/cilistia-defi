import React, {useState} from "react";
import ContentCopyIcon from "@mui/icons-material/ContentCopy";
import {Button} from "@mui/material";
import {CopyToClipboard} from "react-copy-to-clipboard";
import CheckIcon from "@mui/icons-material/Check";
import useMountHandler from "hooks/useMountHandler";

const CopyableButton = ({
    onCopy,
    text,
    startIcon = <ContentCopyIcon />,
    ...otherProps
}) => {
    const handler = useMountHandler();
    const [icon, setIcon] = useState(startIcon);

    const handleCopy = (...args) => {
        setIcon(<CheckIcon />);
        setTimeout(() => handler.execute(() => setIcon(startIcon)), 5000);
        return onCopy?.(...args);
    };

    return (
        <CopyToClipboard text={text} onCopy={handleCopy}>
            <Button startIcon={icon} {...otherProps} sx={{borderRadius: '5px'}} />
        </CopyToClipboard>
    );
};

export default CopyableButton;
