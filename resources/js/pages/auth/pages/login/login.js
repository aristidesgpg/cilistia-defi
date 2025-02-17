import React, { Fragment, useCallback, useEffect, useRef, useState } from 'react';
import { defineMessages, FormattedMessage, useIntl } from 'react-intl';
import { Link as RouterLink } from 'react-router-dom';
import { notify } from 'utils';
import router from 'router';
import { useAuth } from 'models/Auth';
import { errorHandler, route, useFormRequest } from 'services/Http';
import { Box, Checkbox, Container, InputAdornment, Link, Stack, Typography } from '@mui/material';
import { has, upperFirst } from 'lodash';
import Form, { ControlLabel, TextField } from 'components/Form';
import ReCaptcha, { recaptchaSubmit } from 'components/ReCaptcha';
import { ContentStyle, SectionCard, StyledPage } from 'layouts/Auth/auth.style';
import HintLayout from 'layouts/Auth/components/HintLayout';
import illustrationLogin from 'static/login-illustration.png';
import Divider from '@mui/material/Divider';
import { LoadingButton } from '@mui/lab';
import PersonIcon from '@mui/icons-material/Person';
import EmailIcon from '@mui/icons-material/Email';
import PasswordIcon from '@mui/icons-material/Password';
import LockIcon from '@mui/icons-material/Lock';
import Typewriter from 'typewriter-effect';
import { useRecaptcha } from 'hooks/settings';
import WalletButtons from 'layouts/components/Wallet/WalletButtons';
import { useAccount } from 'wagmi';
import LogoLayout from 'layouts/Auth/components/LogoLayout/logoLayout';

const messages = defineMessages({
  username: { defaultMessage: 'Username' },
  email: { defaultMessage: 'Email' },
  password: { defaultMessage: 'Password' },
  tokenTitle: { defaultMessage: 'Two Factor Verification' },
  token: { defaultMessage: 'Token' },
  success: { defaultMessage: 'Login was successful.' },
  rememberMe: { defaultMessage: 'Remember me' },
  lineOne: { defaultMessage: 'Start your Crypto Portfolio today!' },
  lineTwo: { defaultMessage: 'Buy & Sell Crypto with Credit Card' },
  lineThree: { defaultMessage: 'Get paid instantly via Bank Transfer' },
  lineFour: { defaultMessage: 'Buy Giftcards with Crypto' },
  lineFive: { defaultMessage: '... and many more to come!' },
  title: { defaultMessage: 'Login' }
});

const Login = () => {
  const { address, isConnected } = useAccount();

  const [form] = Form.useForm();
  const [withToken, setWithToken] = useState(false);
  const [request, loading] = useFormRequest(form);
  const auth = useAuth();
  const recaptchaRef = useRef();
  const intl = useIntl();

  const recaptcha = useRecaptcha();

  useEffect(() => {
    if (address) {
      form.setFieldsValue({ wallet: address });
    }
  }, [address, form]);

  const submitForm = useCallback(
    (values) => {
      request
        .post(route('auth.login'), values)
        .then((data) => {
          notify.success(intl.formatMessage(messages.success));

          if (data.intended) {
            window.location.replace(data.intended);
          } else {
            window.location.reload();
          }
        })
        .catch(
          errorHandler((e) => {
            if (has(e, 'response.data.errors.token')) {
              setWithToken(true);
            }
          })
        );
    },
    [request, intl]
  );

  const onSubmit = recaptchaSubmit(form, recaptchaRef);

  return (
    <StyledPage title={intl.formatMessage(messages.title)}>
      <HintLayout>
        <FormattedMessage defaultMessage="Don't have an account?" />

        <Link
          underline="none"
          component={RouterLink}
          variant="subtitle2"
          to={router.generatePath('auth.register')}
          sx={{ ml: 1 }}>
          <FormattedMessage defaultMessage="Get started" />
        </Link>
      </HintLayout>

      {/* <SectionCard sx={{ display: { xs: 'none', md: 'block' } }}>
        <Stack sx={{ p: 1, mb: 5 }}>
          <Typography variant="body2">
            <FormattedMessage defaultMessage="Hi, welcome back." />
          </Typography>

          <Typography sx={{ minHeight: 100, fontWeight: 600 }} variant="h3">
            <Typewriter
              options={{
                strings: [
                  intl.formatMessage(messages.lineOne),
                  intl.formatMessage(messages.lineTwo),
                  intl.formatMessage(messages.lineThree),
                  intl.formatMessage(messages.lineFour),
                  intl.formatMessage(messages.lineFive)
                ],
                autoStart: true,
                skipAddStyles: true,
                loop: true,
                pauseFor: 3000
              }}
            />
          </Typography>
        </Stack>

        <Stack justifyContent="center" sx={{ height: 440 }}>
          <img src={illustrationLogin} alt="login" />
        </Stack>
      </SectionCard> */}

      <Container>
        <ContentStyle>
          <Stack direction="row" alignItems="center" sx={{ mb: 5 }}>
            <Box
              sx={{
                flexGrow: 1,
                display: 'flex',
                alignItems: 'center',
                flexDirection: 'column'
              }}>
              <Typography variant="h5" gutterBottom>
                <FormattedMessage defaultMessage="Wallet Connect" />
              </Typography>
              <Typography sx={{ color: 'text.secondary' }}>
                <FormattedMessage defaultMessage="Connect Your Wallet To Access Cilistia" />
              </Typography>
            </Box>
          </Stack>

          {!address && <WalletButtons />}

          <Form form={form} onFinish={submitForm}>
            <Stack spacing={3}>
              <Stack sx={{ display: 'none' }}>
                <Form.Item
                  name="wallet"
                  rules={[{ required: true }]}
                  label={intl.formatMessage(messages.username)}
                  initialValue={address}>
                  <TextField
                    fullWidth
                    InputProps={{
                      startAdornment: (
                        <InputAdornment position="start">
                          <PersonIcon />
                        </InputAdornment>
                      )
                    }}
                  />
                </Form.Item>
              </Stack>

              <Form.Item name="name" rules={[{ required: true }]} label={intl.formatMessage(messages.username)}>
                <TextField
                  fullWidth
                  InputProps={{
                    startAdornment: (
                      <InputAdornment position="start">
                        <PersonIcon />
                      </InputAdornment>
                    )
                  }}
                />
              </Form.Item>

              <Form.Item name="password" rules={[{ required: true }]} label={intl.formatMessage(messages.password)}>
                <TextField
                  fullWidth
                  type="password"
                  InputProps={{
                    startAdornment: (
                      <InputAdornment position="start">
                        <PasswordIcon />
                      </InputAdornment>
                    )
                  }}
                />
              </Form.Item>

              {withToken && <TokenInput />}

              <Stack direction="row" justifyContent="space-between" alignItems="center" sx={{ my: 2 }}>
                <Form.Item
                  name="remember"
                  valuePropName="checked"
                  initialValue={true}
                  label={intl.formatMessage(messages.rememberMe)}>
                  <ControlLabel>
                    <Checkbox />
                  </ControlLabel>
                </Form.Item>

                <Link component={RouterLink} to={router.generatePath('auth.forgot-password')} variant="subtitle2">
                  <FormattedMessage defaultMessage="Forgot password?" />
                </Link>
              </Stack>

              {recaptcha.enable && (
                <Form.Item rules={[{ required: true }]} name="recaptcha">
                  <ReCaptcha ref={recaptchaRef} />
                </Form.Item>
              )}

              <LoadingButton
                fullWidth
                variant="contained"
                size="large"
                onClick={onSubmit}
                loading={loading}
                disabled={!isConnected}>
                <FormattedMessage defaultMessage="Login" />
              </LoadingButton>
            </Stack>
          </Form>
          <Typography variant="body2" sx={{ display: { xs: 'block', sm: 'none' }, mt: 3 }} align="center">
            <FormattedMessage defaultMessage="Don't have an account?" />

            <Link to={router.generatePath('auth.register')} sx={{ ml: 1 }} component={RouterLink}>
              <FormattedMessage defaultMessage="Get started" />
            </Link>
          </Typography>
        </ContentStyle>
      </Container>
    </StyledPage>
  );
};

const TokenInput = () => {
  const intl = useIntl();

  return (
    <Fragment>
      <Divider>
        <FormattedMessage defaultMessage="Two Factor Verification" />
      </Divider>

      <Form.Item name="token" rules={[{ required: true }]} label={intl.formatMessage(messages.token)}>
        <TextField
          fullWidth
          InputProps={{
            startAdornment: (
              <InputAdornment position="start">
                <LockIcon />
              </InputAdornment>
            )
          }}
        />
      </Form.Item>
    </Fragment>
  );
};

export default Login;
