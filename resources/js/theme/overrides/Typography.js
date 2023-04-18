export default function Typography(theme) {
    return {
        MuiTypography: {
            styleOverrides: {
                root: {
                    ".print-container &": {
                        color: `${theme.palette.grey[900]} !important`
                    }
                },
                paragraph: {
                    marginBottom: theme.spacing(2)
                },
                gutterBottom: {
                    marginBottom: theme.spacing(1)
                }
            }
        }
    };
}
