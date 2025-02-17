import React, { createContext, useCallback, useContext, useRef, useState } from 'react';
import { Box, Divider, MenuItem, Typography } from '@mui/material';
import { Link as RouterLink } from 'react-router-dom';
import MenuPopover from 'components/MenuPopover';
import { useAuth } from 'models/Auth';
import UserAvatar from 'components/UserAvatar';
import { defineMessages, FormattedMessage, useIntl } from 'react-intl';
import { notify } from 'utils/index';
import { errorHandler, useRequest } from 'services/Http';
import { LoadingButton } from '@mui/lab';
import ActionButton from 'components/ActionButton';
import router from 'router';
import ProfileLink from 'components/ProfileLink';
import Iconify from 'components/Iconify';
import { useAccount, useDisconnect } from 'wagmi';

const messages = defineMessages({
  logoutSuccess: { defaultMessage: 'Logout was successful.' },
  home: { defaultMessage: 'Home' },
  settings: { defaultMessage: 'Settings' },
  profile: { defaultMessage: 'Profile' }
});

const ToggleContext = createContext();

const Account = () => {
  const { address } = useAccount();
  const { disconnect } = useDisconnect();
  const anchorRef = useRef(null);
  const [request, loading] = useRequest();
  const [open, setOpen] = useState(false);
  const auth = useAuth();
  const intl = useIntl();

  const handleLogout = useCallback(() => {
    auth
      .logout(request)
      .then(() => {
        disconnect();
        notify.success(intl.formatMessage(messages.logoutSuccess));
        window.location.reload();
      })
      .catch(errorHandler());
  }, [auth, request, disconnect, intl]);

  return (
    <ToggleContext.Provider value={{ open, setOpen }}>
      <ActionButton ref={anchorRef} onClick={() => setOpen(true)} sx={{ width: 40, height: 40 }} active={open}>
        <UserAvatar sx={{ width: '1.5em', height: '1.5em' }} user={auth.user} />
      </ActionButton>

      <MenuPopover open={open} anchorEl={anchorRef.current} onClose={() => setOpen(false)} sx={{ width: 220 }}>
        <Box sx={{ my: 1.5, px: 2.5 }}>
          <Typography user={auth.user} component={ProfileLink} variant="subtitle1" noWrap>
            {auth.user.name}
          </Typography>

          <Typography variant="body2" sx={{ color: 'text.secondary' }} noWrap>
            {address}
          </Typography>
        </Box>

        <Divider sx={{ my: 1 }} />

        <LinkItem name="main.home" />
        <LinkItem name="main.user.purchases" />
        <LinkItem name="main.profile" params={{ name: auth.user.name }} />
        <LinkItem name="main.user.account" />

        <Box sx={{ p: 2, pt: 1.5 }}>
          <LoadingButton fullWidth loading={loading} variant="outlined" onClick={handleLogout} color="inherit">
            <FormattedMessage defaultMessage="Logout" />
          </LoadingButton>
        </Box>
      </MenuPopover>
    </ToggleContext.Provider>
  );
};

const LinkItem = ({ name, params, ...otherProps }) => {
  const { setOpen } = useContext(ToggleContext);

  return (
    <MenuItem
      component={RouterLink}
      sx={{ typography: 'body2', py: 1, px: 2.5 }}
      to={router.generatePath(name, params)}
      onClick={() => setOpen(false)}
      {...otherProps}>
      <Iconify sx={{ mr: 2, width: 24, height: 24 }} icon={router.getIcon(name)} />

      {router.getName(name)}
    </MenuItem>
  );
};

export default Account;
