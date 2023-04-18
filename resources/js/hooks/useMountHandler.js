import {mountHandler} from "utils/helpers";
import {useEffect, useRef} from "react";

const useMountHandler = () => {
    const handler = useRef(mountHandler());

    useEffect(() => () => handler.current.unmount(), []);

    return handler.current;
};

export default useMountHandler;
