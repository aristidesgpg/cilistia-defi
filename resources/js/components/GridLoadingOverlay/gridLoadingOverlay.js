import React from "react";
import {LinearProgress} from "@mui/material";
import {GridOverlay} from "@mui/x-data-grid";

const GridLoadingOverlay = () => {
    return (
        <GridOverlay>
            <div style={{position: "absolute", top: 0, width: "100%"}}>
                <LinearProgress />
            </div>
        </GridOverlay>
    );
};

export default GridLoadingOverlay;
