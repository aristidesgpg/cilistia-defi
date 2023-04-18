import React, {
    forwardRef,
    useCallback,
    useEffect,
    useImperativeHandle,
    useMemo,
    useState
} from "react";
import {errorHandler, useRequest} from "services/Http";
import {has, isUndefined} from "lodash";
import {DataGrid} from "@mui/x-data-grid";
import {Box} from "@mui/material";
import {TableProvider} from "contexts/TableContext";
import PropTypes from "prop-types";
import GridLoadingOverlay from "components/GridLoadingOverlay";
import classNames from "classnames";

const AsyncTable = forwardRef((props, ref) => {
    const {
        url,
        autoHeight = true,
        getRowId = (row) => row.id,
        checkboxSelection = false,
        initialPageSize = 10,
        onDataChange,
        rowHeight = 70,
        rowsPerPageOptions = [10, 25, 50],
        columns: baseColumns = [],
        components,
        componentsProps,
        ...otherProps
    } = props;

    const [request, loading] = useRequest();
    const [pageSize, setPageSize] = useState(initialPageSize);
    const [page, setPage] = useState(0);
    const [rowCount, setRowCount] = useState(0);
    const [search, setSearch] = useState({});
    const [params, setParams] = useState({});
    const [selection, setSelection] = useState([]);
    const [filters, setFilters] = useState([]);
    const [data, setData] = useState([]);

    const columns = useMemo(() => {
        return baseColumns.map((column) => {
            const result = {...column};

            if (isUndefined(column.filterable)) {
                result.filterable = false;
            }

            if (isUndefined(column.sortable)) {
                result.sortable = false;
            }

            if (isUndefined(column.disableColumnMenu)) {
                result.disableColumnMenu = !result.filterable;
            }

            if (isUndefined(column.description)) {
                column.description = column.headerName;
            }

            return result;
        });
    }, [baseColumns]);

    const fetchData = useCallback(() => {
        const urlParams = {...params, search, filters};
        urlParams.page = page + 1;
        urlParams.itemPerPage = pageSize;

        request
            .get(url, {params: urlParams})
            .then((response) => {
                setRowCount(response.meta.total);
                setData(response.data);
            })
            .catch(errorHandler());
    }, [request, page, pageSize, filters, search, url, params]);

    useEffect(() => {
        fetchData();
    }, [fetchData]);

    const resetPage = useCallback(() => {
        setPage(0);
    }, []);

    const applySearch = useCallback((search) => {
        setSearch((state) => ({...state, ...search}));
        setPage(0);
    }, []);

    const clearSearch = useCallback(() => {
        setSearch({});
        setPage(0);
    }, []);

    const applyParams = useCallback((params) => {
        setParams((state) => ({...state, ...params}));
        setPage(0);
    }, []);

    const clearParams = useCallback(() => {
        setParams({});
        setPage(0);
    }, []);

    useImperativeHandle(ref, () => ({
        resetPage,
        fetchData,
        applySearch,
        clearSearch,
        applyParams,
        clearParams
    }));

    const containerHeight = useMemo(() => {
        return autoHeight ? (data.length > 0 ? "auto" : 300) : "100%";
    }, [data, autoHeight]);

    const handleFilterChange = useCallback((filterModel) => {
        setFilters(parseFilters(filterModel?.items));
    }, []);

    const getRowClassName = () => {
        return classNames({pointer: has(otherProps, "onRowClick")});
    };

    return (
        <TableProvider
            reload={fetchData}
            applySearch={applySearch}
            clearSearch={clearSearch}
            applyParams={applyParams}
            clearParams={clearParams}
            resetPage={resetPage}
            selection={selection}
            loading={loading}>
            <Box sx={{height: containerHeight}}>
                <DataGrid
                    getRowClassName={getRowClassName}
                    autoHeight={data.length > 0 && autoHeight}
                    {...otherProps}
                    rowHeight={rowHeight}
                    getRowId={getRowId}
                    rows={data}
                    columns={columns}
                    filterMode="server"
                    onFilterModelChange={handleFilterChange}
                    rowsPerPageOptions={rowsPerPageOptions}
                    disableColumnSelector
                    disableSelectionOnClick
                    disableDensitySelector
                    checkboxSelection={checkboxSelection}
                    onSelectionModelChange={setSelection}
                    selectionModel={selection}
                    loading={loading}
                    components={{
                        LoadingOverlay: GridLoadingOverlay,
                        ...components
                    }}
                    componentsProps={componentsProps}
                    pagination
                    paginationMode="server"
                    page={page}
                    pageSize={pageSize}
                    onPageSizeChange={setPageSize}
                    onPageChange={setPage}
                    rowCount={rowCount}
                />
            </Box>
        </TableProvider>
    );
});

AsyncTable.propTypes = {
    url: PropTypes.string.isRequired,
    autoHeight: PropTypes.bool,
    checkboxSelection: PropTypes.bool,
    getRowId: PropTypes.func,
    initialPageSize: PropTypes.number,
    onDataChange: PropTypes.func,
    rowHeight: PropTypes.number,
    rowsPerPageOptions: PropTypes.arrayOf(PropTypes.number),
    columns: PropTypes.arrayOf(PropTypes.object)
};

const parseFilters = (filters) => {
    return filters?.map((o) => ({
        field: o.columnField,
        operator: o.operatorValue,
        value: o.value
    }));
};

export default AsyncTable;
