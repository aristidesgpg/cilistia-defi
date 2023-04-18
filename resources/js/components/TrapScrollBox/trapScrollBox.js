import React, {useRef, useEffect} from "react";
import {Box} from "@mui/material";

const TrapScrollBox = (props) => {
    const ref = useRef();

    useEffect(() => {
        const trapScroll = (e) => e.stopPropagation();
        const options = {passive: true};

        const unsubscribe = () => {
            ref.current?.removeEventListener("touchend", trapScroll, options);
            ref.current?.removeEventListener("touchstart", trapScroll, options);
            ref.current?.removeEventListener("touchmove", trapScroll, options);
        };

        ref.current?.addEventListener("touchend", trapScroll, options);
        ref.current?.addEventListener("touchstart", trapScroll, options);
        ref.current?.addEventListener("touchmove", trapScroll, options);

        return unsubscribe;
    }, []);

    return <Box {...props} ref={ref} />;
};

export default TrapScrollBox;
