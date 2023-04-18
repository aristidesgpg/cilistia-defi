import React, {Fragment, useCallback} from "react";
import {useModal} from "utils/modal";
import {toUpper} from "lodash";
import {experimentalStyled as styled} from "@mui/material/styles";
import {Avatar, Chip, Link, Stack, Typography} from "@mui/material";
import phoneFill from "@iconify/icons-eva/phone-fill";
import emailFill from "@iconify/icons-eva/email-fill";
import Iconify from "components/Iconify";
import Divider from "@mui/material/Divider";
import RenderHtml from "components/RenderHtml";

const BusinessAccount = ({account}) => {
    const [modal, modalElements] = useModal();

    const showAccount = useCallback(() => {
        modal.confirm({
            content: <AccountCard account={account} />,
            dialog: {fullWidth: true}
        });
    }, [modal, account]);

    return (
        <Fragment>
            <ClickableAvatar onClick={showAccount}>
                {toUpper(account.name.charAt(0))}
            </ClickableAvatar>

            {modalElements}
        </Fragment>
    );
};

const AccountCard = ({account}) => {
    return (
        <Stack
            spacing={3}
            divider={<Divider sx={{borderStyle: "dashed"}} />}
            sx={{py: 2, mx: -3}}>
            <Stack direction="row" sx={{px: 2}} spacing={2}>
                <Stack spacing={0.5} sx={{flexGrow: 1, minWidth: 0}}>
                    <Typography variant="subtitle1" noWrap>
                        {account.name}
                    </Typography>

                    <Stack
                        alignItems="flex-start"
                        direction={{xs: "column", sm: "row"}}
                        spacing={{xs: 0.5, sm: 2}}>
                        <ContactItem icon={emailFill} contact={account.email} />
                        <ContactItem icon={phoneFill} contact={account.phone} />
                    </Stack>
                </Stack>

                <Link
                    target="_blank"
                    href={account.website}
                    sx={{flexShrink: 0}}
                    underline="none"
                    rel="noopener">
                    <Avatar variant="rounded">
                        {toUpper(account.name.charAt(0))}
                    </Avatar>
                </Link>
            </Stack>

            <Typography component="div" sx={{px: 2}} variant="body2">
                <RenderHtml html={account.about} />
            </Typography>
        </Stack>
    );
};

const ContactItem = ({icon, contact}) => {
    if (!contact) {
        return null;
    }

    return (
        <Chip
            size="small"
            avatar={<ContactIcon icon={icon} />}
            label={
                <Typography variant="inherit" noWrap>
                    {contact}
                </Typography>
            }
        />
    );
};

const ContactIcon = styled(Iconify)({
    height: 20,
    width: 20,
    flexShrink: 0
});

const ClickableAvatar = styled(Avatar)(({theme}) => ({
    cursor: "pointer",
    boxShadow: theme.customShadows.z8,
    transition: "box-shadow 0.3s ease-in-out",
    "&:hover": {boxShadow: "none"}
}));

export default BusinessAccount;
