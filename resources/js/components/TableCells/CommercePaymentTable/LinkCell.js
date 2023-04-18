import React, {Fragment, useCallback, useRef} from "react";
import {Box, Button, Stack, Typography} from "@mui/material";
import QrCodeScannerIcon from "@mui/icons-material/QrCodeScanner";
import {defineMessages, FormattedMessage, useIntl} from "react-intl";
import {useModal} from "utils/modal";
import ModalContent from "components/ModalContent";
import ModalActions from "components/ModalActions";
import PrintIcon from "@mui/icons-material/Print";
import {truncate} from "lodash";
import {useReactToPrint} from "react-to-print";
import {experimentalStyled as styled} from "@mui/material/styles";
import CopyableButton from "components/CopyableButton";
import QRCode from "components/QRCode";
import defaultLogo from "static/default-logo.png";
import {useBrand} from "hooks/settings";

const messages = defineMessages({
    title: {defaultMessage: "Payment Link"}
});

const LinkCell = ({payment}) => {
    const intl = useIntl();
    const [modal, modalElements] = useModal();

    const displayLink = useCallback(() => {
        modal.confirm({
            title: intl.formatMessage(messages.title),
            content: <LinkCard payment={payment} />,
            dialog: {fullWidth: true, maxWidth: "xs"}
        });
    }, [modal, intl, payment]);

    return (
        <Fragment>
            <Button
            sx={{borderRadius: '5px'}}
                onClick={displayLink}
                startIcon={<QrCodeScannerIcon />}
                size="small">
                <FormattedMessage defaultMessage="View Link" />
            </Button>

            {modalElements}
        </Fragment>
    );
};

const LinkCard = ({payment}) => {
    const contentRef = useRef();
    const brand = useBrand();

    const print = useReactToPrint({
        suppressErrors: true,
        content: () => contentRef.current,
        bodyClass: "print-container"
    });

    return (
        <Stack spacing={3}>
            <ModalContent
                ref={contentRef}
                sx={{textAlign: "center"}}
                spacing={2}>
                <Typography variant="h5" noWrap>
                    {payment.title}
                </Typography>

                <Typography variant="caption">
                    {truncate(payment.description, {length: 200})}
                </Typography>

                <CodeBox
                    component={QRCode}
                    imageSettings={{
                        excavate: true,
                        src: brand.logo_url ?? defaultLogo,
                        width: 20,
                        height: 20
                    }}
                    value={payment.link}
                    renderAs="svg"
                />
            </ModalContent>

            <ModalActions justifyContent="center" spacing={2}>
                <Button
                sx={{borderRadius: '5px'}}
                    variant="contained"
                    startIcon={<PrintIcon />}
                    onClick={print}>
                    <FormattedMessage defaultMessage="Print" />
                </Button>

                <CopyableButton variant="contained" text={payment.link}>
                    <FormattedMessage defaultMessage="Copy" />
                </CopyableButton>
            </ModalActions>
        </Stack>
    );
};

const CodeBox = styled(Box)(({theme}) => ({
    maxWidth: 256,
    padding: theme.spacing(1),
    border: `1px dashed ${theme.palette.grey[500_32]}`,
    borderRadius: "5px",
    alignSelf: "center",
    width: "80%",
    height: "auto"
}));

export default LinkCell;
