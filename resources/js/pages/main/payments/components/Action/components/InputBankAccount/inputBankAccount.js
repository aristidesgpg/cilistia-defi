import React, {
    useCallback,
    useContext,
    useEffect,
    useMemo,
    useRef,
    useState
} from "react";
import {
    Alert,
    Box,
    FormHelperText,
    IconButton,
    Paper,
    Stack,
    Typography
} from "@mui/material";
import Slider from "react-slick";
import {Icon} from "@iconify/react";
import arrowIosForwardFill from "@iconify/icons-eva/arrow-ios-forward-fill";
import arrowIosBackFill from "@iconify/icons-eva/arrow-ios-back-fill";
import {alpha, styled, useTheme} from "@mui/material/styles";
import {FormInputContext} from "components/Form/contexts";
import {FormattedMessage} from "react-intl";
import {isEmpty} from "lodash";
import {errorHandler, route, useRequest} from "services/Http";
import {useAuth} from "models/Auth";
import BankLogo from "components/BankLogo";
import LoadingFallback from "components/LoadingFallback";

const InputBankAccount = ({value, onChange, ...otherProps}) => {
    const sliderRef = useRef();
    const auth = useAuth();
    const [accounts, setAccounts] = useState([]);
    const {errors} = useContext(FormInputContext);
    const [request, loading] = useRequest();
    const theme = useTheme();

    const fetchAccounts = useCallback(() => {
        request
            .get(route("bank-account.all"))
            .then((data) => setAccounts(data))
            .catch(errorHandler());
    }, [request]);

    useEffect(() => {
        if (auth.countryOperation()) {
            fetchAccounts();
        }
    }, [fetchAccounts, auth]);

    const sliderProps = useMemo(
        () => ({
            swipeToSlide: true,
            dots: false,
            slidesToShow: 1,
            arrows: false,
            rtl: theme.direction === "rtl",
            focusOnSelect: true,
            infinite: false,
            variableWidth: true,
            centerMode: true,
            beforeChange: (oldIndex, newIndex) => {
                onChange?.(accounts[newIndex]?.id);
            }
        }),
        [theme, onChange, accounts]
    );

    const slideNext = useCallback(() => {
        sliderRef.current?.slickNext();
    }, []);

    const slidePrev = useCallback(() => {
        sliderRef.current?.slickPrev();
    }, []);

    return (
        <Box {...otherProps}>
            <LoadingFallback
                loading={loading}
                content={accounts}
                fallback={
                    <Alert severity="info">
                        <FormattedMessage defaultMessage="You have not added a bank account." />
                    </Alert>
                }>
                {(accounts) => (
                    <BaseStack
                        direction="row"
                        sx={{position: "relative"}}
                        alignItems="center">
                        <StyledIconButton
                            size="small"
                            onClick={slidePrev}
                            sx={{left: -16}}>
                            <Icon icon={arrowIosBackFill} />
                        </StyledIconButton>

                        <Box sx={{width: "100%"}}>
                            <Slider ref={sliderRef} {...sliderProps}>
                                {accounts.map((account) => (
                                    <AccountField
                                        key={account.id}
                                        account={account}
                                        value={value}
                                    />
                                ))}
                            </Slider>
                        </Box>

                        <StyledIconButton
                            size="small"
                            onClick={slideNext}
                            sx={{right: -16}}>
                            <Icon icon={arrowIosForwardFill} />
                        </StyledIconButton>
                    </BaseStack>
                )}
            </LoadingFallback>

            {!isEmpty(errors) && (
                <FormHelperText sx={{textAlign: "center"}} error>
                    {errors.join(" ")}
                </FormHelperText>
            )}
        </Box>
    );
};

const AccountField = ({value, account}) => {
    const theme = useTheme();
    const selected = value === account.id;

    return (
        <Box key={account.id} sx={{p: 2}}>
            <Paper
                elevation={selected ? 1 : 0}
                sx={{
                    transition: "all",
                    padding: theme.spacing(0.5, 1),
                    opacity: 0.3,
                    ...(selected && {
                        transform: "scale(1.2)",
                        opacity: 1
                    })
                }}>
                <Stack
                    direction="row"
                    justifyContent="center"
                    alignItems="center"
                    spacing={1.2}>
                    <BankLogo
                        src={account.bank_logo}
                        sx={{transform: "scale(0.9)"}}
                        disabled={!selected}
                    />

                    <Stack sx={{maxWidth: 120, my: 1}}>
                        <Typography
                            variant="body2"
                            sx={{fontWeight: "bold"}}
                            noWrap>
                            {account.bank_name}
                        </Typography>

                        <Typography
                            variant="caption"
                            sx={{color: "text.secondary"}}
                            noWrap>
                            {account.number}
                        </Typography>
                    </Stack>
                </Stack>
            </Paper>
        </Box>
    );
};

const StyledIconButton = styled(IconButton)(({theme}) => ({
    position: "absolute",
    zIndex: 9,
    color: theme.palette.grey[300],
    backgroundColor: alpha(theme.palette.grey[900], 0.48),
    "&:hover": {
        backgroundColor: theme.palette.grey[900]
    }
}));

const BaseStack = styled(Stack)(() => ({
    "& .slick-list": {
        // paddingTop: "24px !important"
    }
}));

export default InputBankAccount;
