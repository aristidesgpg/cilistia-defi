import loadable from "@loadable/component";
import {CircularProgress, Stack} from "@mui/material";
import React from "react";

const componentImport = () =>
    import(/* webpackChunkName: 'rich-editor' */ "./richEditor");

const Component = loadable(componentImport, {
    fallback: (
        <Stack direction="column" justifyContent="center" alignItems="center">
            <CircularProgress size="1em" />
        </Stack>
    )
});

const RichEditor = (props) => {
    return <Component {...props} />;
};

export default RichEditor;
