import React, {useMemo} from "react";
import {DataGrid} from "@mui/x-data-grid";
import {isUndefined} from "lodash";
import GridLoadingOverlay from "components/GridLoadingOverlay";

const Table = ({
    rows = [],
    rowHeight = 70,
    pageSize = 10,
    rowsPerPageOptions = [10, 25, 50],
    columns = [],
    autoHeight = true,
    ...otherProps
}) => {
    columns = useMemo(() => {
        return columns.map((column) => {
            const newColumn = {...column};

            if (isUndefined(column.filterable)) {
                newColumn.filterable = false;
            }

            if (isUndefined(column.sortable)) {
                newColumn.sortable = false;
            }

            if (isUndefined(column.disableColumnMenu)) {
                newColumn.disableColumnMenu = !newColumn.filterable;
            }

            return newColumn;
        });
    }, [columns]);

    return (
        <DataGrid
            rows={rows}
            pageSize={pageSize}
            autoHeight={autoHeight}
            rowHeight={rowHeight}
            columns={columns}
            rowsPerPageOptions={rowsPerPageOptions}
            hideFooter={rows.length <= pageSize}
            disableColumnSelector
            disableSelectionOnClick
            disableDensitySelector
            components={{LoadingOverlay: GridLoadingOverlay}}
            {...otherProps}
        />
    );
};

export default Table;
