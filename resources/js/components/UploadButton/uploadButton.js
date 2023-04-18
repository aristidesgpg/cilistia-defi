import React, {useState} from "react";
import {LoadingButton} from "@mui/lab";
import Upload from "rc-upload";
import {defineMessages, useIntl} from "react-intl";
import {useUploadRequest} from "services/Http";
import {get, isEmpty} from "lodash";
import {FormHelperText, Stack} from "@mui/material";
import {experimentalStyled as styled} from "@mui/material/styles";
import BackupIcon from "@mui/icons-material/Backup";
import {notify} from "utils/index";

const messages = defineMessages({
    uploaded: {defaultMessage: "File was uploaded."}
});

const UploadButton = ({
    name = "file",
    action,
    data,
    mimeTypes,
    helperText,
    onSuccess,
    onError,
    startIcon = <BackupIcon />,
    color = "primary",
    ...otherProps
}) => {
    const intl = useIntl();
    const [request, loading] = useUploadRequest();
    const [errors, setErrors] = useState([]);

    const hasErrors = !isEmpty(errors);

    const beforeUpload = (file) => {
        setErrors([]);
        return Promise.resolve(file);
    };

    const handleSuccess = (data) => {
        notify.success(intl.formatMessage(messages.uploaded));
        return onSuccess?.(data);
    };

    const handleError = (e, data) => {
        setErrors(get(data, `errors.${name}`, []));
        return onError?.(e, data);
    };

    helperText = hasErrors ? errors.join(", ") : helperText;

    return (
        <Stack spacing={1}>
            <StyledUpload
                name={name}
                action={action}
                customRequest={request}
                accept={mimeTypes}
                data={data}
                beforeUpload={beforeUpload}
                onError={handleError}
                onSuccess={handleSuccess}>
                <LoadingButton
                    variant="outlined"
                    color={hasErrors ? "error" : color}
                    loading={loading}
                    startIcon={startIcon}
                    sx={{maxWidth: 1}}
                    {...otherProps}
                />
            </StyledUpload>

            {helperText && (
                <FormHelperText error={hasErrors}>{helperText}</FormHelperText>
            )}
        </Stack>
    );
};

const StyledUpload = styled(Upload)({
    minWidth: 0
});

export default UploadButton;
