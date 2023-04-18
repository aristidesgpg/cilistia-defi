import {alpha} from "@mui/material/styles";

export default function CssBaseline(theme) {
    return {
        MuiCssBaseline: {
            styleOverrides: {
                "*": {
                    margin: 0,
                    padding: 0,
                    boxSizing: "border-box"
                },
                "*::-webkit-scrollbar": {width: 6, height: 6},
                "*::-webkit-scrollbar-button": {display: "none"},
                "*::-webkit-scrollbar-track": {background: "transparent"},
                "*::-webkit-scrollbar-corner": {background: "transparent"},
                "*::-webkit-resizer": {background: "transparent"},
                "*::-webkit-scrollbar-thumb": {
                    backgroundColor: alpha(theme.palette.grey[600], 0.48),
                    borderRadius: theme.shape.borderRadius
                },
                html: {
                    width: "100%",
                    height: "100%",
                    WebkitOverflowScrolling: "touch"
                },
                body: {
                    width: "100%",
                    height: "100%"
                },
                "#root": {
                    width: "100%",
                    height: "100%"
                },
                input: {
                    "&[type=number]": {
                        MozAppearance: "textfield",
                        "&::-webkit-outer-spin-button": {
                            margin: 0,
                            WebkitAppearance: "none"
                        },
                        "&::-webkit-inner-spin-button": {
                            margin: 0,
                            WebkitAppearance: "none"
                        }
                    }
                },
                img: {
                    display: "block",
                    maxWidth: "100%"
                }
            }
        }
    };
}
