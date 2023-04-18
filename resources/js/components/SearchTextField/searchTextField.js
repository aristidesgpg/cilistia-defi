import React, {useCallback} from "react";
import {InputAdornment, OutlinedInput} from "@mui/material";
import SearchIcon from "@mui/icons-material/Search";
import {isNull, isUndefined} from "lodash";
import {styled} from "@mui/material/styles";

const SearchTextField = ({search, onSearchChange, ...otherProps}) => {
    const fixControlledValue = useCallback((value) => {
        return isUndefined(value) || isNull(value) ? "" : value;
    }, []);

    const updateSearch = useCallback(
        (e) => onSearchChange(e.target.value),
        [onSearchChange]
    );

    return (
        <StyledOutlinedInput
            {...otherProps}
            size="small"
            value={fixControlledValue(search)}
            onChange={updateSearch}
            startAdornment={
                <InputAdornment position="start">
                    <SearchIcon color="text.disabled" />
                </InputAdornment>
            }
        />
    );
};

const StyledOutlinedInput = styled(OutlinedInput)(({theme}) => ({
    width: 240,
    transition: theme.transitions.create(["box-shadow", "width"], {
        easing: theme.transitions.easing.easeInOut,
        duration: theme.transitions.duration.shorter
    }),
    "&.Mui-focused": {width: 320, boxShadow: theme.customShadows.z8},
    "& fieldset": {
        borderColor: `${theme.palette.grey[500_32]} !important`,
        borderWidth: `1px !important`
    }
}));

export default SearchTextField;
