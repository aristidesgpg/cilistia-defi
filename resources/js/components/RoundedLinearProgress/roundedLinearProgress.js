import React from "react";
import {styled} from "@mui/material/styles";
import LinearProgress, {
    linearProgressClasses
} from "@mui/material/LinearProgress";

const RoundedLinearProgress = (props) => {
    return <StyledLinearProgress variant="determinate" {...props} />;
};

const StyledLinearProgress = styled(LinearProgress)(({theme}) => ({
    height: theme.spacing(1.5),
    borderRadius: 5,
    [`&.${linearProgressClasses.colorPrimary}`]: {
        backgroundColor: theme.palette.background.neutral
    },
    [`& .${linearProgressClasses.bar}`]: {
        backgroundColor: theme.palette.primary.dark,
        borderRadius: 5
    }
}));

export default RoundedLinearProgress;
