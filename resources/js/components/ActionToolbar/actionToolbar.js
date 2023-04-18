import React from "react";
import {experimentalStyled as styled} from "@mui/material/styles";
import {Stack, Toolbar} from "@mui/material";

const ActionToolbar = ({children}) => {
    return (
        <StyledToolbar>
            <Stack
                direction="row"
                justifyContent="space-between"
                alignItems="center"
                sx={{width: "100%"}}
                spacing={2}>
                {children}
            </Stack>
        </StyledToolbar>
    );
};

const StyledToolbar = styled(Toolbar)(({theme}) => ({
    height: 80,
    padding: theme.spacing(0, 3)
}));

export default ActionToolbar;
