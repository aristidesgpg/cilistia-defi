import {alpha} from "@mui/material/styles";

export default function ToggleButton(theme) {
    const style = (color) => ({
        props: {color},
        style: {
            "&:hover": {
                borderColor: alpha(theme.palette[color].main, 0.48),
                backgroundColor: alpha(
                    theme.palette[color].main,
                    theme.palette.action.hoverOpacity
                )
            },
            "&.Mui-selected": {
                borderColor: alpha(theme.palette[color].main, 0.48)
            }
        }
    });

    return {
        MuiToggleButton: {
            variants: [
                {
                    props: {color: "standard"},
                    style: {
                        "&.Mui-selected": {
                            backgroundColor: theme.palette.action.selected
                        }
                    }
                },
                style("primary"),
                style("secondary"),
                style("info"),
                style("success"),
                style("warning"),
                style("error")
            ]
        },
        MuiToggleButtonGroup: {
            styleOverrides: {
                root: {
                    borderRadius: 5,
                    backgroundColor: theme.palette.background.paper,
                    border: `solid 1px ${theme.palette.grey[500_12]}`,
                    "& .MuiToggleButton-root": {
                        margin: theme.spacing(0.5),
                        borderRadius: `${theme.shape.borderRadius}px !important`,
                        borderColor: "transparent !important"
                    }
                },
                groupedVertical: {
                    width: "auto"
                }
            }
        }
    };
}
