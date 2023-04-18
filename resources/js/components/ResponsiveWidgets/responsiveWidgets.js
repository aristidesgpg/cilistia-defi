import React, {useCallback, useEffect, useMemo, useState} from "react";
import {errorHandler, route, useRequest} from "services/Http";
import {
    forEach,
    forOwn,
    intersection,
    isEmpty,
    keyBy,
    mapValues,
    omit
} from "lodash";
import {breakpoints, cols} from "utils/grid";
import {notify, pluck} from "utils";
import GridLayout from "components/GridLayout";
import {defineMessages, useIntl} from "react-intl";
import {Box, Fab, GlobalStyles, Stack} from "@mui/material";
import SaveIcon from "@mui/icons-material/Save";
import CloseIcon from "@mui/icons-material/Close";
import RestoreIcon from "@mui/icons-material/Restore";
import EditIcon from "@mui/icons-material/Edit";
import {useAuth} from "models/Auth";
import LoadingFallback from "components/LoadingFallback";
import GridItem from "./gridItem";

const messages = defineMessages({
    savedSuccess: {defaultMessage: "Grid was updated."},
    resetSuccess: {defaultMessage: "Grid was reset."}
});

const ResponsiveWidgets = ({widgets, page}) => {
    const auth = useAuth();
    const intl = useIntl();
    const [request, loading] = useRequest();
    const [layouts, setLayouts] = useState({});
    const [names, setNames] = useState([]);
    const [editable, setEditable] = useState(false);
    const [backup, setBackup] = useState({});

    const gridItems = useMemo(() => {
        return widgets
            .filter((o) => names.includes(o.name))
            .map((widget) => (
                <GridItem
                    key={widget.name}
                    component={widget.component}
                    editable={editable}
                />
            ));
    }, [names, widgets, editable]);

    const fetchLayouts = useCallback(() => {
        const parseDimensions = (records) => {
            const names = intersection(
                pluck(widgets, "name"),
                pluck(records, "name")
            );

            const layouts = mapValues(breakpoints, () => []);

            records
                .filter((o) => names.includes(o.name))
                .forEach((record) => {
                    forEach(record.dimensions, (o) => {
                        layouts[o.breakpoint]?.push({
                            i: record.name,
                            ...omit(o, ["breakpoint"])
                        });
                    });
                });

            forOwn(layouts, (layout, breakpoint) => {
                let nextX = 0;
                let nextH = 0;
                let nextY = getBottom(layout);

                widgets
                    .filter((o) => names.includes(o.name))
                    .filter((o) => !getLayoutItem(layout, o.name))
                    .forEach((o) => {
                        const col = cols[breakpoint];
                        const dimension = {...getDimension(o, breakpoint)};

                        dimension.i = o.name;
                        dimension.w = Math.min(dimension.w, col);

                        if (nextX + dimension.w <= col) {
                            dimension.x = nextX;
                            dimension.y = nextY;
                        } else {
                            dimension.x = 0;
                            dimension.y = nextY + nextH;
                        }

                        nextX = dimension.x + dimension.w;
                        nextY = dimension.y;

                        if (dimension.x !== 0) {
                            nextH = Math.max(nextH, dimension.h);
                        } else {
                            nextH = dimension.h;
                        }

                        layouts[breakpoint].push(dimension);
                    });
            });

            setLayouts(layouts);
            setNames(names);
        };

        request
            .post(route("grid.all"), {page})
            .then((data) => parseDimensions(data))
            .catch(errorHandler());
    }, [request, widgets, page]);

    useEffect(() => {
        fetchLayouts();
    }, [fetchLayouts]);

    const onLayoutChange = useCallback((value) => {
        setLayouts(value);
    }, []);

    const edit = useCallback(() => {
        setBackup(layouts);
        setEditable(true);
    }, [layouts]);

    const close = useCallback(() => {
        setLayouts(backup);
        setEditable(false);
    }, [backup]);

    const reset = useCallback(() => {
        request
            .post(route("grid.reset-dimensions"), {page})
            .then(() => {
                notify.success(intl.formatMessage(messages.resetSuccess));
                fetchLayouts();
            })
            .catch(errorHandler());
    }, [request, intl, page, fetchLayouts]);

    const save = useCallback(() => {
        const dimensions = mapValues(keyBy(names), () => []);

        forOwn(layouts, (layout, key) => {
            forEach(layout, (o) => {
                dimensions[o.i]?.push({
                    breakpoint: key,
                    ...omit(o, ["i"])
                });
            });
        });

        request
            .post(route("grid.set-dimensions"), {dimensions, page})
            .then(() => {
                notify.success(intl.formatMessage(messages.savedSuccess));
                setEditable(false);
            })
            .catch(errorHandler());
    }, [request, names, intl, page, layouts]);

    return (
        <React.Fragment>
            <LoadingFallback
                compact={true}
                content={layouts}
                loading={loading}
                fallback={false}
                size={70}>
                {(layouts) =>
                    !isEmpty(gridItems) && (
                        <GridLayout
                            layouts={layouts}
                            onLayoutChange={onLayoutChange}
                            isDraggable={editable}
                            isResizable={editable}
                            margin={[16, 24]}>
                            {gridItems}
                        </GridLayout>
                    )
                }
            </LoadingFallback>

            <GlobalStyles
                styles={(theme) => ({
                    ".react-grid-item.react-grid-placeholder": {
                        background: theme.palette.background.paper
                    }
                })}
            />

            {auth.can("manage_customization") && (
                <Box sx={{bottom: 16, position: "fixed", right: 16}}>
                    {editable ? (
                        <Stack direction="row" spacing={1}>
                            <Fab color="primary" size="medium" onClick={save}>
                                <SaveIcon />
                            </Fab>
                            <Fab color="default" size="medium" onClick={close}>
                                <CloseIcon />
                            </Fab>
                        </Stack>
                    ) : (
                        <Stack direction="row" spacing={1}>
                            <Fab color="default" size="medium" onClick={reset}>
                                <RestoreIcon />
                            </Fab>
                            <Fab color="primary" size="medium" onClick={edit}>
                                <EditIcon />
                            </Fab>
                        </Stack>
                    )}
                </Box>
            )}
        </React.Fragment>
    );
};

const getBottom = (layout) => {
    let max = 0,
        bottomY;
    for (let i = 0, len = layout.length; i < len; i++) {
        bottomY = layout[i].y + layout[i].h;
        if (bottomY > max) max = bottomY;
    }
    return max;
};

const getDimension = (widget, breakpoint) => {
    return widget.component.dimensions[breakpoint];
};

const getLayoutItem = (layout, id) => {
    return layout.find((o) => o.i === id);
};

export default ResponsiveWidgets;
