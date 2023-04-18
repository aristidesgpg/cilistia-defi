import React, {useCallback} from "react";
import {Responsive, WidthProvider} from "react-grid-layout";
import {breakpoints, cols, rowHeight} from "utils/grid";

const ResponsiveGridLayout = WidthProvider(Responsive);

const GridLayout = ({
    children,
    layouts,
    isDraggable,
    isResizable,
    onLayoutChange,
    margin = [10, 15]
}) => {
    const handleLayoutChange = useCallback(
        (current, all) => onLayoutChange?.(all),
        [onLayoutChange]
    );

    return (
        <ResponsiveGridLayout
            margin={margin}
            isDraggable={isDraggable}
            onLayoutChange={handleLayoutChange}
            isResizable={isResizable}
            layouts={layouts}
            breakpoints={breakpoints}
            rowHeight={rowHeight}
            cols={cols}>
            {children}
        </ResponsiveGridLayout>
    );
};

export default GridLayout;
