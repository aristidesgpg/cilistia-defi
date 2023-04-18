import React from "react";
import {
    Box,
    Card,
    CardContent,
    CardHeader,
    Stack,
    Typography
} from "@mui/material";
import {FormattedMessage} from "react-intl";
import defaultImage from "static/default-img.png";
import UploadButton from "components/UploadButton";
import {styled} from "@mui/material/styles";
import {route} from "services/Http";
import {useBrand} from "hooks/settings";
import {fetchBrand} from "redux/slices/settings";
import {useDispatch} from "react-redux";

const UploadLogos = () => {
    const brand = useBrand();
    const dispatch = useDispatch();

    const handleSuccess = () => {
        dispatch(fetchBrand());
    };

    return (
        <Card>
            <CardHeader
                title={<FormattedMessage defaultMessage="Upload Logos" />}
            />

            <CardContent>
                <Stack spacing={3}>
                    <Stack direction="row" alignItems="center" spacing={3}>
                        <LogoPreview url={brand.favicon_url} />

                        <UploadButton
                            action={route("admin.brand.upload-favicon")}
                            onSuccess={handleSuccess}
                            helperText={
                                <FormattedMessage
                                    defaultMessage="png, width: {size}px, height: {size}px"
                                    values={{size: <b>32</b>}}
                                />
                            }>
                            <Typography variant="inherit" noWrap>
                                <FormattedMessage defaultMessage="Upload Favicon" />
                            </Typography>
                        </UploadButton>
                    </Stack>

                    <Stack direction="row" alignItems="center" spacing={3}>
                        <LogoPreview url={brand.logo_url} />

                        <UploadButton
                            action={route("admin.brand.upload-logo")}
                            onSuccess={handleSuccess}
                            helperText={
                                <FormattedMessage
                                    defaultMessage="png, width: {size}px, height: {size}px"
                                    values={{size: <b>100</b>}}
                                />
                            }>
                            <Typography variant="inherit" noWrap>
                                <FormattedMessage defaultMessage="Upload Logo" />
                            </Typography>
                        </UploadButton>
                    </Stack>
                </Stack>
            </CardContent>
        </Card>
    );
};

const LogoPreview = ({url}) => {
    return (
        <LogoContainer>
            <Box
                component="img"
                sx={{zIndex: 8, objectFit: "cover"}}
                src={url ?? defaultImage}
                alt="logo-preview"
            />
        </LogoContainer>
    );
};

const LogoContainer = styled("div")(({theme}) => ({
    overflow: "hidden",
    flexShrink: 0,
    padding: theme.spacing(1),
    border: `1px dashed ${theme.palette.grey[500_32]}`,
    borderRadius: "50%",
    display: "flex",
    justifyContent: "center",
    alignItems: "center",
    width: 70,
    height: 70,
    "& > *": {
        maxWidth: "100%",
        maxHeight: "100%"
    }
}));

export default UploadLogos;
