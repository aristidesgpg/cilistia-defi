import React, {forwardRef, useContext, useEffect} from "react";
import {Box, Link, Paper, Stack, Typography, Zoom} from "@mui/material";
import {FormattedMessage} from "react-intl";
import useCommercePayment, {
    useCommerceSettings
} from "hooks/useCommercePayment";
import CircleCountdown from "components/CircleCountdown";
import Completed from "assets/illustrations/completed";
import Canceled from "assets/illustrations/canceled";
import CommerceTransactionContext from "contexts/CommerceTransactionContext";

const StatusFeedback = () => {
    const onExit = (node) => (node.style.position = "absolute");
    const {transaction} = useContext(CommerceTransactionContext);

    return (
        <Stack alignItems="center" sx={{position: "relative"}}>
            <Zoom
                onExit={onExit}
                in={transaction.status === "pending"}
                unmountOnExit>
                <PendingStatus />
            </Zoom>

            <Zoom
                onExit={onExit}
                in={transaction.status === "completed"}
                unmountOnExit>
                <CompletedStatus />
            </Zoom>

            <Zoom
                onExit={onExit}
                in={transaction.status === "canceled"}
                unmountOnExit>
                <CanceledStatus />
            </Zoom>
        </Stack>
    );
};

const PendingStatus = forwardRef((props, ref) => {
    const settings = useCommerceSettings();
    const {transaction} = useContext(CommerceTransactionContext);
    const duration = settings.transaction_interval * 60;

    return (
        <Box ref={ref}>
            <CircleCountdown
                date={transaction.expires_at}
                duration={duration}
                renderer={({minutes}) => (
                    <Stack alignItems="center">
                        <Typography variant="h3" sx={{lineHeight: 1}}>
                            {minutes}
                        </Typography>

                        <Typography variant="caption" noWrap>
                            <FormattedMessage defaultMessage="minutes" />
                        </Typography>
                    </Stack>
                )}
            />
        </Box>
    );
});

const CompletedStatus = forwardRef((props, ref) => {
    const {payment} = useCommercePayment();

    return (
        <Box ref={ref}>
            <Stack alignItems="center" sx={{mb: 5}}>
                <Completed sx={{width: 100, height: 100}} />
            </Stack>

            <Stack alignItems="center" spacing={1}>
                <Paper variant="outlined">
                    <Typography variant="body2" sx={{p: 1.5}}>
                        {!payment.message ? (
                            <FormattedMessage
                                defaultMessage="Your payment was received successfully. If you have any complaint or enquiry about this transaction, feel free to contact the merchant via email {email}"
                                values={{email: <ContactEmail />}}
                            />
                        ) : (
                            <FormattedMessage
                                defaultMessage="Message from merchant: {message}"
                                values={{message: payment.message}}
                            />
                        )}
                    </Typography>
                </Paper>

                <RedirectAction redirect={payment.redirect} />
            </Stack>
        </Box>
    );
});

const CanceledStatus = forwardRef((props, ref) => {
    const {payment} = useCommercePayment();

    return (
        <Box ref={ref}>
            <Stack alignItems="center" sx={{mb: 5}}>
                <Canceled sx={{width: 100, height: 100}} />
            </Stack>

            <Stack alignItems="center" spacing={1}>
                <Paper variant="outlined">
                    <Typography variant="body2" sx={{p: 1.5}}>
                        <FormattedMessage
                            defaultMessage="This transaction has exceeded its timeout and has been automatically canceled. If you have any complaint or enquiry about this transaction, feel free to contact the merchant via email {email}"
                            values={{email: <ContactEmail />}}
                        />
                    </Typography>
                </Paper>

                <RedirectAction redirect={payment.redirect} />
            </Stack>
        </Box>
    );
});

const RedirectAction = ({redirect, timeout = 60000}) => {
    useEffect(() => {
        if (!redirect) return;

        setTimeout(() => {
            window.location.replace(redirect);
        }, timeout);
    }, [redirect, timeout]);

    if (!redirect) return null;

    return (
        <Typography variant="body2" sx={{p: 2}}>
            <FormattedMessage defaultMessage="You will be redirected back to the merchant shortly." />
        </Typography>
    );
};

const ContactEmail = () => {
    const {payment} = useCommercePayment();

    return (
        <Link
            target="_blank"
            href={`mailto:${payment.account.email}`}
            underline="none"
            rel="noopener">
            {payment.account.email}
        </Link>
    );
};

export default StatusFeedback;
