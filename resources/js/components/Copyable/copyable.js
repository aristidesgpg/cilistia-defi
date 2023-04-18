import React, {useState} from "react";
import {CopyToClipboard} from "react-copy-to-clipboard";
import {defaultTo} from "lodash";
import {IconButton, Stack, Typography} from "@mui/material";
import PropTypes from "prop-types";
import ContentCopyIcon from "@mui/icons-material/ContentCopy";
import CheckIcon from "@mui/icons-material/Check";
import useMountHandler from "hooks/useMountHandler";

const Copyable = ({
    onCopy,
    ellipsis,
    children,
    text,
    buttonProps,
    containerProps,
    buttonIcon = <ContentCopyIcon fontSize="inherit" />,
    ...otherProps
}) => {
    const handler = useMountHandler();
    const [icon, setIcon] = useState(buttonIcon);

    const handleCopy = (...args) => {
        setIcon(<CheckIcon fontSize="inherit" />);
        setTimeout(() => handler.execute(() => setIcon(buttonIcon)), 5000);
        return onCopy?.(...args);
    };

    return (
        <Stack
            direction="row"
            sx={{maxWidth: "100%"}}
            alignItems="center"
            spacing={1}
            {...containerProps}>
            <Typography noWrap={ellipsis} {...otherProps}>
                {children}
            </Typography>

            <CopyToClipboard
                text={defaultTo(text, children)}
                onCopy={handleCopy}>
                <IconButton size="small" {...buttonProps}>
                    {icon}
                </IconButton>
            </CopyToClipboard>
        </Stack>
    );
};

Copyable.propTypes = {text: PropTypes.string};

export default Copyable;
