import React, {useContext} from "react";
import {Box, Button, Stack, TextField, Typography} from "@mui/material";
import {FormattedMessage} from "react-intl";
import Popup from "components/Popup";
import DateRangeIcon from "@mui/icons-material/DateRange";
import PeriodContext, {PeriodProvider} from "contexts/PeriodContext";
import {DateTimePicker} from "@mui/x-date-pickers";

const PeriodSelector = ({header, children}) => {
    return (
        <PeriodProvider>
            <Stack direction="row" alignItems="center" mb={3} spacing={2}>
                <Box sx={{flexGrow: 1, minWidth: 100}}>
                    {header ?? (
                        <Typography variant="h4" noWrap>
                            <FormattedMessage defaultMessage="Statistics" />
                        </Typography>
                    )}
                </Box>

                <Popup
                    disableRipple
                    component={Button}
                    variant="outlined"
                    color="inherit"
                    startIcon={<DateRangeIcon />}
                    content={<DateForm />}>
                    <FormattedMessage defaultMessage="Select Period" />
                </Popup>
            </Stack>

            {children}
        </PeriodProvider>
    );
};

const DateForm = () => {
    const {from, setFrom, to, setTo} = useContext(PeriodContext);

    return (
        <Stack sx={{m: -0.5, maxWidth: 220}} alignItems="center" spacing={3}>
            <DateTimePicker
                disableFuture
                onChange={setFrom}
                value={from}
                renderInput={(props) => <TextField {...props} size="small" />}
                label={<FormattedMessage defaultMessage="From" />}
            />

            <DateTimePicker
                disableFuture
                onChange={setTo}
                value={to}
                renderInput={(props) => <TextField {...props} size="small" />}
                label={<FormattedMessage defaultMessage="To" />}
                minDateTime={from}
            />
        </Stack>
    );
};

export default PeriodSelector;
